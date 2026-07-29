@section('title', 'Thiết lập')

<div class="h-full w-full overflow-y-auto sm:overflow-x-hidden">
    <div class="w-full flex flex-col justify-start items-start">
        <div class="bg-green-100 w-full flex items-center justify-between px-4 py-4">
            <a href="{{ route('profile') }}" class="text-black">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <h1 class="text-black text-lg font-medium">Thiết lập</h1>
            <div class="w-6 h-6"></div>
        </div>

        <div class="w-full bg-gray-50 min-h-screen p-4">
            <div class="bg-white rounded-lg p-6">

                @if (!empty($logoUrl))
                    <div class="mb-6 flex items-center space-x-3 justify-center">
                        <img src="{{ route('public_image', ['file_path' => $logoUrl]) }}" alt="Logo"
                            class="h-96 object-contain" />
                    </div>
                @endif

                @auth
                    <form method="POST" action="{{ route('logout') }}" class="flex justify-center">
                        @csrf
                        <button type="submit"
                            class="px-4 py-2 bg-green-600 text-white font-bold rounded cursor-pointer hover:bg-green-700">Đăng
                            xuất</button>
                    </form>
                @else
                    <div class="flex justify-center">
                        <a href="{{ route('login') }}"
                            class="px-4 py-2 bg-green-600 text-white font-semibold rounded inline-block mx-auto">Đăng
                            nhập</a>
                    </div>
                @endauth
            </div>
        </div>
    </div>
</div>
