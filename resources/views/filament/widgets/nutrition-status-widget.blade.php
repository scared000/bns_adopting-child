@php $data = $this->nutritionData; @endphp

<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Nutrition Status</h3>
                <p class="text-xs text-gray-400">{{ now()->format('F Y') }} snapshot</p>
            </div>
            <span class="text-xs text-gray-400">{{ $data['total'] }} total</span>
        </div>

        <div class="space-y-3">
            @foreach ($data['items'] as $item)
                <div class="flex items-center gap-3">
                    <div class="w-2.5 h-2.5 rounded-full {{ $item['dot'] }} flex-shrink-0"></div>
                    <span class="text-sm text-gray-600 dark:text-gray-300 w-28 flex-shrink-0">{{ $item['label'] }}</span>
                    <div class="flex-1 bg-gray-100 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
                        <div class="h-2 rounded-full transition-all duration-500"
                             style="width: {{ $item['width'] }}%; background-color: {{ $item['color'] }};"></div>
                    </div>
                    <span class="text-sm font-semibold text-gray-800 dark:text-white w-8 text-right">{{ $item['count'] }}</span>
                </div>
            @endforeach

            @if (empty($data['items']))
                <p class="text-sm text-gray-400 text-center py-4">No data available.</p>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
