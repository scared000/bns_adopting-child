<?php

namespace App\Filament\Resources\AdoptedChildren\Infolists\Sections;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Illuminate\Support\HtmlString;

final class FamilyMembersSection
{
    public static function make(): Section
    {
        return Section::make('👨‍👩‍👧‍👦 Family Members')
            ->schema([
                TextEntry::make('familyMembers')
                    ->hiddenLabel()
                    ->getStateUsing(fn ($record) => $record)
                    ->formatStateUsing(fn ($state): HtmlString => self::renderTable($state)),
            ]);
    }

    private static function renderTable(mixed $record): HtmlString
    {
        $members = $record->familyMembers;

        if ($members->isEmpty()) {
            return new HtmlString('
            <div style="display:flex;flex-direction:column;align-items:center;justify-content:center;
                        padding:32px 16px;border-radius:10px;border:2px dashed #e5e7eb;
                        background:#f9fafb;text-align:center;">
                <p style="margin:0;font-size:14px;font-weight:600;color:#374151;">No Family Members</p>
                <p style="margin:4px 0 0;font-size:13px;color:#9ca3af;">
                    No family members have been recorded for this child yet.</p>
            </div>');
        }

        $rows = implode('', array_map(
            static fn ($member) => self::buildMemberRow($member),
            $members->all()
        ));

        return new HtmlString("
        <table style=\"width:100%;table-layout:fixed;border-collapse:collapse;\">
            <thead>
                <tr style=\"border-bottom:2px solid #d1d5db;\">
                    <th style=\"padding-bottom:8px;text-align:left;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;width:33%;\">Full Name</th>
                    <th style=\"padding-bottom:8px;text-align:left;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;width:33%;\">Weight</th>
                    <th style=\"padding-bottom:8px;text-align:left;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;width:33%;\">Nutritional Status</th>
                </tr>
            </thead>
            <tbody>{$rows}</tbody>
        </table>");
    }

    private static function buildMemberRow(mixed $member): string
    {
        $name   = $member->fam_member_fullname ?? '—';
        $weight = $member->fam_member_actual_weight
            ? $member->fam_member_actual_weight . ' kg'
            : '—';
        $status = match ($member->fam_member_nutrition_status) {
            'normal'      => 'Normal',
            'underweight' => 'Underweight',
            'overweight'  => 'Overweight',
            'server_uw'   => 'Severely UW',
            default       => '—',
        };

        return "
        <tr style=\"border-bottom:1px solid #e5e7eb;\">
            <td style=\"padding:8px 0;font-size:14px;width:33%;\">{$name}</td>
            <td style=\"padding:8px 0;font-size:14px;width:33%;\">{$weight}</td>
            <td style=\"padding:8px 0;font-size:14px;width:33%;\">{$status}</td>
        </tr>";
    }
}
