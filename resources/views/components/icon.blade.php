@props(['name' => 'washer'])
<svg {{ $attributes->merge(['class' => 'icon']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
@switch($name)
@case('home')<path d="m3 10 9-7 9 7v10H3z"/><path d="M9 20v-7h6v7"/>@break
@case('calendar')<rect x="3" y="5" width="18" height="16" rx="3"/><path d="M7 3v4m10-4v4M3 11h18m-13 4h2m4 0h2m-8 3h2"/>@break
@case('plus')<path d="M12 5v14M5 12h14"/>@break
@case('arrow')<path d="M4 12h16m-6-6 6 6-6 6"/>@break
@case('clock')<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>@break
@case('check')<path d="m5 12 4 4L19 6"/>@break
@case('logout')<path d="M9 4H4v16h5m0-8h12m-5-5 5 5-5 5"/>@break
@case('user')<circle cx="12" cy="8" r="4"/><path d="M4 21v-2a8 8 0 0 1 16 0v2"/>@break
@case('info')<circle cx="12" cy="12" r="9"/><path d="M12 11v6m0-10v.1"/>@break
@case('pin')<path d="M19 10c0 6-7 11-7 11S5 16 5 10a7 7 0 0 1 14 0Z"/><circle cx="12" cy="10" r="2"/>@break
@case('search')<circle cx="10" cy="10" r="6"/><path d="m15 15 6 6"/>@break
@case('edit')<path d="m14 5 5 5M3 21l5-1L21 7l-5-5L3 15z"/>@break
@case('trash')<path d="M3 6h18M9 6V3h6v3M5 6l1 15h12l1-15M10 10v7m4-7v7"/>@break
@case('spark')<path d="m12 2 2.5 7.5L22 12l-7.5 2.5L12 22l-2.5-7.5L2 12l7.5-2.5Z"/>@break
@default<rect x="4" y="2" width="16" height="20" rx="2"/><path d="M4 7h16M7 4.5h.1m3 0h.1"/><circle cx="12" cy="14" r="4.5"/><path d="M8 14c3-3 5 3 8 0"/>
@endswitch
</svg>
