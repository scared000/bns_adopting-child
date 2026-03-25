<x-filament-panels::page>
    <div class="max-w-5xl mx-auto space-y-6">

        {{-- HERO HEADER --}}
        <div class="relative rounded-2xl overflow-hidden"
             style="background: linear-gradient(135deg, #f97316 0%, #ea580c 60%, #9a3412 100%);">
            <div class="absolute -top-6 -right-6 w-40 h-40 rounded-full bg-white/10"></div>
            <div class="absolute -bottom-8 -left-4 w-32 h-32 rounded-full bg-black/10"></div>
            <div class="absolute top-4 right-32 w-16 h-16 rounded-full bg-white/5"></div>

            <div class="relative px-8 py-7 flex items-center gap-5">
                <div class="flex-shrink-0 w-14 h-14 rounded-2xl bg-white/20 backdrop-blur flex items-center justify-center shadow-inner ring-1 ring-white/30">
                    <x-heroicon-o-clipboard-document-check class="w-7 h-7 text-white" />
                </div>
                <div>
                    <p class="text-orange-100 text-xs font-semibold tracking-widest uppercase mb-0.5">
                        Visit Management
                    </p>
                    <h1 class="text-white text-2xl font-bold tracking-tight">
                        Record New Visit
                    </h1>
                    <p class="text-orange-200 text-sm mt-0.5">
                        Document nutritional monitoring visit details
                    </p>
                </div>
            </div>

            <div class="relative bg-black/10 px-8 py-2.5 flex items-center gap-2 text-xs text-orange-100">
                <x-heroicon-m-home class="w-3.5 h-3.5" />
                <span>Dashboard</span>
                <x-heroicon-m-chevron-right class="w-3 h-3 opacity-60" />
                <span>Office Visits</span>
                <x-heroicon-m-chevron-right class="w-3 h-3 opacity-60" />
                <span class="text-white font-medium">New Visit</span>
            </div>
        </div>

        {{-- FORM CARD --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">

            <div class="px-8 py-5 border-b border-gray-100 dark:border-gray-800 flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center">
                    <x-heroicon-m-pencil-square class="w-5 h-5 text-orange-600 dark:text-orange-400" />
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Visit Details</h2>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                        Fields marked <span class="text-red-500 font-semibold">*</span> are required
                    </p>
                </div>
            </div>

            <form wire:submit="create">
                {{-- Force equal-height sections via CSS --}}
                <div class="px-8 py-6 [&_.fi-fo-component-ctn]:grid [&_.fi-fo-component-ctn]:auto-rows-fr">
                    {{ $this->form }}
                </div>

                <div class="px-8 py-5 bg-gray-50 dark:bg-gray-800/50 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between">
                    <a href="{{ url()->previous() }}"
                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-medium
                               text-gray-600 dark:text-gray-400 bg-white dark:bg-gray-800
                               border border-gray-200 dark:border-gray-700
                               hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-150 shadow-sm">
                        <x-heroicon-m-arrow-left class="w-4 h-4" />
                        Back to List
                    </a>

                    <div class="flex items-center gap-3">
                        <a href="{{ url()->previous() }}"
                           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-medium
                                   text-gray-600 dark:text-gray-400
                                   hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors duration-150">
                            Discard
                        </a>
                        <x-filament::button
                            type="submit"
                            color="warning"
                            icon="heroicon-m-check-circle"
                            size="md"
                        >
                            Save Visit Record
                        </x-filament::button>
                    </div>
                </div>
            </form>
        </div>

        {{-- INFO CALLOUT --}}
        <div class="flex items-start gap-3 bg-blue-50 dark:bg-blue-950/30 border border-blue-200 dark:border-blue-800 rounded-xl px-5 py-4">
            <x-heroicon-o-information-circle class="w-5 h-5 text-blue-500 mt-0.5 flex-shrink-0" />
            <p class="text-sm text-blue-700 dark:text-blue-300 leading-relaxed">
                Visit documentation and measurements will be used to track the child's nutritional progress over time.
                Ensure all measurements are accurate before saving.
            </p>
        </div>

        <x-filament-actions::modals />
    </div>
</x-filament-panels::page>
