<div
    x-data="{}"
    x-on:open-print-tab.window="window.open($event.detail.url, '_blank')"
></div>
@push('scripts')
    <script>
        window.addEventListener('open-print-tab', function (event) {
            const url = event.detail?.url ?? event.detail?.[0]?.url;
            if (url) window.open(url, '_blank');
        });
    </script>
@endpush
