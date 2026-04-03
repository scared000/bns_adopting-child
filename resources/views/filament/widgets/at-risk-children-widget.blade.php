<x-filament-widgets::widget>
    <x-filament::section class="h-full min-h-[300px]">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-red-500 animate-pulse"></span>
                    At-Risk Children
                </h3>
                <p class="text-xs text-gray-400 mt-0.5">
                    Severely UW · Wasted · Obese · Stunted
                    &mdash; {{ $this->selectedYear }}
                </p>
            </div>
            <a href="{{ url('/admin/child-visit-log') }}"
               class="text-xs font-medium text-orange-500 hover:text-orange-600 hover:underline">
                View all →
            </a>
        </div>

        @forelse ($this->atRisk as $visit)
            @php
                $s = strtolower($visit->status ?? '');
                $badge = match(true) {
                    str_contains($s, 'severely') || str_contains($s, 'wasted') => 'bg-red-100 text-red-700',
                    str_contains($s, 'obese') => 'bg-red-100 text-red-800',
                    str_contains($s, 'stunted') => 'bg-purple-100 text-purple-700',
                    default => 'bg-yellow-100 text-yellow-700',
                };
                $colors = ['bg-orange-500','bg-blue-500','bg-rose-500','bg-purple-500','bg-teal-500'];
                $color  = $colors[($visit->child?->id ?? 0) % count($colors)];
                $initials = strtoupper(
                    substr($visit->child?->firstname ?? '?', 0, 1) .
                    substr($visit->child?->lastname ?? '', 0, 1)
                );
            @endphp
            <div class="flex items-center gap-3 py-2.5 border-b border-gray-100 dark:border-gray-800 last:border-0">
                <div class="w-9 h-9 rounded-xl {{ $color }} flex items-center justify-center text-white text-xs font-bold flex-shrink-0 overflow-hidden">
                    @if ($visit->child?->profile_path)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($visit->child->profile_path) }}"
                             class="w-full h-full object-cover" />
                    @else
                        {{ $initials }}
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-800 dark:text-white truncate">
                        {{ $visit->child?->firstname }} {{ $visit->child?->lastname }}
                    </p>
                    <p class="text-xs text-gray-400 truncate">
                        BNS: {{ $visit->bns?->firstname }} {{ $visit->bns?->lastname }}
                    </p>
                </div>
                <span class="text-xs px-2 py-1 rounded-full font-medium {{ $badge }} flex-shrink-0">
                    {{ \Illuminate\Support\Str::limit($visit->status ?? '—', 22) }}
                </span>
            </div>
        @empty
            <div class="text-center py-10">
                <x-heroicon-o-check-badge class="w-10 h-10 mx-auto text-green-400 mb-2" />
                <p class="text-sm font-medium text-gray-700 dark:text-white">No at-risk children</p>
                <p class="text-xs text-gray-400 mt-1">No at-risk records found for {{ $this->selectedYear }}</p>
            </div>
        @endforelse
    </x-filament::section>
</x-filament-widgets::widget>
