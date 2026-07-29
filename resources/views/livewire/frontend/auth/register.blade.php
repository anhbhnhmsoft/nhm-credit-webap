@section('title', 'Đăng ký tài khoản')

<div class="max-w-md mx-auto mt-10 bg-white shadow-lg border border-gray-100 rounded p-6">
    <div class="w-full flex justify-start items-center">
        <a href="javascript:history.back()" class="flex items-center space-x-2">
        <span role="img" aria-label="arrow-left" tabindex="-1" class="anticon anticon-arrow-left h-6 w-6">
            <svg viewBox="64 64 896 896" focusable="false" data-icon="arrow-left" width="1em" height="1em" fill="currentColor" aria-hidden="true">
                <path d="M872 474H286.9l350.2-304c5.6-4.9 2.2-14-5.2-14h-88.5c-3.9 0-7.6 1.4-10.5 3.9L155 487.8a31.96 31.96 0 000 48.3L535.1 866c1.5 1.3 3.3 2 5.2 2h91.5c7.4 0 10.8-9.2 5.2-14L286.9 550H872c4.4 0 8-3.6 8-8v-60c0-4.4-3.6-8-8-8z">
                </path>
            </svg>
        </span>
        </a>
    </div>

    <h1 class="text-2xl font-bold mb-6 text-center">Đăng ký tài khoản</h1>

    <form wire:submit.prevent="submit" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium mb-1">Số điện thoại</label>
            <input type="tel" wire:model="phone" class="w-full border rounded px-3 py-2"
                   placeholder="VD: 0865 643 858" required>
            @error('phone') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Họ và tên</label>
            <input type="text" wire:model="fullName" class="w-full border rounded px-3 py-2"
                   placeholder="Nhập họ và tên" required>
            @error('fullName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Email (tùy chọn)</label>
            <input type="email" wire:model="email" class="w-full border rounded px-3 py-2"
                   placeholder="Nhập email">
            @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Mật khẩu</label>
            <div class="relative">
                <input type="password" id="password" wire:model="password" class="w-full border rounded px-3 py-2 pr-10"
                       placeholder="Nhập mật khẩu (tối thiểu 6 ký tự)" required>
                <button type="button" onclick="togglePassword('password')" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                    <svg id="password-eye" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </button>
            </div>
            @error('password') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Xác nhận mật khẩu</label>
            <div class="relative">
                <input type="password" id="confirmPassword" wire:model="confirmPassword" class="w-full border rounded px-3 py-2 pr-10"
                       placeholder="Nhập lại mật khẩu" required>
                <button type="button" onclick="togglePassword('confirmPassword')" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                    <svg id="confirmPassword-eye" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </button>
            </div>
            @error('confirmPassword') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="mt-6">
            <button type="submit" class="w-full bg-green-600 text-white font-semibold py-2 px-4 rounded cursor-pointer hover:bg-green-700">Đăng ký</button>
        </div>
    </form>

    @if (session()->has('success'))
        <div class="mt-4 text-green-600 text-center">{{ session('success') }}</div>
    @endif

    <div class="mt-4 text-center">
        <a href="{{ route('login') }}" class="text-black hover:underline">
            Đã có tài khoản? Đăng nhập
        </a>
    </div>
</div>

<script>
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const eye = document.getElementById(inputId + '-eye');
    
    if (input.type === 'password') {
        input.type = 'text';
        eye.innerHTML = `
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
        `;
    } else {
        input.type = 'password';
        eye.innerHTML = `
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
        `;
    }
}
</script>
