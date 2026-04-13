<x-filament-panels::page>
{{--   Davao de Oro Gold Theme — CSS Variables--}}

    <style>
        :root {
            --gold-50:  #fffbeb;
            --gold-100: #fef3c7;
            --gold-200: #fde68a;
            --gold-300: #fbbf24;
            --gold-400: #f59e0b;
            --gold-500: #d97706;
            --gold-600: #b45309;
            --gold-700: #92400e;
            --gold-800: #78350f;
            --gold-900: #451a03;

            --surface:        #ffffff;
            --surface-2:      #fffbf2;
            --border:         #fde68a;
            --border-strong:  #f59e0b;
            --text-primary:   #1c0a00;
            --text-secondary: #78350f;
            --text-muted:     #a16207;
        }

        .dark {
            --surface:        #1c1008;
            --surface-2:      #251508;
            --border:         #78350f;
            --border-strong:  #b45309;
            --text-primary:   #fef3c7;
            --text-secondary: #fbbf24;
            --text-muted:     #d97706;
        }

        /* ── Hero banner ── */
        .ddo-hero-banner {
            background: linear-gradient(135deg, var(--gold-300) 0%, var(--gold-500) 45%, var(--gold-700) 100%);
            border-radius: 1rem;
            overflow: hidden;
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 24px 0 rgba(180, 83, 9, 0.18);
        }

        .ddo-hero-icon-wrap {
            width: 3.5rem;
            height: 3.5rem;
            border-radius: 0.75rem;
            background: rgba(255, 255, 255, 0.22);
            backdrop-filter: blur(6px);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: inset 0 1px 3px rgba(0,0,0,0.12);
            flex-shrink: 0;
        }

        .ddo-breadcrumb-bar {
            background: rgba(0, 0, 0, 0.12);
            padding: 0.625rem 2rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.75rem;
            color: var(--gold-100);
        }

        /* ── Info alert ── */
        .ddo-info-alert {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            background: var(--gold-50);
            border: 1px solid var(--gold-200);
            border-radius: 0.875rem;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
        }

        .dark .ddo-info-alert {
            background: rgba(120, 53, 15, 0.18);
            border-color: var(--border);
        }

        .ddo-info-alert p {
            font-size: 0.875rem;
            line-height: 1.6;
            color: var(--gold-700);
        }

        .dark .ddo-info-alert p {
            color: var(--gold-200);
        }

        /* ── Stat cards ── */
        .ddo-stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .ddo-stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 1rem;
            padding: 1.125rem 1.25rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: box-shadow 0.2s, border-color 0.2s;
        }

        .ddo-stat-card:hover {
            border-color: var(--border-strong);
            box-shadow: 0 4px 16px rgba(180, 83, 9, 0.10);
        }

        .ddo-stat-icon {
            width: 2.75rem;
            height: 2.75rem;
            border-radius: 0.625rem;
            background: var(--gold-100);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .dark .ddo-stat-icon {
            background: rgba(120, 53, 15, 0.35);
        }

        .ddo-stat-icon svg {
            width: 1.25rem;
            height: 1.25rem;
            color: var(--gold-600);
        }

        .dark .ddo-stat-icon svg {
            color: var(--gold-300);
        }

        .ddo-stat-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 0.125rem;
        }

        .ddo-stat-value {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--text-primary);
            line-height: 1;
        }

        /* ── Form card ── */
        .ddo-form-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 1.25rem;
            box-shadow: 0 2px 12px rgba(180, 83, 9, 0.06);
            overflow: hidden;
        }

        .ddo-form-header {
            padding: 1.75rem 2rem 1rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 0.875rem;
        }

        .ddo-form-header-icon {
            width: 2.25rem;
            height: 2.25rem;
            border-radius: 0.5rem;
            background: var(--gold-100);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .dark .ddo-form-header-icon {
            background: rgba(120, 53, 15, 0.35);
        }

        .ddo-form-header-icon svg {
            width: 1rem;
            height: 1rem;
            color: var(--gold-600);
        }

        .dark .ddo-form-header-icon svg {
            color: var(--gold-300);
        }

        .ddo-form-header-title {
            font-size: 0.9rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .ddo-form-header-sub {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-top: 0.125rem;
        }

        .ddo-form-body {
            padding: 2rem;
        }

        /* ── Form action bar ── */
        .ddo-form-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 1.5rem;
            margin-top: 1.5rem;
            border-top: 1px solid var(--border);
        }

        .ddo-btn-back {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.625rem 1.25rem;
            border-radius: 0.75rem;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-secondary);
            background: var(--gold-50);
            border: 1px solid var(--border);
            transition: background 0.15s, border-color 0.15s;
            text-decoration: none;
        }

        .ddo-btn-back:hover {
            background: var(--gold-100);
            border-color: var(--border-strong);
        }

        .dark .ddo-btn-back {
            background: rgba(120, 53, 15, 0.18);
            color: var(--gold-200);
            border-color: var(--border);
        }

        .dark .ddo-btn-back:hover {
            background: rgba(120, 53, 15, 0.35);
            border-color: var(--border-strong);
        }

        /* ── Accent line under hero ── */
        .ddo-accent-dot {
            display: inline-block;
            width: 0.4rem;
            height: 0.4rem;
            border-radius: 50%;
            background: rgba(255,255,255,0.5);
        }
    </style>

    <div class="max-w-4xl mx-auto">
        <div class="ddo-hero-banner">
            <div class="px-8 py-7 flex items-center gap-5">
                <div class="ddo-hero-icon-wrap">
                    <x-heroicon-o-user-plus class="w-7 h-7 text-white" />
                </div>
                <div>
                    <p style="color: var(--gold-100); font-size: 0.75rem; font-weight: 600;
                           letter-spacing: 0.12em; text-transform: uppercase; margin-bottom: 0.2rem;">
                        Child Assignment Management
                    </p>
                    <h1 style="color: #fff; font-size: 1.5rem; font-weight: 800;
                            letter-spacing: -0.02em; line-height: 1.25;">
                        Assign Child to BNS
                    </h1>
                    <p style="color: var(--gold-200); font-size: 0.875rem; margin-top: 0.25rem;">
                        Link an adopted child to a Barangay Nutrition Scholar
                    </p>
                </div>
            </div>

            <div class="ddo-breadcrumb-bar">
                <x-heroicon-m-home class="w-3.5 h-3.5" style="flex-shrink:0;" />
                <span>Dashboard</span>
                <x-heroicon-m-chevron-right class="w-3 h-3" style="opacity:0.5;" />
                <span>Child Assignments</span>
                <x-heroicon-m-chevron-right class="w-3 h-3" style="opacity:0.5;" />
                <span style="color:#fff; font-weight:600;">New Assignment</span>
            </div>
        </div>

        {{-- ══ Info Alert ══ --}}
        <div class="ddo-info-alert">
            <x-heroicon-o-information-circle class="w-5 h-5 flex-shrink-0" style="color: var(--gold-500); margin-top:0.125rem;" />
            <p>
                Each child must be assigned to a <strong style="color: var(--gold-700);">Barangay Nutrition Scholar (BNS)</strong>
                within their barangay. The BNS will be responsible for monitoring the child's
                nutritional status and scheduling visits.
            </p>
        </div>

        {{-- ══ Stats Row ══ --}}
        <div class="ddo-stats-grid">

            {{-- Total BNS --}}
            <div class="ddo-stat-card">
                <div class="ddo-stat-icon">
                    <x-heroicon-o-users />
                </div>
                <div>
                    <p class="ddo-stat-label">Total BNS</p>
                    <p class="ddo-stat-value">
                        {{ \App\Models\BaranggayNutritionScholars::whereHas('user', fn($q) => $q->role('bns'))->count() }}
                    </p>
                </div>
            </div>

            {{-- Total Children --}}
            <div class="ddo-stat-card">
                <div class="ddo-stat-icon">
                    <x-heroicon-o-face-smile />
                </div>
                <div>
                    <p class="ddo-stat-label">Total Children</p>
                    <p class="ddo-stat-value">
                        {{ \App\Models\AdoptedChild::count() }}
                    </p>
                </div>
            </div>

            {{-- Assigned --}}
            <div class="ddo-stat-card">
                <div class="ddo-stat-icon">
                    <x-heroicon-o-link />
                </div>
                <div>
                    <p class="ddo-stat-label">Assigned</p>
                    <p class="ddo-stat-value">
                        {{ \App\Models\OfficeChildAssign::count() }}
                    </p>
                </div>
            </div>

        </div>

        {{-- ══ Form Card ══ --}}
        <div class="ddo-form-card">

            {{-- Card Header --}}
            <div class="ddo-form-header">
                <div class="ddo-form-header-icon">
                    <x-heroicon-m-clipboard-document-list />
                </div>
                <div>
                    <p class="ddo-form-header-title">Assignment Details</p>
                    <p class="ddo-form-header-sub">
                        All fields marked with <span style="color:#ef4444;">*</span> are required
                    </p>
                </div>
            </div>

            {{-- Card Body --}}
            <div class="ddo-form-body">
                <form wire:submit="create">
                    @csrf
                    {{ $this->form }}

                    <div class="ddo-form-actions">
                        <a href="{{ url()->previous() }}" class="ddo-btn-back">
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

</x-filament-panels::page>
