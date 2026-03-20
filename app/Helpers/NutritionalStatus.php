<?php

namespace App\Helpers;

use App\Models\WhoGrowthStandard;
use Illuminate\Support\Facades\Cache;

class NutritionalStatus
{
    /**
     * Classify nutritional status based on WHO standards.
     *
     * 0–60 months  → WHO Child Growth Standards (2006)
     *                Uses WFA, HFA, WFH indicators
     *
     * 61–228 months (5–19 years) → WHO Growth Reference (2007)
     *                Uses BMI-for-Age indicator
     *
     * @param int    $ageMonths  Age in months
     * @param float  $weight     Weight in kg
     * @param float  $height     Height in cm
     * @param string $sex        'male' or 'female'
     */
    public static function classify(int $ageMonths, float $weight, float $height, string $sex = 'male'): string
    {
        if ($ageMonths < 0) {
            return 'Invalid Age';
        }

        if ($height <= 0 || $weight <= 0) {
            return 'Invalid Measurement';
        }

        // 5–19 years: use BMI-for-Age (WHO Growth Reference 2007)
        if ($ageMonths > 60) {
            return self::classifyBmiForAge($ageMonths, $weight, $height, $sex);
        }

        // 0–5 years: validate against expected child ranges
        if ($height < 44 || $height > 121) {
            return 'Invalid Height';
        }

        if ($weight < 1 || $weight > 35) {
            return 'Invalid Weight';
        }

        // 0–5 years: use WFA + HFA + WFH
        $wfa = self::weightForAge($ageMonths, $weight, $sex);
        $hfa = self::heightForAge($ageMonths, $height, $sex);
        $wfh = self::weightForHeight($height, $weight, $sex);

        $statuses = array_filter([$wfa, $hfa, $wfh]);

        return implode(' | ', $statuses) ?: 'Normal (N)';
    }

    // -------------------------------------------------------
    // 5–19 years: BMI-for-Age classification
    // -------------------------------------------------------

    protected static function classifyBmiForAge(int $ageMonths, float $weight, float $height, string $sex): string
    {
        // WHO BFA tables cover 61–228 months (5–19 years)
        if ($ageMonths > 228) {
            return self::classifyAdultBmi($weight, $height);
        }

        $heightM = $height / 100;
        $bmi     = $weight / ($heightM * $heightM);

        $lms = self::getLMS('bfa', $sex, $ageMonths);

        if (!$lms) {
            // Fallback to simple BMI if no LMS data
            return self::classifyAdultBmi($weight, $height);
        }

        $z = self::zScore($bmi, $lms->l, $lms->m, $lms->s);

        if ($z < -3) return 'SUW — Severely Underweight';
        if ($z < -2) return 'UW — Underweight';
        if ($z > 3)  return 'OB — Obese';
        if ($z > 2)  return 'OW — Overweight';
        if ($z > 1)  return 'At Risk of Overweight';

        return 'Normal (N)';
    }

    // -------------------------------------------------------
    // Over 19 years: simple adult BMI cutoffs (WHO)
    // -------------------------------------------------------

    protected static function classifyAdultBmi(float $weight, float $height): string
    {
        $heightM = $height / 100;
        $bmi     = $weight / ($heightM * $heightM);

        if ($bmi < 16.0) return 'SUW — Severely Underweight';
        if ($bmi < 17.0) return 'UW — Underweight';
        if ($bmi < 18.5) return 'UW — Underweight';
        if ($bmi < 25.0) return 'Normal (N)';
        if ($bmi < 30.0) return 'OW — Overweight';

        return 'OB — Obese';
    }

    // -------------------------------------------------------
    // 0–5 years indicators
    // -------------------------------------------------------

    protected static function weightForAge(int $months, float $weight, string $sex): string
    {
        $lms = self::getLMS('wfa', $sex, $months);
        if (!$lms) return '';

        $z = self::zScore($weight, $lms->l, $lms->m, $lms->s);

        if ($z < -3) return 'SUW — Severely Underweight';
        if ($z < -2) return 'UW — Underweight';
        if ($z > 3)  return 'OB — Obese';
        if ($z > 2)  return 'OW — Overweight';
        return '';
    }

    protected static function heightForAge(int $months, float $height, string $sex): string
    {
        $lms = self::getLMS('hfa', $sex, $months);
        if (!$lms) return '';

        $z = self::zScore($height, $lms->l, $lms->m, $lms->s);

        if ($z < -3) return 'SST — Severely Stunted';
        if ($z < -2) return 'ST — Stunted';
        if ($z > 3)  return 'VT — Very Tall';
        return '';
    }

    protected static function weightForHeight(float $height, float $weight, string $sex): string
    {
        $lms = self::getLMS('wfh', $sex, $height);
        if (!$lms) return '';

        $z = self::zScore($weight, $lms->l, $lms->m, $lms->s);

        if ($z < -3) return 'W — Wasted';
        if ($z < -2) return 'MW — Moderately Wasted';
        if ($z > 3)  return 'OB — Obese';
        if ($z > 2)  return 'OW — Overweight';
        return '';
    }

    // -------------------------------------------------------
    // LMS lookup with caching + interpolation
    // -------------------------------------------------------

    protected static function getLMS(string $indicator, string $sex, float $keyValue): ?object
    {
        $cacheKey = "who_lms_{$indicator}_{$sex}";

        $table = Cache::rememberForever($cacheKey, fn () =>
        WhoGrowthStandard::where('indicator', $indicator)
            ->where('sex', $sex)
            ->orderBy('key_value')
            ->get()
            ->map(fn ($row) => [
                'l'         => (float) $row->l,
                'm'         => (float) $row->m,
                's'         => (float) $row->s,
                'key_value' => (float) $row->key_value,
            ])
            ->values()
            ->toArray()
        );

        if (empty($table)) return null;

        $lower = null;
        $upper = null;

        foreach ($table as $row) {
            if ($row['key_value'] <= $keyValue) {
                $lower = $row;
            }
            if ($row['key_value'] >= $keyValue && $upper === null) {
                $upper = $row;
            }
        }

        if (!$lower && !$upper) return null;
        if (!$lower) return (object) $upper;
        if (!$upper) return (object) $lower;

        if ($lower['key_value'] === $upper['key_value']) {
            return (object) $lower;
        }

        $ratio = ($keyValue - $lower['key_value']) / ($upper['key_value'] - $lower['key_value']);

        return (object) [
            'l' => $lower['l'] + $ratio * ($upper['l'] - $lower['l']),
            'm' => $lower['m'] + $ratio * ($upper['m'] - $lower['m']),
            's' => $lower['s'] + $ratio * ($upper['s'] - $lower['s']),
        ];
    }

    // -------------------------------------------------------
    // WHO LMS z-score formula
    // -------------------------------------------------------

    protected static function zScore(float $x, float $l, float $m, float $s): float
    {
        if ($l == 0) {
            return log($x / $m) / $s;
        }
        return (pow($x / $m, $l) - 1) / ($l * $s);
    }
}
