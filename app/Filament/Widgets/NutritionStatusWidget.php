<?php

namespace App\Filament\Widgets;

use App\Models\AdoptedChild;
use App\Models\OfficeChildVisit;
use Filament\Widgets\Widget;

class NutritionStatusWidget extends Widget
{
    protected static ?int $sort = 2;
    protected string $view = 'filament.widgets.nutrition-status-widget';
    protected int|string|array $columnSpan = 2;

    public function getNutritionDataProperty(): array
    {
        // Get all children with their latest visit status
        $children = \App\Models\AdoptedChild::with([
            'officeVisits' => fn ($q) => $q->orderBy('visit_date', 'desc')->limit(1),
        ])->get();

        $total = $children->count();

        $dominant = $children->map(function ($child) {
            // Use latest visit status if available, fallback to static field
            $status = $child->officeVisits->first()?->status
                ?? $child->nutritional_status
                ?? '';

            $s = strtolower($status);

            if (empty($s)) return 'Normal';

            return match(true) {
                str_contains($s, 'severely')    => 'Severely UW',
                str_contains($s, 'wasted')      => 'Obese / Wasted',
                str_contains($s, 'obese')       => 'Obese / Wasted',
                str_contains($s, 'stunted')     => 'Stunted',
                str_contains($s, 'overweight')  => 'Overweight',
                str_contains($s, 'underweight') => 'Underweight',
                str_contains($s, 'normal')      => 'Normal',
                default                         => 'Normal',
            };
        });

        $groups = [
            'Normal'         => ['color' => '#22c55e', 'bg' => 'bg-green-500'],
            'Underweight'    => ['color' => '#f59e0b', 'bg' => 'bg-yellow-500'],
            'Severely UW'    => ['color' => '#ef4444', 'bg' => 'bg-red-500'],
            'Stunted'        => ['color' => '#8b5cf6', 'bg' => 'bg-purple-500'],
            'Overweight'     => ['color' => '#f97316', 'bg' => 'bg-orange-500'],
            'Obese / Wasted' => ['color' => '#dc2626', 'bg' => 'bg-red-700'],
        ];

        $result = [];
        foreach ($groups as $label => $config) {
            $count = $dominant->filter(fn ($d) => $d === $label)->count();
            $result[] = [
                'label'   => $label,
                'count'   => $count,
                'percent' => $total > 0 ? round(($count / $total) * 100) : 0,
                'color'   => $config['color'],
                'bg'      => $config['bg'],
                'width'   => $total > 0 ? round(($count / $total) * 100) : 0,
            ];
        }

        return ['items' => $result, 'total' => $total];
    }
}
