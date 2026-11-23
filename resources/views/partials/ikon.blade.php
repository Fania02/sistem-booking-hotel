@php
    $path = match ($nama) {
        'pin' => '<path d="M12 21s7-6.2 7-11a7 7 0 1 0-14 0c0 4.8 7 11 7 11z"/><circle cx="12" cy="10" r="2.5"/>',
        'phone' => '<path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z"/>',
        'wa' => '<path d="M3 21l1.6-4.7A8.5 8.5 0 1 1 8 19.5L3 21z"/><path d="M9 9c0 3 3 6 6 6l1-1.5-2-1-1 .5c-.8-.4-1.6-1.2-2-2l.5-1-1-2L9 9z"/>',
        'mail' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'instagram' => '<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor"/>',
        'facebook' => '<path d="M14 8h2.5V4.5H14A4 4 0 0 0 10 8.5V11H7.5v3.5H10V21h3.5v-6.5h2.5l.5-3.5h-3V8.8c0-.5.2-.8.5-.8z"/>',
        'tiktok' => '<path d="M14 3v11.5a3.5 3.5 0 1 1-3.5-3.5"/><path d="M14 3c.4 2.6 2 4.2 5 4.5"/>',
        'wifi' => '<path d="M2 9a15 15 0 0 1 20 0M5 12.5a10 10 0 0 1 14 0M8.5 16a5 5 0 0 1 7 0"/><circle cx="12" cy="19.5" r="1" fill="currentColor"/>',
        'parkir' => '<rect x="4" y="3" width="16" height="18" rx="3"/><path d="M10 17V8h3a3 3 0 0 1 0 6h-3"/>',
        'spa' => '<path d="M12 21c-4-2-7-5-7-9 3 0 5.500 1 7 3 1.500-2 4-3 7-3 0 4-3 7-7 9z"/><path d="M12 15c-2-1-3-3-3-6 2 1 3 3 3 6zM12 15c2-1 3-3 3-6-2 1-3 3-3 6z"/>',
        'kolam' => '<path d="M3 17c2 0 2-1.500 4.500-1.500S10 17 12 17s2.500-1.500 4.500-1.500S19 17 21 17M3 21c2 0 2-1.500 4.500-1.500S10 21 12 21s2.500-1.500 4.500-1.500S19 21 21 21"/><path d="M8 13V5a2 2 0 0 1 4 0M16 13V5a2 2 0 0 0-4 0M8 9h8"/>',
        'resto' => '<path d="M6 3v7a2 2 0 0 0 4 0V3M8 3v18M17 3c-2 2-3 5-3 8h3v10"/>',
        'rapat' => '<rect x="3" y="4" width="18" height="11" rx="1.500"/><path d="M8 21l4-6 4 6M12 15v6"/>',
        default => '',
    };
@endphp
<svg class="{{ $kelas ?? 'size-5' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $path !!}</svg>
