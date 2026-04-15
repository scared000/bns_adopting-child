<?php

namespace App\Filament\Resources\AdoptedChildren\Infolists\Html;

use Illuminate\Support\HtmlString;

/**
 * Readonly renderer that builds the tabbed Visit History / Visit Items
 * widget (with client-side pagination) as a self-contained HtmlString.
 */
final readonly class VisitHistoryRenderer
{
    public function __construct(private mixed $record) {}

    public function render(): HtmlString
    {
        $visits = $this->record->officeVisits()
            ->with('visitItems', 'bns', 'office')
            ->latest('visit_date')
            ->get();

        $uid         = 'vt_' . $this->record->id;
        $historyRows = $this->buildHistoryRows($visits, $uid);

        [$itemRows, $itemsFooter] = $this->buildItemRows($visits, $uid);

        return new HtmlString(
            $this->buildStyles($uid) .
            $this->buildLayout($uid, $historyRows, $itemRows, $itemsFooter)
        );
    }

    // ─── Styles ──────────────────────────────────────────────────────────────────

    private function buildStyles(string $uid): string
    {
        return "
        <style>
            /* ── Container ── */
            .{$uid}-wrap {
                border-radius: 12px;
                border: 1px solid #e5e7eb;
                overflow: hidden;
            }

            /* ── Tab bar ── */
            .{$uid}-tabbar {
                display: flex;
                gap: 0;
                border-bottom: 1px solid #e5e7eb;
                background: #f9fafb;
                padding: 0 20px;
            }
            .{$uid}-tab {
                padding: 12px 18px;
                font-size: 13px;
                font-weight: 500;
                border: none;
                background: transparent;
                cursor: pointer;
                color: #6b7280;
                border-bottom: 2px solid transparent;
                margin-bottom: -1px;
            }
            .{$uid}-tab.active {
                font-weight: 600;
                color: #f97316;
                border-bottom-color: #f97316;
            }

            /* ── Table chrome ── */
            .{$uid}-thead {
                background: #f9fafb;
            }
            .{$uid}-th {
                padding: 10px 16px;
                text-align: left;
                font-size: 11px;
                font-weight: 600;
                color: #6b7280;
                text-transform: uppercase;
                letter-spacing: .06em;
                white-space: nowrap;
            }
            .{$uid}-th-right  { text-align: right; }
            .{$uid}-th-center { text-align: center; }

            .{$uid}-row {
                border-bottom: 1px solid #f3f4f6;
            }
            .{$uid}-row-orig {
                border-bottom: 1px solid #f3f4f6;
                background: #fefce8;
            }
            .{$uid}-td {
                padding: 12px 16px;
                font-size: 13px;
                color: #374151;
            }
            .{$uid}-td-primary {
                padding: 12px 16px;
                font-size: 13px;
                font-weight: 600;
                white-space: nowrap;
                color: #111827;
            }
            .{$uid}-td-muted {
                padding: 12px 16px;
                font-size: 13px;
                color: #6b7280;
                max-width: 180px;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
            .{$uid}-td-italic {
                padding: 12px 16px;
                font-size: 13px;
                color: #6b7280;
                font-style: italic;
            }

            /* ── Pager ── */
            .{$uid}-pager {
                display: flex;
                align-items: center;
                justify-content: space-between;
                padding: 10px 16px;
                border-top: 1px solid #f3f4f6;
                background: #f9fafb;
            }
            .{$uid}-pager-info {
                font-size: 12px;
                color: #6b7280;
            }
            .{$uid}-pager-btn {
                padding: 5px 12px;
                font-size: 12px;
                border-radius: 6px;
                border: 1px solid #e5e7eb;
                background: #ffffff;
                cursor: pointer;
                color: #374151;
            }
            .{$uid}-pager-btn:disabled {
                cursor: default;
                color: #d1d5db;
            }

            /* ── Footer total row ── */
            .{$uid}-tfoot-row {
                background: #f9fafb;
                border-top: 2px solid #e5e7eb;
            }
            .{$uid}-tfoot-label {
                padding: 12px 16px;
                font-size: 13px;
                font-weight: 700;
                color: #111827;
                text-align: right;
            }
            .{$uid}-tfoot-value {
                padding: 12px 16px;
                font-size: 14px;
                font-weight: 800;
                color: #111827;
                text-align: right;
            }

            /* ── Original pill ── */
            .{$uid}-orig-pill {
                padding: 2px 8px;
                border-radius: 20px;
                font-size: 10px;
                font-weight: 700;
                background: #fde68a;
                color: #92400e;
                text-transform: uppercase;
                letter-spacing: .05em;
            }

            /* ── Status badges ── */
            .{$uid}-badge {
                padding: 3px 10px;
                border-radius: 20px;
                font-size: 11px;
                font-weight: 600;
                white-space: nowrap;
                display: inline-block;
            }
            .{$uid}-badge-danger  { background: #fee2e2; color: #b91c1c; }
            .{$uid}-badge-warning { background: #fef9c3; color: #a16207; }
            .{$uid}-badge-normal  { background: #dcfce7; color: #15803d; }
            .{$uid}-badge-default { background: #f3f4f6; color: #374151; }

            /* ── Empty state ── */
            .{$uid}-empty {
                padding: 40px;
                text-align: center;
                color: #9ca3af;
                font-size: 13px;
            }

            /* Dark mode overrides (.dark on <html>)*/
            .dark .{$uid}-wrap {
                border-color: #374151;
            }

            .dark .{$uid}-tabbar {
                background: #111827;
                border-bottom-color: #374151;
            }
            .dark .{$uid}-tab {
                color: #9ca3af;
            }
            .dark .{$uid}-tab.active {
                color: #fb923c;
                border-bottom-color: #fb923c;
            }

            .dark .{$uid}-thead {
                background: #1f2937;
            }
            .dark .{$uid}-th {
                color: #9ca3af;
            }

            .dark .{$uid}-row {
                border-bottom-color: #374151;
            }
            .dark .{$uid}-row-orig {
                background: #2d2000;
                border-bottom-color: #374151;
            }
            .dark .{$uid}-td          { color: #d1d5db; }
            .dark .{$uid}-td-primary  { color: #f3f4f6; }
            .dark .{$uid}-td-muted    { color: #9ca3af; }
            .dark .{$uid}-td-italic   { color: #9ca3af; }

            .dark .{$uid}-pager {
                background: #1f2937;
                border-top-color: #374151;
            }
            .dark .{$uid}-pager-info  { color: #9ca3af; }
            .dark .{$uid}-pager-btn {
                background: #374151;
                border-color: #4b5563;
                color: #d1d5db;
            }
            .dark .{$uid}-pager-btn:disabled { color: #6b7280; }

            .dark .{$uid}-tfoot-row   { background: #1f2937; border-top-color: #374151; }
            .dark .{$uid}-tfoot-label { color: #f3f4f6; }
            .dark .{$uid}-tfoot-value { color: #f9fafb; }

            .dark .{$uid}-orig-pill   { background: #451a03; color: #fcd34d; }

            .dark .{$uid}-badge-danger  { background: #450a0a; color: #fca5a5; }
            .dark .{$uid}-badge-warning { background: #1c1200; color: #fcd34d; }
            .dark .{$uid}-badge-normal  { background: #052e16; color: #86efac; }
            .dark .{$uid}-badge-default { background: #374151; color: #d1d5db; }

            .dark .{$uid}-empty { color: #6b7280; }
        </style>";
    }

    // Badge class

    private function badgeClass(string $uid, string $status): string
    {
        $s = strtolower($status);

        $variant = match (true) {
            str_contains($s, 'severely') || str_contains($s, 'wasted') || str_contains($s, 'obese') => 'danger',
            str_contains($s, 'underweight') || str_contains($s, 'stunted') ||
            str_contains($s, 'overweight')  || str_contains($s, 'at risk')                          => 'warning',
            str_contains($s, 'normal')                                                               => 'normal',
            default                                                                                  => 'default',
        };

        return "{$uid}-badge {$uid}-badge-{$variant}";
    }

    //  History rows

    private function buildHistoryRows(mixed $visits, string $uid): string
    {
        $record     = $this->record;
        $origHeight = $record->height_cm ? $record->height_cm . ' cm' : '—';
        $origWeight = $record->weight_kg ? $record->weight_kg . ' kg' : '—';

        $statusBadge = $record->nutritional_status
            ? "<span class='" . $this->badgeClass($uid, $record->nutritional_status) . "'>"
            . htmlspecialchars($record->nutritional_status) . "</span>"
            : "<span class='{$uid}-td-muted' style='font-style:italic;'>—</span>";

        $rows = "
        <tr class='{$uid}-row-orig'>
            <td class='{$uid}-td-primary'>
                <span class='{$uid}-orig-pill'>📋 Original</span>
            </td>
            <td class='{$uid}-td-italic'>Initial record on enrollment</td>
            <td class='{$uid}-td'>{$origHeight}</td>
            <td class='{$uid}-td'>{$origWeight}</td>
            <td class='{$uid}-td'>{$statusBadge}</td>
        </tr>";

        if ($visits->isEmpty()) {
            return $rows . "<tr><td colspan='5' class='{$uid}-empty'>No visits recorded yet.</td></tr>";
        }

        foreach ($visits as $visit) {
            $date    = $visit->visit_date?->format('M d, Y') ?? '—';
            $height  = $visit->height ? $visit->height . ' cm' : '—';
            $weight  = $visit->weight ? $visit->weight . ' kg' : '—';
            $status  = htmlspecialchars($visit->status ?? '—');
            $address = htmlspecialchars($visit->visit_address ?? '—');
            $bc      = $this->badgeClass($uid, $visit->status ?? '');

            $rows .= "
            <tr class='{$uid}-row {$uid}_hrow'>
                <td class='{$uid}-td-primary'>{$date}</td>
                <td class='{$uid}-td-muted'>{$address}</td>
                <td class='{$uid}-td'>{$height}</td>
                <td class='{$uid}-td'>{$weight}</td>
                <td class='{$uid}-td'>
                    <span class='{$bc}'>{$status}</span>
                </td>
            </tr>";
        }

        return $rows;
    }

    // Item rows

    /** @return array{0: string, 1: string} [$itemRows, $footer] */
    private function buildItemRows(mixed $visits, string $uid): array
    {
        $itemRows   = '';
        $hasItems   = false;
        $grandTotal = 0.0;

        foreach ($visits as $visit) {
            foreach ($visit->visitItems as $item) {
                $hasItems = true;
                $date     = $visit->visit_date?->format('M d, Y') ?? '—';
                $desc     = htmlspecialchars($item->Item_description ?? '—');
                $qty      = htmlspecialchars((string) ($item->item_quantity ?? '—'));
                $rawAmt   = $item->item_amount ?? null;
                $amount   = $rawAmt !== null ? '₱' . number_format((float) $rawAmt, 2) : '—';

                $grandTotal += (float) ($rawAmt ?? 0);
                $office = htmlspecialchars($visit->office?->short_name ?? $visit->bns?->name ?? '—');
                $itemRows .= "
                <tr class='{$uid}-row {$uid}_irow'>
                    <td class='{$uid}-td-primary'>{$date}</td>
                    <td class='{$uid}-td-muted'>{$office}</td>
                    <td class='{$uid}-td'>{$desc}</td>
                    <td class='{$uid}-td' style='text-align:center;'>{$qty}</td>
                    <td class='{$uid}-td' style='text-align:right;'>{$amount}</td>
                </tr>";
            }
        }

        if (! $hasItems) {
            $itemRows = "<tr><td colspan='5' class='{$uid}-empty'>No items distributed yet.</td></tr>";
        }

        $totalFormatted = '₱' . number_format($grandTotal, 2);
        $footer = $hasItems ? "
        <tfoot>
            <tr class='{$uid}-tfoot-row'>
                <td colspan='4' class='{$uid}-tfoot-label'>Total Amount</td>
                <td class='{$uid}-tfoot-value'>{$totalFormatted}</td>
            </tr>
        </tfoot>" : '';

        return [$itemRows, $footer];
    }

    // Layout

    private function buildLayout(
        string $uid,
        string $historyRows,
        string $itemRows,
        string $itemsFooter
    ): string {
        return "
        <div class='{$uid}-wrap'>

            <div class='{$uid}-tabbar'>
                <button id='{$uid}_btn_history' class='{$uid}-tab active'>
                    🗓️ Visit History
                </button>
                <button id='{$uid}_btn_items' class='{$uid}-tab'>
                    📦 Item Distributed
                </button>
            </div>

            <div id='{$uid}_history' style='display:block;overflow-x:auto;'>
                <table style='width:100%;border-collapse:collapse;'>
                    <thead class='{$uid}-thead'>
                        <tr>
                            <th class='{$uid}-th'>Visit Date</th>
                            <th class='{$uid}-th'>Address</th>
                            <th class='{$uid}-th'>Height</th>
                            <th class='{$uid}-th'>Weight</th>
                            <th class='{$uid}-th'>Nutritional Status</th>
                        </tr>
                    </thead>
                    <tbody id='{$uid}_hbody'>{$historyRows}</tbody>
                </table>
                <div id='{$uid}_hpager' class='{$uid}-pager'></div>
            </div>

            <div id='{$uid}_items' style='display:none;overflow-x:auto;'>
                <table style='width:100%;border-collapse:collapse;'>
                    <thead class='{$uid}-thead'>
                        <tr>
                            <th class='{$uid}-th'>Visit Date</th>
                            <th class='{$uid}-th'>Office</th>
                            <th class='{$uid}-th'>Item Description</th>
                            <th class='{$uid}-th {$uid}-th-center'>Quantity</th>
                            <th class='{$uid}-th {$uid}-th-right'>Amount</th>
                        </tr>
                    </thead>
                    <tbody id='{$uid}_ibody'>{$itemRows}</tbody>
                    {$itemsFooter}
                </table>
                <div id='{$uid}_ipager' class='{$uid}-pager'></div>
            </div>
        </div>

        " . $this->buildScript($uid);
    }

    private function buildScript(string $uid): string
    {
        // Tab switching uses class toggling instead of cssText
        // so dark-mode CSS overrides are never clobbered by JS.
        return "
        <script>
        (function () {
            var PER_PAGE = 6;
            var uid      = '{$uid}';
            var btnH     = document.getElementById(uid + '_btn_history');
            var btnI     = document.getElementById(uid + '_btn_items');
            var tabH     = document.getElementById(uid + '_history');
            var tabI     = document.getElementById(uid + '_items');

            btnH.addEventListener('click', function () {
                tabH.style.display = 'block'; tabI.style.display = 'none';
                btnH.classList.add('active');  btnI.classList.remove('active');
            });
            btnI.addEventListener('click', function () {
                tabH.style.display = 'none'; tabI.style.display = 'block';
                btnI.classList.add('active'); btnH.classList.remove('active');
            });

            function paginate(rowClass, pagerId) {
                var rows  = Array.from(document.querySelectorAll('.' + rowClass));
                var pager = document.getElementById(pagerId);
                var page  = 1;
                var total = rows.length;
                var pages = Math.ceil(total / PER_PAGE);
                if (total === 0) { pager.style.display = 'none'; return; }

                function render() {
                    rows.forEach(function (r, i) {
                        r.style.display = (i >= (page - 1) * PER_PAGE && i < page * PER_PAGE) ? '' : 'none';
                    });
                    var from = (page - 1) * PER_PAGE + 1;
                    var to   = Math.min(page * PER_PAGE, total);

                    var prev = document.createElement('button');
                    prev.textContent = '\u2039 Prev';
                    prev.disabled    = (page === 1);
                    prev.className   = uid + '-pager-btn';
                    prev.addEventListener('click', function () { if (page > 1) { page--; render(); } });

                    var next = document.createElement('button');
                    next.textContent = 'Next \u203a';
                    next.disabled    = (page === pages);
                    next.className   = uid + '-pager-btn';
                    next.addEventListener('click', function () { if (page < pages) { page++; render(); } });

                    var info = document.createElement('span');
                    info.className   = uid + '-pager-info';
                    info.textContent = 'Showing ' + from + '\u2013' + to + ' of ' + total;

                    var controls = document.createElement('div');
                    controls.style.cssText = 'display:flex;gap:6px;';
                    controls.appendChild(prev);
                    controls.appendChild(next);

                    pager.innerHTML = '';
                    pager.appendChild(info);
                    pager.appendChild(controls);
                }
                render();
            }

            paginate(uid + '_hrow', uid + '_hpager');
            paginate(uid + '_irow', uid + '_ipager');
        }());
        </script>";
    }
}
