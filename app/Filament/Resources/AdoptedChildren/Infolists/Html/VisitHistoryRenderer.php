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
            $this->buildLayout($uid, $historyRows, $itemRows, $itemsFooter)
        );
    }

    // Badge style

    private function badgeStyle(string $status): string
    {
        $s = strtolower($status);

        return match (true) {
            str_contains($s, 'severely') || str_contains($s, 'wasted') || str_contains($s, 'obese')
            => 'background:#fee2e2;color:#b91c1c;',
            str_contains($s, 'underweight') || str_contains($s, 'stunted') ||
            str_contains($s, 'overweight')  || str_contains($s, 'at risk')
            => 'background:#fef9c3;color:#a16207;',
            str_contains($s, 'normal')
            => 'background:#dcfce7;color:#15803d;',
            default
            => 'background:#f3f4f6;color:#374151;',
        };
    }

    // History rows

    private function buildHistoryRows(mixed $visits, string $uid): string
    {
        $record     = $this->record;
        $origHeight = $record->height_cm ? $record->height_cm . ' cm' : '—';
        $origWeight = $record->weight_kg ? $record->weight_kg . ' kg' : '—';

        $statusBadge = $record->nutritional_status
            ? "<span style='padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;" .
            $this->badgeStyle($record->nutritional_status) .
            "white-space:nowrap;display:inline-block;'>" .
            htmlspecialchars($record->nutritional_status) . "</span>"
            : "<span style='color:#6b7280;font-style:italic;'>—</span>";

        $rows = "
        <tr style='border-bottom:1px solid #f3f4f6;background:#fefce8;'>
            <td style='padding:12px 16px;font-size:13px;white-space:nowrap;color:#111827;'>
                <span style='padding:2px 8px;border-radius:20px;font-size:10px;font-weight:700;
                             background:#fde68a;color:#92400e;text-transform:uppercase;letter-spacing:.05em;'>
                    📋 Original
                </span>
            </td>
            <td style='padding:12px 16px;font-size:13px;color:#6b7280;font-style:italic;'>
                Initial record on enrollment</td>
            <td style='padding:12px 16px;font-size:13px;color:#374151;font-weight:600;'>{$origHeight}</td>
            <td style='padding:12px 16px;font-size:13px;color:#374151;font-weight:600;'>{$origWeight}</td>
            <td style='padding:12px 16px;font-size:13px;'>{$statusBadge}</td>
        </tr>";

        if ($visits->isEmpty()) {
            return $rows . "<tr><td colspan='5' style='padding:40px;text-align:center;color:#9ca3af;font-size:13px;'>
                No visits recorded yet.</td></tr>";
        }

        foreach ($visits as $visit) {
            $date    = $visit->visit_date?->format('M d, Y') ?? '—';
            $height  = $visit->height ? $visit->height . ' cm' : '—';
            $weight  = $visit->weight ? $visit->weight . ' kg' : '—';
            $status  = htmlspecialchars($visit->status ?? '—');
            $address = htmlspecialchars($visit->visit_address ?? '—');
            $bs      = $this->badgeStyle($visit->status ?? '');

            $rows .= "
            <tr class='{$uid}_hrow' style='border-bottom:1px solid #f3f4f6;'>
                <td style='padding:12px 16px;font-size:13px;font-weight:600;white-space:nowrap;color:#111827;'>{$date}</td>
                <td style='padding:12px 16px;font-size:13px;color:#6b7280;max-width:180px;
                           overflow:hidden;text-overflow:ellipsis;white-space:nowrap;'>{$address}</td>
                <td style='padding:12px 16px;font-size:13px;color:#374151;'>{$height}</td>
                <td style='padding:12px 16px;font-size:13px;color:#374151;'>{$weight}</td>
                <td style='padding:12px 16px;font-size:13px;'>
                    <span style='padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;
                                 {$bs}white-space:nowrap;display:inline-block;'>{$status}</span>
                </td>
            </tr>";
        }

        return $rows;
    }

    // ─── Item rows ───────────────────────────────────────────────────────────────

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

                $itemRows .= "
                <tr class='{$uid}_irow' style='border-bottom:1px solid #f3f4f6;'>
                    <td style='padding:12px 16px;font-size:13px;font-weight:600;white-space:nowrap;color:#111827;'>{$date}</td>
                    <td style='padding:12px 16px;font-size:13px;color:#374151;'>{$desc}</td>
                    <td style='padding:12px 16px;font-size:13px;color:#374151;text-align:center;'>{$qty}</td>
                    <td style='padding:12px 16px;font-size:13px;color:#374151;text-align:right;'>{$amount}</td>
                </tr>";
            }
        }

        if (! $hasItems) {
            $itemRows = "<tr><td colspan='4' style='padding:40px;text-align:center;color:#9ca3af;font-size:13px;'>
                No items distributed yet.</td></tr>";
        }

        $totalFormatted = '₱' . number_format($grandTotal, 2);
        $footer = $hasItems ? "
        <tfoot>
            <tr style='background:#f9fafb;border-top:2px solid #e5e7eb;'>
                <td colspan='3' style='padding:12px 16px;font-size:13px;font-weight:700;
                                       color:#111827;text-align:right;'>Total Amount</td>
                <td style='padding:12px 16px;font-size:14px;font-weight:800;
                           color:#111827;text-align:right;'>{$totalFormatted}</td>
            </tr>
        </tfoot>" : '';

        return [$itemRows, $footer];
    }

    // ─── Layout ──────────────────────────────────────────────────────────────────

    private function buildLayout(
        string $uid,
        string $historyRows,
        string $itemRows,
        string $itemsFooter
    ): string {
        return "
        <div style='border-radius:12px;border:1px solid #e5e7eb;overflow:hidden;'>

            <div style='display:flex;gap:0;border-bottom:1px solid #e5e7eb;background:#f9fafb;padding:0 20px;'>
                <button id='{$uid}_btn_history'
                    style='padding:12px 18px;font-size:13px;font-weight:600;border:none;background:transparent;
                           cursor:pointer;color:#f97316;border-bottom:2px solid #f97316;margin-bottom:-1px;'>
                    🗓️ Visit History
                </button>
                <button id='{$uid}_btn_items'
                    style='padding:12px 18px;font-size:13px;font-weight:500;border:none;background:transparent;
                           cursor:pointer;color:#6b7280;border-bottom:2px solid transparent;margin-bottom:-1px;'>
                    📦 Visit Items
                </button>
            </div>

            <div id='{$uid}_history' style='display:block;overflow-x:auto;'>
                <table style='width:100%;border-collapse:collapse;'>
                    <thead>
                        <tr style='background:#f9fafb;'>
                            <th style='padding:10px 16px;text-align:left;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;white-space:nowrap;'>Visit Date</th>
                            <th style='padding:10px 16px;text-align:left;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;'>Address</th>
                            <th style='padding:10px 16px;text-align:left;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;'>Height</th>
                            <th style='padding:10px 16px;text-align:left;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;'>Weight</th>
                            <th style='padding:10px 16px;text-align:left;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;'>Nutritional Status</th>
                        </tr>
                    </thead>
                    <tbody id='{$uid}_hbody'>{$historyRows}</tbody>
                </table>
                <div id='{$uid}_hpager' style='display:flex;align-items:center;justify-content:space-between;
                     padding:10px 16px;border-top:1px solid #f3f4f6;background:#f9fafb;'></div>
            </div>

            <div id='{$uid}_items' style='display:none;overflow-x:auto;'>
                <table style='width:100%;border-collapse:collapse;'>
                    <thead>
                        <tr style='background:#f9fafb;'>
                            <th style='padding:10px 16px;text-align:left;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;white-space:nowrap;'>Visit Date</th>
                            <th style='padding:10px 16px;text-align:left;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;'>Item Description</th>
                            <th style='padding:10px 16px;text-align:center;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;'>Quantity</th>
                            <th style='padding:10px 16px;text-align:right;font-size:11px;font-weight:600;color:#6b7280;text-transform:uppercase;letter-spacing:.06em;'>Amount</th>
                        </tr>
                    </thead>
                    <tbody id='{$uid}_ibody'>{$itemRows}</tbody>
                    {$itemsFooter}
                </table>
                <div id='{$uid}_ipager' style='display:flex;align-items:center;justify-content:space-between;
                     padding:10px 16px;border-top:1px solid #f3f4f6;background:#f9fafb;'></div>
            </div>
        </div>

        " . $this->buildScript($uid);
    }

    private function buildScript(string $uid): string
    {
        return "
        <script>
        (function () {
            var PER_PAGE  = 6;
            var uid       = '{$uid}';
            var btnH      = document.getElementById(uid + '_btn_history');
            var btnI      = document.getElementById(uid + '_btn_items');
            var tabH      = document.getElementById(uid + '_history');
            var tabI      = document.getElementById(uid + '_items');
            var activeCSS   = 'padding:12px 18px;font-size:13px;font-weight:600;border:none;background:transparent;cursor:pointer;color:#f97316;border-bottom:2px solid #f97316;margin-bottom:-1px;';
            var inactiveCSS = 'padding:12px 18px;font-size:13px;font-weight:500;border:none;background:transparent;cursor:pointer;color:#6b7280;border-bottom:2px solid transparent;margin-bottom:-1px;';

            btnH.addEventListener('click', function () {
                tabH.style.display = 'block'; tabI.style.display = 'none';
                btnH.style.cssText = activeCSS; btnI.style.cssText = inactiveCSS;
            });
            btnI.addEventListener('click', function () {
                tabH.style.display = 'none'; tabI.style.display = 'block';
                btnI.style.cssText = activeCSS; btnH.style.cssText = inactiveCSS;
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
                    prev.textContent  = '\u2039 Prev';
                    prev.disabled     = (page === 1);
                    prev.style.cssText = 'padding:5px 12px;font-size:12px;border-radius:6px;border:1px solid #e5e7eb;background:#fff;cursor:' +
                        (page === 1 ? 'default;color:#d1d5db;' : 'pointer;color:#374151;');
                    prev.addEventListener('click', function () { if (page > 1) { page--; render(); } });

                    var next = document.createElement('button');
                    next.textContent  = 'Next \u203a';
                    next.disabled     = (page === pages);
                    next.style.cssText = 'padding:5px 12px;font-size:12px;border-radius:6px;border:1px solid #e5e7eb;background:#fff;cursor:' +
                        (page === pages ? 'default;color:#d1d5db;' : 'pointer;color:#374151;');
                    next.addEventListener('click', function () { if (page < pages) { page++; render(); } });

                    var info = document.createElement('span');
                    info.style.cssText = 'font-size:12px;color:#6b7280;';
                    info.textContent   = 'Showing ' + from + '\u2013' + to + ' of ' + total;

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
