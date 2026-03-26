<?php

namespace App\Filament\Widgets;

use App\Models\AdoptedChild;
use Filament\Widgets\Widget;

class NutritionStatusWidget extends Widget
{
    protected static ?int $sort = 2;
    protected string $view = 'filament.widgets.nutrition-status-widget';
    protected int|string|array $columnSpan = 1;

    public function getNutritionDataProperty(): array
    {
        $total    = AdoptedChild::count();
        $statuses = [
            'Normal'        => ['color' => '#22c55e', 'dot' => 'bg-green-500'],
            'Underweight'   => ['color' => '#f59e0b', 'dot' => 'bg-yellow-500'],
            'Severely'      => ['color' => '#ef4444', 'dot' => 'bg-red-500'],
            'Overweight'    => ['color' => '#3b82f6', 'dot' => 'bg-blue-500'],
            'Stunted'       => ['color' => '#8b5cf6', 'dot' => 'bg-purple-500'],
            'Wasted'        => ['color' => '#f97316', 'dot' => 'bg-orange-500'],
        ];

        $result = [];
        foreach ($statuses as $label => $config) {
            $count = AdoptedChild::where('nutritional_status', 'like', "%{$label}%")->count();
            if ($count > 0) {
                $result[] = [
                    'label'   => $label,
                    'count'   => $count,
                    'percent' => $total > 0 ? round(($count / $total) * 100) : 0,
                    'color'   => $config['color'],
                    'dot'     => $config['dot'],
                    'width'   => $total > 0 ? round(($count / $total) * 100) : 0,
                ];
            }
        }

        return ['items' => $result, 'total' => $total];
    }
}
