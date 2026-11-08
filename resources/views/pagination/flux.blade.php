{{-- Mirrors the flux:pagination markup, but with real links instead of wire:click,
     since this view is rendered from plain (non-Livewire) controllers. --}}
@if ($paginator->hasPages())
    <div class="@container pt-3 border-t border-zinc-100 dark:border-zinc-700 flex justify-between items-center gap-3" data-flux-pagination>
        @if ($paginator->total() > 0)
            <div class="text-zinc-500 dark:text-zinc-400 text-xs font-medium whitespace-nowrap">
                {!! __('Showing') !!} {{ $paginator->firstItem() }} {!! __('to') !!} {{ $paginator->lastItem() }} {!! __('of') !!} {{ $paginator->total() }} {!! __('results') !!}
            </div>
        @else
            <div></div>
        @endif

        {{-- Mobile pagination --}}
        <div class="flex @[40rem]:hidden items-center bg-white border border-zinc-200 rounded-[8px] p-[1px] dark:bg-white/10 dark:border-white/10">
            @if ($paginator->onFirstPage())
                <div aria-disabled="true" aria-label="{{ __('pagination.previous') }}" class="flex justify-center items-center size-8 sm:size-6 rounded-[6px] text-zinc-300 dark:text-zinc-500">
                    <flux:icon.chevron-left variant="micro" class="rtl:hidden" />
                    <flux:icon.chevron-right variant="micro" class="hidden rtl:inline" />
                </div>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" wire:navigate aria-label="{{ __('pagination.previous') }}" class="flex justify-center items-center size-8 sm:size-6 rounded-[6px] text-zinc-400 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-white/20 hover:text-zinc-800 dark:hover:text-white">
                    <flux:icon.chevron-left variant="micro" class="rtl:hidden" />
                    <flux:icon.chevron-right variant="micro" class="hidden rtl:inline" />
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" wire:navigate aria-label="{{ __('pagination.next') }}" class="flex justify-center items-center size-8 sm:size-6 rounded-[6px] text-zinc-400 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-white/20 hover:text-zinc-800 dark:hover:text-white">
                    <flux:icon.chevron-right variant="micro" class="rtl:hidden" />
                    <flux:icon.chevron-left variant="micro" class="hidden rtl:inline" />
                </a>
            @else
                <div aria-label="{{ __('pagination.next') }}" class="flex justify-center items-center size-8 sm:size-6 rounded-[6px] text-zinc-300 dark:text-zinc-500">
                    <flux:icon.chevron-right variant="micro" class="rtl:hidden" />
                    <flux:icon.chevron-left variant="micro" class="hidden rtl:inline" />
                </div>
            @endif
        </div>

        {{-- Desktop pagination --}}
        <div class="hidden @[40rem]:flex items-center bg-white border border-zinc-200 rounded-[8px] p-[1px] dark:bg-white/10 dark:border-white/10">
            @if ($paginator->onFirstPage())
                <div aria-disabled="true" aria-label="{{ __('pagination.previous') }}" class="flex justify-center items-center size-6 rounded-[6px] text-zinc-300 dark:text-zinc-500">
                    <flux:icon.chevron-left variant="micro" class="rtl:hidden" />
                    <flux:icon.chevron-right variant="micro" class="hidden rtl:inline" />
                </div>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" wire:navigate aria-label="{{ __('pagination.previous') }}" class="flex justify-center items-center size-6 rounded-[6px] text-zinc-400 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-white/20 hover:text-zinc-800 dark:hover:text-white">
                    <flux:icon.chevron-left variant="micro" class="rtl:hidden" />
                    <flux:icon.chevron-right variant="micro" class="hidden rtl:inline" />
                </a>
            @endif

            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <div aria-disabled="true" class="cursor-default flex justify-center items-center text-xs size-6 rounded-[6px] font-medium dark:text-zinc-400 text-zinc-400">{{ $element }}</div>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <div aria-current="page" class="cursor-default flex justify-center items-center text-xs h-6 px-2 rounded-[6px] font-medium dark:text-white text-zinc-800">{{ $page }}</div>
                        @else
                            <a href="{{ $url }}" wire:navigate aria-label="{{ __('Go to page :page', ['page' => $page]) }}" class="flex justify-center items-center text-xs h-6 px-2 rounded-[6px] text-zinc-400 font-medium dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-white/20 hover:text-zinc-800 dark:hover:text-white">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" wire:navigate aria-label="{{ __('pagination.next') }}" class="flex justify-center items-center size-6 rounded-[6px] text-zinc-400 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-white/20 hover:text-zinc-800 dark:hover:text-white">
                    <flux:icon.chevron-right variant="micro" class="rtl:hidden" />
                    <flux:icon.chevron-left variant="micro" class="hidden rtl:inline" />
                </a>
            @else
                <div aria-label="{{ __('pagination.next') }}" class="flex justify-center items-center size-6 rounded-[6px] text-zinc-300 dark:text-zinc-500">
                    <flux:icon.chevron-right variant="micro" class="rtl:hidden" />
                    <flux:icon.chevron-left variant="micro" class="hidden rtl:inline" />
                </div>
            @endif
        </div>
    </div>
@endif
