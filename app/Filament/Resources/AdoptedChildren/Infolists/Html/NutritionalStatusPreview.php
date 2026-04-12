<?php

namespace App\Filament\Resources\AdoptedChildren\Infolists\Html;

use App\Helpers\NutritionalStatus;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\HtmlString;

/**
 * Readonly value object that computes and renders the live nutritional status
 * preview card shown inside the Record Visit modal.
 */
final readonly class NutritionalStatusPreview
{
    public function __construct(
        private ?int    $months,
        private float   $weight,
        private float   $height,
        private ?string $sex,
    ) {}

    /** Named constructor — hydrates from a Filament live Get closure. */
    public static function from(Get $get): self
    {
        return new self(
            months: $get('age_months'),
            weight: (float) $get('weight'),
            height: (float) $get('height'),
            sex:    $get('sex'),
        );
    }

    public function render(): HtmlString
    {
        if ($this->isIncomplete()) {
            return $this->renderPlaceholder();
        }

        $status = NutritionalStatus::classify(
            (int) $this->months,
            $this->weight,
            $this->height,
            $this->sex
        );

        $config = $this->resolveConfig($status);
        $bmi    = round($this->weight / (($this->height / 100) ** 2), 1);

        return $this->renderCard($status, $config, $bmi);
    }

    // Helpers

    private function isIncomplete(): bool
    {
        return $this->months === null
            || $this->weight <= 0
            || $this->height <= 0
            || ! in_array($this->sex, ['male', 'female'], strict: true);
    }

    private function renderPlaceholder(): HtmlString
    {
        return new HtmlString('
            <div style="display:flex;align-items:center;gap:12px;padding:16px 20px;
                        border-radius:10px;border:2px dashed #d1d5db;background:#f9fafb;">
                <svg style="width:24px;height:24px;color:#9ca3af;flex-shrink:0;"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0
                             002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2
                             2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2
                             2 0 01-2-2z"/>
                </svg>
                <div>
                    <p style="margin:0;font-size:13px;font-weight:600;color:#6b7280;">
                        Nutritional Status</p>
                    <p style="margin:0;font-size:12px;color:#9ca3af;">
                        Enter height &amp; weight to compute status</p>
                </div>
            </div>');
    }

    /**
     * @return array{bg:string,border:string,text:string,icon_bg:string,icon_color:string,icon:string}
     */
    private function resolveConfig(string $status): array
    {
        return match (true) {
            str_contains($status, 'Obese'),
            str_contains($status, 'Severely Underweight'),
            str_contains($status, 'SUW'),
            str_contains($status, 'SST'),
            str_contains($status, 'Wasted (W)') => [
                'bg' => '#fef2f2', 'border' => '#fca5a5', 'text' => '#dc2626',
                'icon_bg' => '#fee2e2', 'icon_color' => '#dc2626',
                'icon' => 'M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            ],
            str_contains($status, 'Overweight'),
            str_contains($status, 'At Risk'),
            str_contains($status, 'OW') => [
                'bg' => '#eff6ff', 'border' => '#93c5fd', 'text' => '#2563eb',
                'icon_bg' => '#dbeafe', 'icon_color' => '#2563eb',
                'icon' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            ],
            str_contains($status, 'Underweight'),
            str_contains($status, 'Stunted'),
            str_contains($status, 'UW'),
            str_contains($status, 'ST'),
            str_contains($status, 'MW') => [
                'bg' => '#fffbeb', 'border' => '#fcd34d', 'text' => '#d97706',
                'icon_bg' => '#fef3c7', 'icon_color' => '#d97706',
                'icon' => 'M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            ],
            default => [
                'bg' => '#f0fdf4', 'border' => '#86efac', 'text' => '#16a34a',
                'icon_bg' => '#dcfce7', 'icon_color' => '#16a34a',
                'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
            ],
        };
    }

    private function renderCard(string $status, array $config, float $bmi): HtmlString
    {
        return new HtmlString("
            <div style=\"padding:20px;border-radius:12px;border:2px solid {$config['border']};background:{$config['bg']};\">
                <div style=\"display:flex;align-items:center;gap:14px;\">
                    <div style=\"width:48px;height:48px;border-radius:50%;background:{$config['icon_bg']};
                                display:flex;align-items:center;justify-content:center;flex-shrink:0;\">
                        <svg style=\"width:26px;height:26px;\" fill=\"none\"
                             stroke=\"{$config['icon_color']}\" viewBox=\"0 0 24 24\">
                            <path stroke-linecap=\"round\" stroke-linejoin=\"round\"
                                  stroke-width=\"2\" d=\"{$config['icon']}\"/>
                        </svg>
                    </div>
                    <div>
                        <p style=\"margin:0;font-size:11px;font-weight:600;text-transform:uppercase;
                                   letter-spacing:0.08em;color:{$config['text']};opacity:0.8;\">
                            Nutritional Status</p>
                        <p style=\"margin:4px 0 0;font-size:20px;font-weight:800;color:{$config['text']};\">
                            {$status}</p>
                    </div>
                </div>
                <div style=\"display:flex;gap:20px;margin-top:12px;padding-top:12px;
                            border-top:1px solid {$config['border']};\">
                    <div style=\"text-align:center;\">
                        <p style=\"margin:0;font-size:11px;color:{$config['text']};font-weight:600;
                                   text-transform:uppercase;letter-spacing:0.05em;\">Height</p>
                        <p style=\"margin:4px 0 0;font-size:16px;font-weight:700;color:{$config['text']};\">{$this->height} cm</p>
                    </div>
                    <div style=\"text-align:center;\">
                        <p style=\"margin:0;font-size:11px;color:{$config['text']};font-weight:600;
                                   text-transform:uppercase;letter-spacing:0.05em;\">Weight</p>
                        <p style=\"margin:4px 0 0;font-size:16px;font-weight:700;color:{$config['text']};\">{$this->weight} kg</p>
                    </div>
                    <div style=\"text-align:center;\">
                        <p style=\"margin:0;font-size:11px;color:{$config['text']};font-weight:600;
                                   text-transform:uppercase;letter-spacing:0.05em;\">BMI</p>
                        <p style=\"margin:4px 0 0;font-size:16px;font-weight:700;color:{$config['text']};\">{$bmi}</p>
                    </div>
                    <div style=\"text-align:center;\">
                        <p style=\"margin:0;font-size:11px;color:{$config['text']};font-weight:600;
                                   text-transform:uppercase;letter-spacing:0.05em;\">Age</p>
                        <p style=\"margin:4px 0 0;font-size:16px;font-weight:700;color:{$config['text']};\">{$this->months} mos</p>
                    </div>
                </div>
            </div>");
    }
}
