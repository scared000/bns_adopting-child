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

        $variant = $this->resolveVariant($status);
        $bmi     = round($this->weight / (($this->height / 100) ** 2), 1);

        return $this->renderCard($status, $variant, $bmi);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────────

    private function isIncomplete(): bool
    {
        return $this->months === null
            || $this->weight <= 0
            || $this->height <= 0
            || ! in_array($this->sex, ['male', 'female'], strict: true);
    }

    /**
     * Resolves a semantic variant name ('danger'|'warning'|'info'|'success')
     * from the status string. All actual colours live in the <style> block.
     */
    private function resolveVariant(string $status): string
    {
        return match (true) {
            str_contains($status, 'Obese'),
            str_contains($status, 'Severely Underweight'),
            str_contains($status, 'SUW'),
            str_contains($status, 'SST'),
            str_contains($status, 'Wasted (W)') => 'danger',

            str_contains($status, 'Overweight'),
            str_contains($status, 'At Risk'),
            str_contains($status, 'OW')          => 'info',

            str_contains($status, 'Underweight'),
            str_contains($status, 'Stunted'),
            str_contains($status, 'UW'),
            str_contains($status, 'ST'),
            str_contains($status, 'MW')           => 'warning',

            default                               => 'success',
        };
    }

    private function resolveIcon(string $variant): string
    {
        return $variant === 'success'
            ? 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'
            : 'M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z';
    }

    // ─── Renderers ───────────────────────────────────────────────────────────────

    private function renderPlaceholder(): HtmlString
    {
        return new HtmlString('
            <style>
                .ns-placeholder {
                    display: flex;
                    align-items: center;
                    gap: 12px;
                    padding: 16px 20px;
                    border-radius: 10px;
                    border: 2px dashed #d1d5db;
                    background: #f9fafb;
                }
                .ns-placeholder-icon  { width: 24px; height: 24px; color: #9ca3af; flex-shrink: 0; }
                .ns-placeholder-title { margin: 0; font-size: 13px; font-weight: 600; color: #6b7280; }
                .ns-placeholder-hint  { margin: 0; font-size: 12px; color: #9ca3af; }

                .dark .ns-placeholder { border-color: #4b5563; background: #1f2937; }
                .dark .ns-placeholder-title { color: #9ca3af; }
                .dark .ns-placeholder-hint  { color: #6b7280; }
            </style>
            <div class="ns-placeholder">
                <svg class="ns-placeholder-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0
                             002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2
                             2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2
                             2 0 01-2-2z"/>
                </svg>
                <div>
                    <p class="ns-placeholder-title">Nutritional Status</p>
                    <p class="ns-placeholder-hint">Enter height &amp; weight to compute status</p>
                </div>
            </div>');
    }

    private function renderCard(string $status, string $variant, float $bmi): HtmlString
    {
        $icon = $this->resolveIcon($variant);

        return new HtmlString("
            <style>
                /* ── Base card ── */
                .ns-card           { padding: 20px; border-radius: 12px; border-width: 2px; border-style: solid; }
                .ns-card-header    { display: flex; align-items: center; gap: 14px; }
                .ns-icon-wrap      { width: 48px; height: 48px; border-radius: 50%;
                                     display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
                .ns-eyebrow        { margin: 0; font-size: 11px; font-weight: 600;
                                     text-transform: uppercase; letter-spacing: 0.08em; opacity: 0.8; }
                .ns-status-text    { margin: 4px 0 0; font-size: 20px; font-weight: 800; }
                .ns-stats          { display: flex; gap: 20px; margin-top: 12px; padding-top: 12px; }
                .ns-stat           { text-align: center; }
                .ns-stat-label     { margin: 0; font-size: 11px; font-weight: 600;
                                     text-transform: uppercase; letter-spacing: 0.05em; }
                .ns-stat-value     { margin: 4px 0 0; font-size: 16px; font-weight: 700; }
                .ns-divider        { border-top-width: 1px; border-top-style: solid; }

                /* ── Variant: danger ── */
                .ns-card.danger             { background: #fef2f2; border-color: #fca5a5; }
                .ns-card.danger .ns-icon-wrap { background: #fee2e2; }
                .ns-card.danger svg,
                .ns-card.danger .ns-eyebrow,
                .ns-card.danger .ns-status-text,
                .ns-card.danger .ns-stat-label,
                .ns-card.danger .ns-stat-value { color: #dc2626; }
                .ns-card.danger .ns-divider  { border-top-color: #fca5a5; }

                /* ── Variant: info ── */
                .ns-card.info               { background: #eff6ff; border-color: #93c5fd; }
                .ns-card.info .ns-icon-wrap { background: #dbeafe; }
                .ns-card.info svg,
                .ns-card.info .ns-eyebrow,
                .ns-card.info .ns-status-text,
                .ns-card.info .ns-stat-label,
                .ns-card.info .ns-stat-value { color: #2563eb; }
                .ns-card.info .ns-divider    { border-top-color: #93c5fd; }

                /* ── Variant: warning ── */
                .ns-card.warning               { background: #fffbeb; border-color: #fcd34d; }
                .ns-card.warning .ns-icon-wrap { background: #fef3c7; }
                .ns-card.warning svg,
                .ns-card.warning .ns-eyebrow,
                .ns-card.warning .ns-status-text,
                .ns-card.warning .ns-stat-label,
                .ns-card.warning .ns-stat-value { color: #d97706; }
                .ns-card.warning .ns-divider   { border-top-color: #fcd34d; }

                /* ── Variant: success ── */
                .ns-card.success               { background: #f0fdf4; border-color: #86efac; }
                .ns-card.success .ns-icon-wrap { background: #dcfce7; }
                .ns-card.success svg,
                .ns-card.success .ns-eyebrow,
                .ns-card.success .ns-status-text,
                .ns-card.success .ns-stat-label,
                .ns-card.success .ns-stat-value { color: #16a34a; }
                .ns-card.success .ns-divider   { border-top-color: #86efac; }

                /* ════════════════════════════
                   Dark mode overrides
                   ════════════════════════════ */
                .dark .ns-card.danger    { background: #2d0808; border-color: #7f1d1d; }
                .dark .ns-card.danger .ns-icon-wrap { background: #450a0a; }
                .dark .ns-card.danger svg,
                .dark .ns-card.danger .ns-eyebrow,
                .dark .ns-card.danger .ns-status-text,
                .dark .ns-card.danger .ns-stat-label,
                .dark .ns-card.danger .ns-stat-value { color: #fca5a5; }
                .dark .ns-card.danger .ns-divider    { border-top-color: #7f1d1d; }

                .dark .ns-card.info      { background: #0c1a3d; border-color: #1d4ed8; }
                .dark .ns-card.info .ns-icon-wrap    { background: #1e3a8a; }
                .dark .ns-card.info svg,
                .dark .ns-card.info .ns-eyebrow,
                .dark .ns-card.info .ns-status-text,
                .dark .ns-card.info .ns-stat-label,
                .dark .ns-card.info .ns-stat-value   { color: #93c5fd; }
                .dark .ns-card.info .ns-divider      { border-top-color: #1d4ed8; }

                .dark .ns-card.warning   { background: #1c1200; border-color: #92400e; }
                .dark .ns-card.warning .ns-icon-wrap { background: #451a03; }
                .dark .ns-card.warning svg,
                .dark .ns-card.warning .ns-eyebrow,
                .dark .ns-card.warning .ns-status-text,
                .dark .ns-card.warning .ns-stat-label,
                .dark .ns-card.warning .ns-stat-value { color: #fcd34d; }
                .dark .ns-card.warning .ns-divider   { border-top-color: #92400e; }

                .dark .ns-card.success   { background: #052e16; border-color: #166534; }
                .dark .ns-card.success .ns-icon-wrap { background: #14532d; }
                .dark .ns-card.success svg,
                .dark .ns-card.success .ns-eyebrow,
                .dark .ns-card.success .ns-status-text,
                .dark .ns-card.success .ns-stat-label,
                .dark .ns-card.success .ns-stat-value { color: #86efac; }
                .dark .ns-card.success .ns-divider   { border-top-color: #166534; }
            </style>

            <div class='ns-card {$variant}'>
                <div class='ns-card-header'>
                    <div class='ns-icon-wrap'>
                        <svg style='width:26px;height:26px;' fill='none' stroke='currentColor' viewBox='0 0 24 24'>
                            <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='{$icon}'/>
                        </svg>
                    </div>
                    <div>
                        <p class='ns-eyebrow'>Nutritional Status</p>
                        <p class='ns-status-text'>{$status}</p>
                    </div>
                </div>
                <div class='ns-stats ns-divider'>
                    <div class='ns-stat'>
                        <p class='ns-stat-label'>Height</p>
                        <p class='ns-stat-value'>{$this->height} cm</p>
                    </div>
                    <div class='ns-stat'>
                        <p class='ns-stat-label'>Weight</p>
                        <p class='ns-stat-value'>{$this->weight} kg</p>
                    </div>
                    <div class='ns-stat'>
                        <p class='ns-stat-label'>BMI</p>
                        <p class='ns-stat-value'>{$bmi}</p>
                    </div>
                    <div class='ns-stat'>
                        <p class='ns-stat-label'>Age</p>
                        <p class='ns-stat-value'>{$this->months} mos</p>
                    </div>
                </div>
            </div>");
    }
}
