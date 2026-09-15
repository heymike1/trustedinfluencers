@props(['notice'])
@if($notice)
    <div class="mb-4 rounded-md border px-3.5 py-2.5 text-sm {{ match($notice['type']) { 'success' => 'border-verified-100 bg-verified-50 text-verified-600', 'error' => 'border-danger-100 bg-red-50 text-danger-700', default => 'border-ink-200 bg-ink-50 text-ink-700' } }}">
        {{ $notice['message'] }}
    </div>
@endif
