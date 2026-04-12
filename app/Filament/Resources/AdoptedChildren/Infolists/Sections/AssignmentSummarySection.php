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
        <div style='display:grid;grid-template-columns:1fr 1fr 1fr;gap:0;border-top:4px solid #f97316;
                    border-radius:12px;background:#fff;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,0.08);'>
            <div style='text-align:center;padding:20px 16px;'>
                <p style='font-size:28px;font-weight:800;color:#111827;margin:0;'>{$totalVisits}</p>
                <p style='font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.08em;color:#9ca3af;margin:4px 0 0;'>
                    Total Visits</p>
            </div>
            <div style='text-align:center;padding:20px 16px;border-left:1px solid #f3f4f6;border-right:1px solid #f3f4f6;'>
                <p style='font-size:18px;font-weight:700;color:#111827;margin:0;'>{$bnsName}</p>
                <p style='font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.08em;color:#9ca3af;margin:4px 0 0;'>
                    Assigned BNS</p>
            </div>
            <div style='text-align:center;padding:20px 16px;'>
                <p style='font-size:15px;font-weight:700;color:#111827;margin:0;'>{$officeName}</p>
                <p style='font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.08em;color:#9ca3af;margin:4px 0 0;'>
                    Assigned Office</p>
            </div>
        </div>");
    }
}
