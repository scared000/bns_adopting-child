<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Recent Visits</h3>
                <p class="text-xs text-gray-400">Last 5 home visits</p>
            </div>
            <a href="{{ url('/admin/office-visits') }}"
               class="text-xs font-medium text-orange-500 hover:text-orange-600 hover:underline transition-colors">
                View all →
            </a>
        </div>

        <div class="space-y-3">
            @forelse ($this->recentVisits as $visit)
                @php
                    $initials = strtoupper(
                        substr($visit->child?->firstname ?? '?', 0, 1) .
                        substr($visit->child?->lastname ?? '', 0, 1)
                    );
                    $colors = ['bg-orange-500','bg-blue-500','bg-green-500','bg-purple-500','bg-rose-500'];
                    $color  = $colors[($visit->child?->id ?? 0) % count($colors)];
                    $isToday     = $visit->visit_date?->isToday();
                    $isYesterday = $visit->visit_date?->isYesterday();
                    $dateLabel   = $isToday ? 'Today'
                        : ($isYesterday ? 'Yesterday'
                        : $visit->visit_date?->format('M d'));
                @endphp

                <div class="flex items-center gap-3 py-1">
                    {{-- Avatar --}}
                    <div class="w-9 h-9 rounded-xl {{ $color }} flex items-center justify-center text-white text-xs font-bold flex-shrink-0 overflow-hidden">
                        @if ($visit->child?->profile_path)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($visit->child->profile_path) }}"
                                 class="w-full h-full object-cover" alt="{{ $visit->child->firstname }}" />
                        @else
                            {{ $initials }}
                        @endif
                    </div>

                    {{-- Info --}}
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-800 dark:text-white truncate">
                            {{ $visit->child?->firstname }} {{ $visit->child?->lastname }}
                        </p>
                        <p class="text-xs text-gray-400 truncate">
                            {{ $visit->bns?->barangay?->brgyDesc ?? '—' }}
                            @if ($visit->visitItems->count())
                                · {{ $visit->visitItems->pluck('Item_description')->take(2)->implode(', ') }}
                            @endif
                        </p>
                    </div>

                    {{-- Date --}}
                    <span class="text-xs text-gray-400 flex-shrink-0">{{ $dateLabel }}</span>
                </div>
            @empty
                <p class="text-sm text-gray-400 text-center py-6">No recent visits.</p>
            @endforelse
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
