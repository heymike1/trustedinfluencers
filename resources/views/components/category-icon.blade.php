@props(['slug', 'class' => 'size-5'])
<svg {{ $attributes->class([$class]) }} viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($slug)
        @case('finance')<circle cx="10" cy="10" r="7"></circle><path d="M10 6.5v7M8 8.5h3a1.5 1.5 0 0 1 0 3H8"></path>@break
        @case('tech')<rect x="5" y="5" width="10" height="10" rx="2"></rect><path d="M8 2v3M12 2v3M8 15v3M12 15v3M2 8h3M2 12h3M15 8h3M15 12h3"></path>@break
        @case('fitness')<path d="M3 8v4M17 8v4M6 6v8M14 6v8M6 10h8"></path>@break
        @case('food')<path d="M6 2v7a2 2 0 0 0 4 0V2M8 2v16M14 2c-1.5 1-2 3-2 6v2h2v8"></path>@break
        @case('education')<path d="M2 7l8-4 8 4-8 4-8-4zM5 9v4c0 1.5 2.5 3 5 3s5-1.5 5-3V9"></path>@break
        @case('gaming')<rect x="2" y="6" width="16" height="9" rx="4"></rect><path d="M6 9.5v3M4.5 11h3M13 10h.01M15 12h.01"></path>@break
        @case('beauty')<path d="M10 3c2 3 5 5 5 9a5 5 0 0 1-10 0c0-4 3-6 5-9z"></path>@break
        @case('travel')<path d="M18 10l-6-1-4-6H6l2 6-3 1-2-2H2l1 3-1 3h1l2-2 3 1-2 6h2l4-6 6-1z"></path>@break
        @case('comedy')<circle cx="10" cy="10" r="7"></circle><path d="M7 8h.01M13 8h.01M6.5 11.5c1 1.5 2.3 2 3.5 2s2.5-.5 3.5-2"></path>@break
        @case('lifestyle')<path d="M3 10l7-6 7 6M5 9v8h10V9"></path>@break
        @case('business')<rect x="2" y="6" width="16" height="11" rx="2"></rect><path d="M7 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2M2 11h16"></path>@break
        @case('photography')<rect x="2" y="6" width="16" height="11" rx="2"></rect><path d="M7 6l1.5-2h3L13 6"></path><circle cx="10" cy="11.5" r="3"></circle>@break
        @default<circle cx="10" cy="10" r="7"></circle><path d="M10 7v3l2 2"></path>
    @endswitch
</svg>
