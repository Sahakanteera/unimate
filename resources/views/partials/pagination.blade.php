{{-- ตัวแบ่งหน้าแบบปุ่มแคปซูล ใช้: {{ $items->links('partials.pagination') }} --}}
@if($paginator->hasPages())
    <nav class="flex items-center justify-between gap-3" aria-label="เปลี่ยนหน้า">
        @if($paginator->onFirstPage())
            <span class="btn btn-secondary btn-sm pointer-events-none opacity-40"><x-ui.icon name="chevron-left" class="h-4 w-4" /> ก่อนหน้า</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="btn btn-secondary btn-sm"><x-ui.icon name="chevron-left" class="h-4 w-4" /> ก่อนหน้า</a>
        @endif

        <div class="hidden items-center gap-1 sm:flex">
            @foreach($elements as $element)
                @if(is_string($element))
                    <span class="px-2 text-sm text-ink-faint">{{ $element }}</span>
                @endif
                @if(is_array($element))
                    @foreach($element as $page => $url)
                        @if($page == $paginator->currentPage())
                            <span aria-current="page" class="grid h-9 min-w-9 place-items-center rounded-full bg-night px-3 text-sm font-medium text-white">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="grid h-9 min-w-9 place-items-center rounded-full px-3 text-sm text-ink-muted transition hover:bg-white hover:text-ink">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach
        </div>
        <span class="text-sm text-ink-muted sm:hidden">หน้า {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span>

        @if($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="btn btn-secondary btn-sm">ถัดไป <x-ui.icon name="chevron-right" class="h-4 w-4" /></a>
        @else
            <span class="btn btn-secondary btn-sm pointer-events-none opacity-40">ถัดไป <x-ui.icon name="chevron-right" class="h-4 w-4" /></span>
        @endif
    </nav>
@endif
