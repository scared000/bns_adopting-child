<x-filament-panels::page>
    <div class="max-w-4xl mx-auto">
        <div class="rounded-2xl overflow-hidden mb-6"
             style="background: linear-gradient(135deg, #f97316 0%, #ea580c 50%, #c2410c 100%);">
            <div class="px-8 py-7 flex items-center gap-5">
                <div class="flex-shrink-0 w-14 h-14 rounded-xl bg-white/20 backdrop-blur flex items-center justify-center shadow-inner">
                    <x-heroicon-o-user-plus class="w-7 h-7 text-white" />
                </div>
                <div>
                    <p class="text-orange-100 text-sm font-medium tracking-widest uppercase mb-0.5">
                        Child Assignment Management
                    </p>
                    <h1 class="text-white text-2xl font-bold tracking-tight leading-tight">
                        Assign Child to BNS
                    </h1>
                    <p class="text-orange-200 text-sm mt-1">
                        Link an adopted child to a Barangay Nutrition Scholar
                    </p>
                </div>
            </div>

            <div class="bg-black/10 px-8 py-2.5 flex items-center gap-2 text-xs text-orange-100">
                <x-heroicon-m-home class="w-3.5 h-3.5" />
                <span>Dashboard</span>
                <x-heroicon-m-chevron-right class="w-3 h-3 opacity-60" />
                <span>Child Assignments</span>
                <x-heroicon-m-chevron-right class="w-3 h-3 opacity-60" />
                <span class="text-white font-medium">New Assignment</span>
            </div>
        </div>

        <div class="flex items-start gap-3 bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800 rounded-xl px-5 py-4 mb-6">
            <x-heroicon-o-information-circle class="w-5 h-5 text-blue-500 mt-0.5 flex-shrink-0" />
            <p class="text-sm text-blue-700 dark:text-blue-300 leading-relaxed">
                Each child must be assigned to a <strong>Barangay Nutrition Scholar (BNS)</strong>
                within their barangay. The BNS will be responsible for monitoring the child's
                nutritional status and scheduling visits.
            </p>
        </div>
        {{-- Stats --}}
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 px-5 py-4 flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center flex-shrink-0">
                    <x-heroicon-o-users class="w-5 h-5 text-orange-600 dark:text-orange-400" />
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide font-medium">Total BNS</p>
                    <p class="text-xl font-bold text-gray-900 dark:text-white">
                        {{ \App\Models\BaranggayNutritionScholars::count() }}
                    </p>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 px-5 py-4 flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center flex-shrink-0">
                    <x-heroicon-o-face-smile class="w-5 h-5 text-green-600 dark:text-green-400" />
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide font-medium">Total Children</p>
                    <p class="text-xl font-bold text-gray-900 dark:text-white">
                        {{ \App\Models\AdoptedChild::count() }}
                    </p>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700 px-5 py-4 flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center flex-shrink-0">
                    <x-heroicon-o-link class="w-5 h-5 text-blue-600 dark:text-blue-400" />
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide font-medium">Assigned</p>
                    <p class="text-xl font-bold text-gray-900 dark:text-white">
                        {{ \App\Models\OfficeChildAssign::count() }}
                    </p>
                </div>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">

            {{-- Form Section Header --}}
            <div class="px-8 pt-7 pb-4 border-b border-gray-100 dark:border-gray-800">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center">
                        <x-heroicon-m-clipboard-document-list class="w-4 h-4 text-orange-600 dark:text-orange-400" />
                    </div>
                    <div>
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">
                            Assignment Details
                        </h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            All fields marked with <span class="text-red-500">*</span> are required
                        </p>
                    </div>
                </div>
            </div>

            <div class="px-8 py-7">
                <div class="px-8 py-7">
                    <form wire:submit="create">
                        @csrf
                        {{ $this->form }}

                        <div class="flex items-center justify-between pt-6 mt-6 border-t border-gray-100 dark:border-gray-800">
                            <a href="{{ url()->previous() }}"
                               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-medium
                                       text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-800
                                       hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors duration-150">
                                <x-heroicon-m-arrow-left class="w-4 h-4" />
                                Back to List
                            </a>

                            <x-filament::button type="submit" color="warning" icon="heroicon-m-check">
                                Save Assignment
                            </x-filament::button>
                        </div>
                    </form>

                    <x-filament-actions::modals />
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
