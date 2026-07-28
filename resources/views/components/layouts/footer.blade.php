<div class="py-1 bg-white border-solid flex flex-row justify-between items-center w-full shadow-lg px-2">
    <a href="{{ route('home') }}" class="flex flex-col flex-1 items-center justify-start py-1 px-2 rounded-md text-center text-xs font-medium text-neutral-700">
        <img class="h-5 w-5" src="/images/{{ Route::currentRouteName() == 'home' ? 'home-active.png' : 'home.png' }}">
        <span class="mt-1 font-normal">Khoản vay</span>
    </a>
    <a href="{{ route('loan-application') }}" class="flex flex-col flex-1 items-center justify-start py-1 px-2 rounded-md text-center text-xs font-medium text-neutral-700">
        <img class="h-5 w-5" src="/images/{{ Route::currentRouteName() == 'loan-application' ? 'loan-active.png' : 'loan.png' }}">
        <span class="mt-1 font-normal">Đơn vay</span>
    </a>
    <a href="{{ route('profile') }}" class="flex flex-col flex-1 items-center justify-start py-1 px-2 rounded-md text-center text-xs font-medium text-neutral-700">
        <img class="h-5 w-5" src="/images/{{ Route::currentRouteName() == 'profile' ? 'user-active.png' : 'user.png' }}">
        <span class="mt-1 font-normal">Của tôi</span>
    </a>
</div>
