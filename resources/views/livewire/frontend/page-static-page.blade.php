<div class="w-full h-full">
    <div class="shadow sticky top-0 z-10 py-3 px-4 flex justify-between items-center w-full bg-[#8b1dd0] border-b border-white">
        <a href="{{ route('profile') }}" class="text-white" aria-label="Quay lại">
            <svg viewBox="64 64 896 896" focusable="false" data-icon="arrow-left" width="1em" height="1em" fill="currentColor" aria-hidden="true"><path d="M872 474H286.9l350.2-304c5.6-4.9 2.2-14-5.2-14h-88.5c-3.9 0-7.6 1.4-10.5 3.9L155 487.8a31.96 31.96 0 000 48.3L535.1 866c1.5 1.3 3.3 2 5.2 2h91.5c7.4 0 10.8-9.2 5.2-14L286.9 550H872c4.4 0 8-3.6 8-8v-60c0-4.4-3.6-8-8-8z"></path></svg>
        </a>
        <span class="text-sm text-white font-light">{{ $page->title }}</span>
        <span class="text-white">
            @if(!empty($page->icon_svg))
            <div class="w-[25px] h-[25px] leading-none">{!! str_replace('<svg','<svg class=\"w-full h-full\"',$page->icon_svg) !!}</div>
        @endif
        </span>
    </div>

    <div class="p-4">
        <div class="flex items-center gap-2 mb-3">
            <h1 class="text-lg font-semibold">{{ $page->title }}</h1>
        </div>
        <div class="rich-content">{!! $page->content !!}</div>
    </div>
</div>


