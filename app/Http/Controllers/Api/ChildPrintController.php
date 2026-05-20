<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdoptedChild;
use App\Services\ChildPrintService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * ChildPrintController
 *
 * Provides separate JSON endpoints for each print layout.
 * All heavy computation is delegated to ChildPrintService.
 *
 * Routes:
 *   GET /api/print/child/{id}/monthly-monitoring
 *   GET /api/print/child/{id}/profile
 *   GET /api/print/child/{id}/items-delivered
 *   GET /api/print/child/{id}/immunization-record
 */
class ChildPrintController extends Controller
{
    public function __construct(
        protected ChildPrintService $printService
    ) {}

    /**
     * Batch Monthly Monitoring for multiple children (used by "Print Filtered")
     * GET /api/print/child/batch/monthly-monitoring?ids=1,2,3
     * or with batch filter: ?batch=Batch%201
     */
    public function batchMonthlyMonitoring(Request $request): JsonResponse
    {
        $ids = array_filter(explode(',', $request->query('ids', '')));
        $batchFilter = $request->query('batch');

        if (empty($ids) && empty($batchFilter)) {
            return response()->json(['error' => 'No child IDs or batch provided.'], 422);
        }

        $query = AdoptedChild::with([
            'officeVisits' => fn ($q) => $q->orderBy('visit_date'),
        ]);

        if (!empty($ids)) {
            $query->whereIn('id', $ids);
        }

        if ($batchFilter) {
            $query->where('batch', $batchFilter);
        }

        $children = $query->get();

        $result = [];

        foreach ($children as $child) {
            $baseline = $this->printService->getBaselineVisit($child->id);
            $latest   = $this->printService->getLatestVisit($child->id);

            $baselineData = null;
            if ($baseline) {
                $components = $this->printService->parseStatusComponents($baseline->status);

                $baselineData = [
                    'age_months'         => $this->printService->ageInMonths($child->birthdate, $baseline->visit_date),
                    'weight'             => $baseline->weight,
                    'height'             => $baseline->height,
                    'nutritional_status' => $baseline->status,
                    'date_weighing'      => $baseline->visit_date,
                    'WFA'                => $this->printService->abbreviateStatus($components['WFA']),
                    'HFA'                => $this->printService->abbreviateStatus($components['HFA']),
                    'WFH'                => $this->printService->abbreviateStatus($components['WFH']),
                ];
            }

            $followUpData = null;
            if ($latest) {
                $components = $this->printService->parseStatusComponents($latest->status);

                $followUpData = [
                    'age_months'            => $this->printService->ageInMonths($child->birthdate, $latest->visit_date),
                    'weight'                => $latest->weight,
                    'height'                => $latest->height,
                    'WFA'                   => $this->printService->abbreviateStatus($components['WFA']),
                    'HFA'                   => $this->printService->abbreviateStatus($components['HFA']),
                    'WFH'                   => $this->printService->abbreviateStatus($components['WFH']),
                    'rehabilitation_status' => $this->printService->getRehabStatus($components['WFA']),
                    'date_weighing'         => $latest->visit_date,
                ];
            }

            $result[] = [
                'child_id'     => $child->id,
                'child_name'   => trim("{$child->firstname} {$child->lastname}"),
                'sex'          => $child->sex,
                'birthdate'    => $child->birthdate,
                'batch'        => $child->batch,
                'baseline'     => $baselineData,
                'follow_up'    => $followUpData,
            ];
        }

        return response()->json([
            'children' => $result,
            'total'    => count($result),
            'generated_at' => now()->toIso8601String(),
        ]);
    }

    /**
     * Returns baseline + latest visit data for the Monthly Monitoring print layout.
     *
     * Reuses:
     *   - parseStatusComponents()
     *   - abbreviateStatus()
     *   - getRehabStatus()
     *   - getBaselineVisit()
     *   - getLatestVisit()
     *   - ageInMonths()
     */

    public function childProfile(int $id): JsonResponse
    {
        return response()->json($this->buildProfile($id));
    }

    public function itemsDelivered(int $id): JsonResponse
    {
        return response()->json($this->buildItemsDelivered($id));
    }

    public function immunizationRecord(int $id): JsonResponse
    {
        return response()->json($this->buildImmunizationRecord($id));
    }

    private function buildProfile(int $id): array
    {
        $child  = $this->printService->findChildWithVisits($id);
        $latest = $this->printService->getLatestVisit($id);

        $currentAgeMonths = $currentHeight = $currentWeight = null;
        $dateOfWeighing   = $bmi = $nutritionalStatus = null;

        if ($latest) {
            $currentAgeMonths  = $this->printService->ageInMonths($child->birthdate, $latest->visit_date);
            $currentHeight     = $latest->height;
            $currentWeight     = $latest->weight;
            $dateOfWeighing    = $latest->visit_date;
            $bmi               = $this->printService->computeBmi($latest->weight, $latest->height);
            $nutritionalStatus = $latest->status;
        }

        return [
            'profile_path' => $child->profile_path ? asset('storage/' . $child->profile_path) : null,
            'firstname'    => $child->firstname,
            'lastname'     => $child->lastname,
            'middlename'   => $child->middlename,
            'suffix'       => $child->suffix,
            'address'      => [
                'purok'        => $child->purok,
                'barangay'     => $child->barangay?->brgyDesc,
                'municipality' => $child->municipality?->citymunDesc,
                'province'     => $child->municipality?->province?->provDesc,
            ],
            'sex'                => $child->sex,
            'birthdate'          => $child->birthdate,
            'birthplace'         => $child->birthplace,
            'age_months'         => $currentAgeMonths,
            'height_cm'          => $currentHeight,
            'weight_kg'          => $currentWeight,
            'date_of_weighing'   => $dateOfWeighing,
            'body_mass_index'    => $bmi,
            'nutritional_status' => $nutritionalStatus,
            'lcr_registered'     => $child->lcr_registered,
            'breastfed'          => $child->breastfed,
            'v_supplemented'     => $child->v_suplemented,
            'underlying_cause'   => $child->underlying_cause,
        ];
    }

    private function buildItemsDelivered(int $id): array
    {
        $child = $this->printService->findChildForPrint($id);
        $items = $child->visitItems->map(fn ($item) => [
            'item_description' => $item->Item_description,
            'item_quantity'    => $item->item_quantity,
            'item_amount'      => $item->item_amount,
        ]);

        return [
            'child_name'   => trim("{$child->firstname} {$child->lastname}"),
            'items'        => $items,
            'total_amount' => $child->visitItems->sum('item_amount'),
        ];
    }

    private function buildImmunizationRecord(int $id): array
    {
        $child   = $this->printService->findChildForPrint($id);
        $records = $child->immunizations->map(fn ($record) => [
            'vaccine_description' => $record->vaccine_description,
            'dose_1'              => $record->dose_1,
            'dose_2'              => $record->dose_2,
            'dose_3'              => $record->dose_3,
            'dose_4'              => $record->dose_4,
            'dose_5'              => $record->dose_5,
            'total_doses'         => $record->total_doses,
            'status'              => $record->status,
            'remarks'             => $record->remarks,
        ]);

        return [
            'child_name'    => trim("{$child->firstname} {$child->lastname}"),
            'immunizations' => $records,
        ];
    }
    public function monthlyMonitoring(int $id): JsonResponse
    {
        $child    = $this->printService->findChildWithVisits($id);
        $baseline = $this->printService->getBaselineVisit($id);
        $latest   = $this->printService->getLatestVisit($id);

        $baselineData = null;
        if ($baseline) {
            $components = $this->printService->parseStatusComponents($baseline->status);

            $baselineData = [
                'age_months'         => $this->printService->ageInMonths($child->birthdate, $baseline->visit_date), // ✅
                'weight'             => $baseline->weight,
                'height'             => $baseline->height,
                'nutritional_status' => $baseline->status,
                'date_weighing'      => $baseline->visit_date,
                'WFA'                => $this->printService->abbreviateStatus($components['WFA']),
                'HFA'                => $this->printService->abbreviateStatus($components['HFA']),
                'WFH'                => $this->printService->abbreviateStatus($components['WFH']),
            ];
        }

        $followUpData = null;
        if ($latest) {
            $components = $this->printService->parseStatusComponents($latest->status);

            $followUpData = [
                'age_months' => $this->printService->ageInMonths($child->birthdate, $latest->visit_date),
                'weight' => $latest->weight,
                'height' => $latest->height,
                'WFA' => $this->printService->abbreviateStatus($components['WFA']),
                'HFA' => $this->printService->abbreviateStatus($components['HFA']),
                'WFH' => $this->printService->abbreviateStatus($components['WFH']),
                'rehabilitation_status' => $this->printService->getRehabStatus($components['WFA']),
                'date_weighing' => $latest->visit_date,
            ];
        }

        return response()->json([
            'child_name' => trim("{$child->firstname} {$child->lastname}"),
            'sex' => $child->sex,
            'birthdate' => $child->birthdate,
            'baseline' => $baselineData,
            'follow_up' => $followUpData,
        ]);
    }

    public function combined(int $id, Request $request): JsonResponse
    {
        $types = array_filter(explode(',', $request->query('types', '')));

        if (empty($types)) {
            return response()->json(['error' => 'No print types selected.'], 422);
        }

        $allowed = ['profile', 'items-delivered', 'immunization-record'];
        $invalid = array_diff($types, $allowed);

        if (! empty($invalid)) {
            return response()->json([
                'error'   => 'Invalid print types.',
                'invalid' => array_values($invalid),
            ], 422);
        }

        $result = [];

        foreach ($types as $type) {
            $result[$type] = match ($type) {
                'profile'             => $this->buildProfile($id),
                'items-delivered'     => $this->buildItemsDelivered($id),
                'immunization-record' => $this->buildImmunizationRecord($id),
            };
        }

        return response()->json($result);
    }
}
