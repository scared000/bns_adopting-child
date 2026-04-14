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

        return new HtmlString("
        <style>
            .assign-card {
                display: grid;
                grid-template-columns: 1fr 1fr 1fr;
                border-radius: 14px;
                overflow: hidden;
                border: 1px solid #fde68a;
                border-top: 4px solid #d97706;
                box-shadow: 0 1px 6px rgba(180,83,9,0.08), 0 0 0 1px rgba(253,230,138,0.3);
                background: #ffffff;
            }
            .assign-cell {
                text-align: center;
                padding: 22px 16px;
                background: #ffffff;
                transition: background 0.15s;
            }
            .assign-cell:hover {
                background: #fffbf2;
            }
            .assign-cell--mid {
                border-left: 1px solid #fde68a;
                border-right: 1px solid #fde68a;
            }
            .assign-value--xl {
                font-size: 30px;
                font-weight: 900;
                color: #1c0a00;
                margin: 0;
                line-height: 1;
            }
            .assign-value--lg {
                font-size: 17px;
                font-weight: 800;
                color: #1c0a00;
                margin: 0;
                line-height: 1.2;
            }
            .assign-value--md {
                font-size: 14px;
                font-weight: 700;
                color: #1c0a00;
                margin: 0;
                line-height: 1.2;
            }
            .assign-label {
                font-size: 10px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: .1em;
                color: #a16207;
                margin: 6px 0 0;
            }
            .assign-pill {
                display: inline-block;
                margin-top: 6px;
                padding: 2px 10px;
                border-radius: 999px;
                font-size: 10px;
                font-weight: 800;
                background: #fef3c7;
                color: #b45309;
                border: 1px solid #fde68a;
            }

            /* ── Dark mode overrides (Filament adds .dark on <html>) ── */
            .dark .assign-card {
                background: #1c1400;
                border-color: #78350f;
                border-top-color: #d97706;
                box-shadow: 0 1px 6px rgba(0,0,0,0.4), 0 0 0 1px rgba(120,53,15,0.4);
            }
            .dark .assign-cell {
                background: #1c1400;
            }
            .dark .assign-cell:hover {
                background: #27190a;
            }
            .dark .assign-cell--mid {
                border-left-color: #78350f;
                border-right-color: #78350f;
            }
            .dark .assign-value--xl,
            .dark .assign-value--lg,
            .dark .assign-value--md {
                color: #fef3c7;
            }
            .dark .assign-label {
                color: #fbbf24;
            }
            .dark .assign-pill {
                background: #292101;
                color: #fbbf24;
                border-color: #78350f;
            }
        </style>

        <div class=\"assign-card\">

            <div class=\"assign-cell\">
                <p class=\"assign-value--xl\">{$totalVisits}</p>
                <p class=\"assign-label\">Total Visits</p>
            </div>

            <div class=\"assign-cell assign-cell--mid\">
                <p class=\"assign-value--lg\">{$bnsName}</p>
                <p class=\"assign-label\">Assigned BNS</p>
                <span class=\"assign-pill\">BNS</span>
            </div>

            <div class=\"assign-cell\">
                <p class=\"assign-value--md\">{$officeName}</p>
                <p class=\"assign-label\">Assigned Office</p>
                <span class=\"assign-pill\">Office</span>
            </div>

        </div>");
    }
}
