@php $data = $this->nutritionData; @endphp

<x-filament-widgets::widget>
    <x-filament::section class="h-full min-h-[300px]">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Nutrition Status Breakdown</h3>
                <p class="text-xs text-gray-400 mt-0.5">Based on each child's latest visit · {{ $data['total'] }} children tracked</p>
            </div>
            <span class="text-xs font-medium px-2.5 py-1 rounded-full bg-orange-50 text-orange-600 dark:bg-orange-900/30 dark:text-orange-400">
                {{ $data['year'] }}
            </span>
        </div>

        <div class="space-y-3">
            @foreach ($data['items'] as $item)
                <div class="flex items-center gap-3">
                    <div class="w-24 flex-shrink-0 flex items-center gap-2">
                        <div class="w-2.5 h-2.5 rounded-full {{ $item['bg'] }} flex-shrink-0"></div>
                        <span class="text-xs text-gray-600 dark:text-gray-400 truncate">{{ $item['label'] }}</span>
                    </div>
                    <div class="flex-1 bg-gray-100 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
                        <div class="h-2 rounded-full transition-all duration-700"
                             style="width: {{ $item['width'] }}%; background-color: {{ $item['color'] }};"></div>
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0 w-16 justify-end">
                        <span class="text-sm font-bold text-gray-800 dark:text-white">{{ $item['count'] }}</span>
                        <span class="text-xs text-gray-400">{{ $item['percent'] }}%</span>
                    </div>
                </div>
            @endforeach

            @if (empty($data['items']) || $data['total'] === 0)
                <div class="text-center py-8">
                    <x-heroicon-o-chart-bar class="w-10 h-10 mx-auto text-gray-300 mb-2" />
                    <p class="text-sm text-gray-400">No visit data available for {{ $data['year'] }}.</p>
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
