<div class="max-w-md mx-auto mt-10 bg-white shadow rounded p-6">
    <h1 class="text-2xl font-bold mb-2 text-center">Đăng ký tài khoản</h1>
    <p class="text-center text-gray-600 mb-4">Nhập thông tin để tạo tài khoản</p>

    <form wire:submit.prevent="submitPassword" class="space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium mb-1">Số điện thoại</label>
            <input type="tel" wire:model="phone" class="w-full border rounded px-3 py-2 border-gray-200 shadow-sm" placeholder="VD: 0865 643 858" required>
            @error('phone') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Họ và tên</label>
            <input type="text" wire:model="fullName" class="w-full border rounded px-3 py-2 border-gray-200 shadow-sm" placeholder="VD: Nguyễn Văn A" required>
            @error('fullName') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Email (không bắt buộc)</label>
            <input type="email" wire:model="email" class="w-full border rounded px-3 py-2 border-gray-200 shadow-sm" placeholder="example@email.com">
            @error('email') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Mật khẩu</label>
            <div class="relative">
                <input type="password" id="password_register_unified" wire:model="password" class="w-full border rounded px-3 py-2 pr-10 border-gray-200 shadow-sm" placeholder="Nhập mật khẩu" required>
                <button type="button" onclick="togglePassword('password_register_unified')" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                    <svg id="password_register_unified-eye" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </button>
            </div>
            @error('password') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Xác nhận mật khẩu</label>
            <div class="relative">
                <input type="password" id="confirmPassword_register_unified" wire:model="confirmPassword" class="w-full border rounded px-3 py-2 pr-10 border-gray-200 shadow-sm" placeholder="Nhập lại mật khẩu" required>
                <button type="button" onclick="togglePassword('confirmPassword_register_unified')" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                    <svg id="confirmPassword_register_unified-eye" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                </button>
            </div>
            @error('confirmPassword') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
        </div>
        <button type="submit" class="w-full bg-[#fef4bf] text-black font-semibold py-2 rounded cursor-pointer">Đăng ký</button>
    </form>

    <div class="mt-4 text-center text-sm text-gray-700">
        Đã có tài khoản? <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Đăng nhập</a>
    </div>
</div>

<script>
  function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const eye = document.getElementById(inputId + '-eye');
    if (!input || !eye) return;
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
