@php($messages = collect(['success' => session('success'), 'error' => session('error'), 'info' => session('info')])->filter())
@if($messages->isNotEmpty())
    <div class="mb-6 space-y-2">
        @foreach($messages as $type => $message)
            <div class="rounded-md border px-3.5 py-2.5 text-sm {{ match($type) { 'success' => 'border-verified-100 bg-verified-50 text-verified-600', 'error' => 'border-danger-100 bg-red-50 text-danger-700', default => 'border-ink-200 bg-ink-50 text-ink-700' } }}">
                {{ $message }}
            </div>
        @endforeach
    </div>
@endif
