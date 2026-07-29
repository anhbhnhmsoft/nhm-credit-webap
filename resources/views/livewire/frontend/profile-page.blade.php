@section('title', 'Của tôi')

<div class="h-full w-full overflow-y-auto sm:overflow-x-hidden">
    <div class="w-full flex flex-col justify-start items-start">
        <div class="bg-green-100 w-full flex flex-col justify-center items-center h-[220px] space-y-3">
            <img class="h-[56px]" src="/images/user_default.png">
            @auth
                @php
                    $raw = preg_replace('/\D/','', auth()->user()->phone ?? '');
                    if (strlen($raw) >= 7) {
                        $masked = substr($raw, 0, 3) . '****' . substr($raw, -3);
                    } else {
                        $masked = $raw ?: 'Chưa cập nhật';
                    }
                @endphp
                <span class="text-sm text-black">{{ $masked }}</span>
            @else
                <a href="{{ route('login') }}" class="px-4 py-2 bg-white text-black rounded text-sm font-medium">Đăng nhập</a>
            @endauth
        </div>
        
        <div class="w-full bg-white">
            <ul class="divide-y divide-gray-200">
                <li>
                    <a href="{{ route('my-bank') }}" class="flex items-center px-4 py-4 hover:bg-green-50 transition-colors">
                        <svg class="w-6 h-6 text-gray-700 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                        </svg>
                        <span class="text-gray-700">Quản lý thẻ ngân hàng</span>
                    </a>
                </li>
                
                @foreach($staticPages as $ps)
                <li>
                    <a href="{{ route('frontend.page-static', ['slug' => $ps->slug]) }}" class="flex items-center px-4 py-4 hover:bg-green-50 transition-colors">
                        @if(!empty($ps->icon_svg))
                            <span class="mr-3 w-6 h-6 leading-none">{!! str_replace('<svg','<svg class=\'w-full h-full\'', $ps->icon_svg) !!}</span>
                        @else
                            <svg class="w-6 h-6 text-gray-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                        @endif
                        <span class="text-gray-700">{{ $ps->title }}</span>
                    </a>
                </li>
                @endforeach
                
                <li>
                    <a href="{{ route('my-setting') }}" class="flex items-center px-4 py-4 hover:bg-green-50 transition-colors">
                        <svg class="w-6 h-6 text-gray-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span class="text-gray-700">Thiết lập</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>
