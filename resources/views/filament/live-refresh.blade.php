{{-- Re-draws the page every few seconds so changes other people make appear without a refresh. --}}
<div wire:poll.{{ $every }} data-live-refresh aria-hidden="true" hidden></div>
