<x-filament-panels::page>
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Child Visit History</h2>
            <p class="text-sm text-gray-500">{{ $this->children->count() }} children with recorded visits</p>
        </div>
        <input
            type="text"
            wire:model.live="search"
            placeholder="Search children..."
            class="rounded-lg border border-gray-300 px-4 py-2 text-sm shadow-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:bg-gray-800 dark:border-gray-600 dark:text-white w-64"
        />
    </div>

    {{-- Card Grid --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @forelse ($this->children as $child)
            @php
                $totalVisits   = $child->officeVisits->count();
                $lastVisit     = $child->officeVisits->sortByDesc('visit_date')->first();
                $bns           = $child->officeAssignments->first()?->bns;
                $initials      = strtoupper(substr($child->firstname, 0, 1) . substr($child->lastname, 0, 1));
                $colors        = ['bg-orange-500','bg-blue-500','bg-green-500','bg-purple-500','bg-rose-500','bg-teal-500'];
                $color         = $colors[$child->id % count($colors)];
                $lastStatus    = $lastVisit?->status ?? 'No visits yet';
                $statusColors  = match(true) {
                    str_contains(strtolower($lastStatus), 'severely') ||
                    str_contains(strtolower($lastStatus), 'wasted')   => 'bg-red-100 text-red-700',
                    str_contains(strtolower($lastStatus), 'underweight') ||
                    str_contains(strtolower($lastStatus), 'stunted')  => 'bg-yellow-100 text-yellow-700',
                    str_contains(strtolower($lastStatus), 'normal')   => 'bg-green-100 text-green-700',
                    default => 'bg-gray-100 text-gray-600',
                };
            @endphp

            <a href="{{ route('filament.admin.pages.child-visit-detail', ['childId' => $child->id]) }}"
               class="block rounded-xl border bg-white dark:bg-gray-900 p-5 no-underline group transition-all duration-200 hover:-translate-y-1 hover:shadow-xl hover:border-orange-400"
               style="border-color: #e5e7eb; box-shadow: 0 1px 3px rgba(0,0,0,0.08);">

                {{-- Avatar + Name --}}
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 rounded-full {{ $color }} flex items-center justify-center text-white font-bold text-lg flex-shrink-0 overflow-hidden">
                        @if ($child->profile_path)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($child->profile_path) }}"
                                 alt="{{ $child->firstname }}"
                                 class="w-full h-full object-cover rounded-full" />
                        @else
                            {{ $initials }}
                        @endif
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-white text-sm leading-tight">
                            {{ $child->firstname }} {{ $child->lastname }}
                        </h3>
                        <span class="text-xs text-gray-400">ID #{{ $child->id }}</span>
                    </div>
                </div>

                {{-- Stats Row --}}
                <div class="flex items-center justify-between mb-3">
                    <div class="text-center">
                        <p class="text-xl font-bold text-gray-900 dark:text-white">{{ $totalVisits }}</p>
                        <p class="text-xs text-gray-400 uppercase tracking-wide">Visits</p>
                    </div>
                    <div class="text-center">
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            {{ $lastVisit?->visit_date?->format('M d, Y') ?? '—' }}
                        </p>
                        <p class="text-xs text-gray-400 uppercase tracking-wide">Last Visit</p>
                    </div>
                </div>

                {{-- BNS --}}
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-3 truncate">
                    <span class="font-medium">BNS:</span>
                    {{ $bns ? $bns->firstname . ' ' . $bns->lastname : 'Not assigned' }}
                </p>

                {{-- Status Badge + Link --}}
                <div class="flex items-center justify-between">
                    <span class="text-xs px-2 py-1 rounded-full font-medium {{ $statusColors }}">
                        {{ Str::limit($lastStatus, 20) }}
                    </span>
                    <span class="text-xs text-orange-500 font-semibold group-hover:underline">View →</span>
                </div>
            </a>
        @empty
            <div class="col-span-4 text-center py-16 text-gray-400">
                <x-heroicon-o-user-group class="w-12 h-12 mx-auto mb-3 opacity-40" />
                <p class="text-sm">No children found.</p>
            </div>
        @endforelse
    </div>
</x-filament-panels::page>
