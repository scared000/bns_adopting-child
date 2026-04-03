<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Recent Visits</h3>
                <p class="text-xs text-gray-400 mt-0.5">
                    Latest recorded office visits across all BNS
                    &mdash; {{ $this->selectedYear }}
                </p>
            </div>
            <a href="{{ url('/admin/office-visits') }}"
               class="text-xs font-medium text-orange-500 hover:text-orange-600 hover:underline transition-colors">
                View all →
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                <tr class="text-xs font-semibold text-gray-400 uppercase tracking-wider border-b border-gray-100 dark:border-gray-800">
                    <th class="pb-3 text-left">Child</th>
                    <th class="pb-3 text-left">BNS</th>
                    <th class="pb-3 text-left">Office</th>
                    <th class="pb-3 text-left">Visit Date</th>
                    <th class="pb-3 text-left">Status</th>
                    <th class="pb-3 text-left">Items Given</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse ($this->recentVisits as $visit)
                    @php
                        $s = strtolower($visit->status ?? '');
                        $badge = match(true) {
                            str_contains($s, 'severely') || str_contains($s, 'wasted') || str_contains($s, 'obese') => 'bg-red-100 text-red-700',
                            str_contains($s, 'overweight') || str_contains($s, 'underweight') || str_contains($s, 'stunted') => 'bg-yellow-100 text-yellow-700',
                            str_contains($s, 'normal') => 'bg-green-100 text-green-700',
                            default => 'bg-gray-100 text-gray-600',
                        };
                        $colors = ['bg-orange-500','bg-blue-500','bg-green-500','bg-purple-500','bg-rose-500','bg-teal-500'];
                        $color  = $colors[($visit->child?->id ?? 0) % count($colors)];
                        $initials = strtoupper(
                            substr($visit->child?->firstname ?? '?', 0, 1) .
                            substr($visit->child?->lastname ?? '', 0, 1)
                        );
                        $isToday     = $visit->visit_date?->isToday();
                        $isYesterday = $visit->visit_date?->isYesterday();
                        $dateLabel   = $isToday ? 'Today' : ($isYesterday ? 'Yesterday' : $visit->visit_date?->format('M d, Y'));
                    @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors">
                        <td class="py-3 pr-4">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg {{ $color }} flex items-center justify-center text-white text-xs font-bold flex-shrink-0 overflow-hidden">
                                    @if ($visit->child?->profile_path)
                                        <img src="{{ \Illuminate\Support\Facades\Storage::url($visit->child->profile_path) }}"
                                             class="w-full h-full object-cover" />
                                    @else
                                        {{ $initials }}
                                    @endif
                                </div>
                                <span class="font-semibold text-gray-800 dark:text-white">
                                        {{ $visit->child?->firstname }} {{ $visit->child?->lastname }}
                                    </span>
                            </div>
                        </td>
                        <td class="py-3 pr-4 text-gray-500 dark:text-gray-400">
                            {{ $visit->bns?->firstname }} {{ $visit->bns?->lastname }}
                        </td>
                        <td class="py-3 pr-4 text-gray-500 dark:text-gray-400">
                            {{ $visit->office?->short_name ?? $visit->office?->office ?? '—' }}
                        </td>
                        <td class="py-3 pr-4">
                            <span class="text-gray-600 dark:text-gray-300 font-medium">{{ $dateLabel }}</span>
                        </td>
                        <td class="py-3 pr-4">
                                <span class="text-xs px-2 py-1 rounded-full font-medium {{ $badge }}">
                                    {{ \Illuminate\Support\Str::limit($visit->status ?? '—', 25) }}
                                </span>
                        </td>
                        <td class="py-3 text-xs text-gray-400">
                            @if ($visit->visitItems->count())
                                {{ $visit->visitItems->pluck('Item_description')->take(2)->implode(', ') }}
                                @if ($visit->visitItems->count() > 2)
                                    <span class="text-gray-300">+{{ $visit->visitItems->count() - 2 }} more</span>
                                @endif
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center">
                            <x-heroicon-o-clipboard-document-check class="w-10 h-10 mx-auto text-gray-300 mb-2" />
                            <p class="text-sm text-gray-400">No visits recorded for {{ $this->selectedYear }}.</p>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
