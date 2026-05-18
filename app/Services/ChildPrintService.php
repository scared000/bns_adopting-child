<?php

namespace App\Services;

use App\Models\AdoptedChild;
use App\Models\OfficeChildVisit;
use Carbon\Carbon;


class ChildPrintService
{

    public function getLatestVisit(int $childId): ?OfficeChildVisit
    {
        return OfficeChildVisit::where('adopted_id', $childId)
            ->orderByDesc('visit_date')
            ->first();
    }


    public function getBaselineVisit(int $childId): ?OfficeChildVisit
    {
        return OfficeChildVisit::where('adopted_id', $childId)
            ->orderBy('visit_date')
            ->first();
    }


    public function ageInMonths(?string $birthdate, ?string $reference = null): ?int
    {
        if (! $birthdate) {
            return null;
        }

        $birth = Carbon::parse($birthdate);
        $ref   = $reference ? Carbon::parse($reference) : Carbon::today();

        return (int) $birth->diffInMonths($ref);
    }


    public function parseStatusComponents(?string $rawStatus): array
    {
        $defaults = ['WFA' => null, 'HFA' => null, 'WFH' => null];

        if (! $rawStatus) {
            return $defaults;
        }

        $components = [];
        foreach (explode('|', $rawStatus) as $part) {
            $part = trim($part);
            if (str_contains($part, ':')) {
                [$key, $value]          = explode(':', $part, 2);
                $components[trim($key)] = trim($value);
            }
        }

        return array_merge($defaults, $components);
    }


    public function abbreviateStatus(?string $status): ?string
    {
        if (! $status) {
            return null;
        }

        $map = [
            'severely underweight' => 'SUW',
            'underweight'          => 'UW',
            'normal'               => 'N',
            'overweight'           => 'OW',
            'obese'                => 'OB',
            'stunted'              => 'ST',
            'severely stunted'     => 'SST',
            'tall'                 => 'T',
            'wasted'               => 'W',
            'severely wasted'      => 'SW',
        ];

        return $map[strtolower(trim($status))] ?? $status;
    }

    public function getRehabStatus(?string $wfaStatus, ?string $wfhStatus = null): ?string
    {
        $wfaLower = strtolower(trim($wfaStatus ?? ''));

        if (! $wfaLower) {
            return null;
        }

        if ($wfaLower === 'normal') {
            return 'Rehabilitated';
        }

        return 'Maintained';
    }


    public function computeBmi(?float $weightKg, ?float $heightCm): ?float
    {
        if (! $weightKg || ! $heightCm || $heightCm <= 0) {
            return null;
        }

        $heightM = $heightCm / 100;

        return round($weightKg / ($heightM ** 2), 2);
    }

    public function findChildForPrint(int $id): AdoptedChild
    {
        return AdoptedChild::with([
            'officeVisits',
            'visitItems',
            'immunizations',
        ])->findOrFail($id);
    }

    /**
     * Load child with only visit data (lighter query for monthly monitoring + profile).
     */
    public function findChildWithVisits(int $id): AdoptedChild
    {
        return AdoptedChild::with([
            'officeVisits' => fn ($q) => $q->orderBy('date_weighing'),
        ])->findOrFail($id);
    }
}
