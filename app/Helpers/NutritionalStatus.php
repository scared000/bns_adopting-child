<?php

namespace App\Helpers;

use App\Models\WhoGrowthStandard;

class NutritionalStatus
{
    /**
     * Classify nutritional status based on WHO Growth Standards (LMS method).
     *
     * - 0–60 months  : uses WFA (weight-for-age) + HFA (height-for-age) + WFH (weight-for-height)
     * - 61–228 months: uses BFA (BMI-for-age)
     *
     * @param int    $ageMonths  Age in completed months
     * @param float  $weightKg   Weight in kilograms
     * @param float  $heightCm   Height in centimetres
     * @param string $sex        'male' or 'female'
     * @return string
     */
    public static function classify(int $ageMonths, float $weightKg, float $heightCm, string $sex): string
    {
        if (!in_array($sex, ['male', 'female'])) return 'Incomplete Data';
        if ($weightKg <= 0 || $heightCm <= 0) return 'Incomplete Data';

        // --- 0 to 5 Years (60 Months) ---
        if ($ageMonths <= 60) {
            $wfaZ = self::computeZScore('wfa', $sex, (float)$ageMonths, $weightKg);
            $hfaZ = self::computeZScore('hfa', $sex, (float)$ageMonths, $heightCm);
            $wfhZ = self::computeZScore('wfh', $sex, $heightCm, $weightKg);

            $results = [];

            // Weight-for-Age (WFA)
            if ($wfaZ !== null) {
                if ($wfaZ <= -3) $results[] = 'WFA: Severely Underweight';
                elseif ($wfaZ <= -2) $results[] = 'WFA: Underweight';
                elseif ($wfaZ >= 2) $results[] = 'WFA: Overweight';
                else $results[] = 'WFA: Normal';
            }

            // Height-for-Age (HFA)
            if ($hfaZ !== null) {
                if ($hfaZ <= -3) $results[] = 'HFA: Severely Stunted';
                elseif ($hfaZ <= -2) $results[] = 'HFA: Stunted';
                elseif ($hfaZ >= 2) $results[] = 'HFA: Tall';
                else $results[] = 'HFA: Normal';
            }

            // Weight-for-Height (WFH)
            if ($wfhZ !== null) {
                if ($wfhZ <= -3) $results[] = 'WFH: Severely Wasted';
                elseif ($wfhZ <= -2) $results[] = 'WFH: Wasted';
                elseif ($wfhZ >= 3) $results[] = 'WFH: Obese';
                elseif ($wfhZ >= 2) $results[] = 'WFH: Overweight';
                else $results[] = 'WFH: Normal';
            }

            return count($results) > 0 ? implode(' | ', $results) : 'Normal';
        }

        // --- 5 to 19 Years (61 to 228 Months) ---
        if ($ageMonths > 60 && $ageMonths <= 228) {
            $bmi = $weightKg / (($heightCm / 100.0) ** 2);
            $zScore = self::computeZScore('bfa', $sex, (float)$ageMonths, $bmi);

            if ($zScore === null) return 'N/A';

            if ($zScore > 3) return 'Obese';
            if ($zScore > 2) return 'Overweight';
            if ($zScore > 1) return 'At Risk of Overweight';

            if ($zScore < -3) return 'Severely Underweight';
            if ($zScore < -2) return 'Underweight';

            return 'Normal';
        }

        // --- Adults (Above 19 Years) ---
        $bmi = $weightKg / (($heightCm / 100.0) ** 2);
        if ($bmi < 18.5) return 'Underweight';
        if ($bmi < 25.0) return 'Normal';
        if ($bmi < 30.0) return 'Overweight';
        return 'Obese';
    }

    // -------------------------------------------------------------------------
    // LMS z-score computation
    // -------------------------------------------------------------------------

    /**
     * Compute a WHO LMS z-score.
     *
     * Formula:
     *   L ≠ 0 → Z = [(X / M)^L − 1] / (L × S)
     *   L = 0 → Z = ln(X / M) / S
     *
     * @param string $indicator  'wfa' | 'hfa' | 'wfh' | 'bfa'
     * @param string $sex        'male' | 'female'
     * @param float  $keyValue   age_months (wfa/hfa/bfa) or height_cm (wfh)
     * @param float  $measurement The observed value (weight, height, or BMI)
     * @return float|null  null if no matching LMS row found
     */
    private static function computeZScore(
        string $indicator,
        string $sex,
        float  $keyValue,
        float  $measurement
    ): ?float {
        $row = WhoGrowthStandard::where('indicator', $indicator)
            ->where('sex', $sex)
            ->where('key_value', $keyValue)
            ->first();

        // Fallback: nearest available key_value (handles rounding gaps)
        if (!$row) {
            $row = WhoGrowthStandard::where('indicator', $indicator)
                ->where('sex', $sex)
                ->orderByRaw('ABS(key_value - ?)', [$keyValue])
                ->first();
        }

        if (!$row || $row->m <= 0) {
            return null;
        }

        $L = (float) $row->l;
        $M = (float) $row->m;
        $S = (float) $row->s;

        if (abs($L) < 1e-6) {
            // L ≈ 0: use natural log formula
            $z = log($measurement / $M) / $S;
        } else {
            $z = (pow($measurement / $M, $L) - 1.0) / ($L * $S);
        }

        return $z;
    }
}
