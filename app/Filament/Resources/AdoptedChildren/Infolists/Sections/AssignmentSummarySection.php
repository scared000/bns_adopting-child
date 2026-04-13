<?php

namespace App\Filament\Resources\AdoptedChildren\Infolists\Sections;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Illuminate\Support\HtmlString;

final class AssignmentSummarySection
{
    public static function make(): Section
    {
        return Section::make()
            ->schema([
                TextEntry::make('Assignment')
                    ->columnSpanFull()
                    ->getStateUsing(fn ($record) => self::renderCard($record)),
            ])
            ->extraAttributes(['style' => 'padding:0;border:none;box-shadow:none;background:transparent;']);
    }

    private static function renderCard(mixed $record): HtmlString
    {
        $assignment  = $record->officeAssignments()->with('bns', 'office')->latest()->first();
        $totalVisits = $record->officeVisits()->count();
        $bnsName     = $assignment?->bns
            ? trim("{$assignment->bns->firstname} {$assignment->bns->lastname}")
            : '—';
        $officeName  = $assignment?->office?->office ?? '—';

        // Colour constants (Davao de Oro gold palette)
        $surface      = '#ffffff';
        $surface2     = '#fffbf2';
        $borderLight  = '#fde68a';
        $borderStrong = '#d97706';
        $textPrimary  = '#1c0a00';
        $textMuted    = '#a16207';
        $gold100      = '#fef3c7';
        $gold200      = '#fde68a';
        $gold600      = '#b45309';
        $shadow       = '0 1px 6px rgba(180,83,9,0.08), 0 0 0 1px rgba(253,230,138,0.3)';

        //Shared style fragments
        $cardStyle = "display:grid;grid-template-columns:1fr 1fr 1fr;border-radius:14px;"
            . "background:{$surface};overflow:hidden;"
            . "border:1px solid {$borderLight};border-top:4px solid {$borderStrong};"
            . "box-shadow:{$shadow};";

        $cellBase = "text-align:center;padding:22px 16px;background:{$surface};";
        $cellMid  = $cellBase . "border-left:1px solid {$borderLight};border-right:1px solid {$borderLight};";

        $labelStyle = "font-size:10px;font-weight:700;text-transform:uppercase;"
            . "letter-spacing:.1em;color:{$textMuted};margin:6px 0 0;";

        $pillStyle = "display:inline-block;margin-top:6px;padding:2px 10px;border-radius:999px;"
            . "font-size:10px;font-weight:800;"
            . "background:{$gold100};color:{$gold600};border:1px solid {$gold200};";

        $hoverOn  = "this.style.background='{$surface2}'";
        $hoverOff = "this.style.background='{$surface}'";

        return new HtmlString("
        <div style=\"{$cardStyle}\">

            <div style=\"{$cellBase}\" onmouseover=\"{$hoverOn}\" onmouseout=\"{$hoverOff}\">
                <p style=\"font-size:30px;font-weight:900;color:{$textPrimary};margin:0;line-height:1;\">
                    {$totalVisits}
                </p>
                <p style=\"{$labelStyle}\">Total Visits</p>
            </div>

            <div style=\"{$cellMid}\" onmouseover=\"{$hoverOn}\" onmouseout=\"{$hoverOff}\">
                <p style=\"font-size:17px;font-weight:800;color:{$textPrimary};margin:0;line-height:1.2;\">
                    {$bnsName}
                </p>
                <p style=\"{$labelStyle}\">Assigned BNS</p>
                <span style=\"{$pillStyle}\">BNS</span>
            </div>

            <div style=\"{$cellBase}\" onmouseover=\"{$hoverOn}\" onmouseout=\"{$hoverOff}\">
                <p style=\"font-size:14px;font-weight:700;color:{$textPrimary};margin:0;line-height:1.2;\">
                    {$officeName}
                </p>
                <p style=\"{$labelStyle}\">Assigned Office</p>
                <span style=\"{$pillStyle}\">Office</span>
            </div>

        </div>");
    }
}
