<x-filament-panels::page>
    @php
        $child      = $this->child;
        $visits     = $child->officeVisits->sortByDesc('visit_date');
        $totalVisits= $visits->count();
        $bns        = $child->officeAssignments->first()?->bns;
        $initials   = strtoupper(substr($child->firstname, 0, 1) . substr($child->lastname, 0, 1));
    @endphp

    {{-- HERO PROFILE SECTION --}}
    <div class="space-y-6">
        <div class="relative rounded-2xl overflow-hidden shadow-sm">
            <div class="bg-white dark:bg-gray-900 px-8 pb-7 border-t-4 border-orange-500">
                <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mt-4">

                    {{-- Avatar --}}
                    <div class="flex items-end gap-5">
                        <div class="relative">
                            @if($child->profile_path)
                                <img
                                    src="{{ Storage::url($child->profile_path) }}"
                                    alt="{{ $child->firstname }}"
                                    class="w-24 h-24 rounded-2xl object-cover ring-4 ring-white dark:ring-gray-900 shadow-lg"
                                />
                            @else
                                <img
                                    src="https://ui-avatars.com/api/?{{ http_build_query([
                                        'name'       => $child->firstname . ' ' . $child->lastname,
                                        'background' => $child->sex === 'male' ? '3b82f6' : 'ec4899',
                                        'color'      => 'fff',
                                        'size'       => '128',
                                        'bold'       => 'true',
                                    ]) }}"
                                    alt="{{ $child->firstname }}"
                                    class="w-24 h-24 rounded-2xl ring-4 ring-white dark:ring-gray-900 shadow-lg"
                                />
                            @endif
                        </div>

                        <div class="pb-1">
                            <h1 class="text-xl font-bold text-gray-900 dark:text-white tracking-tight">
                                {{ $child->firstname }} {{ $child->lastname }}
                            </h1>
                            <p class="text-sm text-gray-500 dark:text-gray-400 flex items-center gap-1.5 mt-0.5">
                                <x-heroicon-m-map-pin class="w-3.5 h-3.5 text-orange-500" />
                                {{ $child->purok }},
                                {{ $child->barangay?->brgyDesc ?? 'No barangay' }},
                                {{ $child->municipality?->citymunDesc ?? 'No municipality' }},
                                {{ $child->municipality?->province?->provDesc ?? 'No province' }}
                            </p>
                        </div>
                    </div>

                    {{-- Back Button --}}
                    <div class="flex items-center gap-2 pb-1">
                        <a href="{{ route('filament.admin.pages.child-visit-log') }}"
                           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium
                                  border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400
                                  hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                            <x-heroicon-m-arrow-left class="w-4 h-4" />
                            Back
                        </a>
                    </div>
                </div>

                {{-- Stats Row --}}
                <div class="grid grid-cols-2 gap-4 mt-6 pt-6 border-t border-gray-100 dark:border-gray-800">
                    <div class="text-center">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $totalVisits }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 uppercase tracking-wide font-medium">
                            Total Visits
                        </p>
                    </div>
                    <div class="text-center border-l border-gray-100 dark:border-gray-800">
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ $bns ? $bns->firstname . ' ' . $bns->lastname : '—' }}
                        </p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 uppercase tracking-wide font-medium">
                            Child Assigned BNS
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="flex gap-1 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-xl p-1 w-fit shadow-sm">
            <button wire:click="setTab('history')"
                    class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all
                       {{ $this->activeTab === 'history'
                           ? 'bg-orange-500 text-white shadow'
                           : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300' }}">
                Visit History
            </button>
            <button wire:click="setTab('items')"
                    class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all
                       {{ $this->activeTab === 'items'
                           ? 'bg-orange-500 text-white shadow'
                           : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300' }}">
                Visit Items
            </button>
        </div>

        {{-- Visit History Tab --}}
        @if ($this->activeTab === 'history')
            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">

                {{-- Table Header --}}
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-800 flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center">
                        <x-heroicon-o-calendar-days class="w-5 h-5 text-orange-600 dark:text-orange-400" />
                    </div>
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Visit History</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">All recorded visits for this child</p>
                    </div>
                </div>

                <table class="w-full">
                    <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800/50">
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Visit Date</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Address</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Height</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Weight</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">BNS</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($visits as $visit)
                        @php
                            $s = strtolower($visit->status ?? '');
                            $badge = match(true) {
                                str_contains($s,'severely') || str_contains($s,'wasted') => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                str_contains($s,'underweight') || str_contains($s,'stunted') => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
                                str_contains($s,'normal') => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                default => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
                            };
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors">
                            <td class="px-6 py-4 text-sm font-semibold text-gray-900 dark:text-white whitespace-nowrap">
                                {{ $visit->visit_date?->format('M d, Y') ?? '—' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400 max-w-xs truncate">
                                {{ $visit->visit_address ?? '—' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                {{ $visit->height ? $visit->height . ' cm' : '—' }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                {{ $visit->weight ? $visit->weight . ' kg' : '—' }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-xs font-medium {{ $badge }}">
                                    {{ $visit->status ?? '—' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                {{ $visit->bns ? $visit->bns->firstname . ' ' . $visit->bns->lastname : '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="flex flex-col items-center justify-center py-16 text-center">
                                    <div class="w-16 h-16 rounded-2xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center mb-4">
                                        <x-heroicon-o-calendar-days class="w-8 h-8 text-gray-400" />
                                    </div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">No visits recorded yet</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Visits will appear here once recorded</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        @endif

        {{-- Visit Items Tab --}}
        @if ($this->activeTab === 'items')
            <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">

                {{-- Table Header --}}
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-800 flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center">
                        <x-heroicon-o-archive-box class="w-5 h-5 text-orange-600 dark:text-orange-400" />
                    </div>
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Visit Items Log</h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Items distributed to this child across all visits</p>
                    </div>
                </div>

                <table class="w-full">
                    <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800/50">
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Visit Date</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Item Description</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Quantity</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Amount</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse ($visits as $visit)
                        @foreach ($visit->visitItems as $item)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors">
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900 dark:text-white whitespace-nowrap">
                                    {{ $visit->visit_date?->format('M d, Y') ?? '—' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                    {{ $item->Item_description ?? '—' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-center text-gray-600 dark:text-gray-400">
                                    {{ $item->item_quantity ?? '—' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-right text-gray-600 dark:text-gray-400">
                                    {{ $item->item_amount ? '₱' . number_format($item->item_amount, 2) : 'Free' }}
                                </td>
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="flex flex-col items-center justify-center py-16 text-center">
                                    <div class="w-16 h-16 rounded-2xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center mb-4">
                                        <x-heroicon-o-archive-box class="w-8 h-8 text-gray-400" />
                                    </div>
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">No items recorded yet</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Distributed items will appear here</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        @endif

    </div>
</x-filament-panels::page>
