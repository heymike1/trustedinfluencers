@props(['for'])
@error($for)
    <p {{ $attributes->class(['mt-1 text-xs text-danger-700']) }}>{{ $message }}</p>
@enderror
