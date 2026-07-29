@section('title', 'Quên mật khẩu')

<div class="max-w-md mx-auto mt-10 bg-white shadow rounded p-6">
    <h1 class="text-2xl font-bold mb-6 text-center">Quên mật khẩu</h1>
            
    @if($message)
        <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded">
            {{ $message }}
        </div>
    @endif

    @if($error)
        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
            {{ $error }}
        </div>
    @endif

    @if($step == 1)
        <form wire:submit.prevent="step1">
            <div>
                <label class="block text-sm font-medium mb-1">Số điện thoại</label>
                <input wire:model="phone" 
                       type="tel" 
                       class="w-full border rounded px-3 py-2" 
                       placeholder="VD: 0865 643 858" 
                       required>
                @error('phone') 
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                @enderror
            </div>

            <div class="mt-3">
                <label class="block text-sm font-medium pt-3 pb-1">Số thẻ CCCD/CMND</label>
                <input wire:model="number_card" 
                       type="text" 
                       class="w-full border rounded px-3 py-2" 
                       placeholder="Nhập số thẻ căn cước" 
                       required>
                @error('number_card') 
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                @enderror
            </div>

            <div class="mt-4">
                <button type="submit" 
                        class="w-full bg-green-600 text-white font-bold py-2 px-4 rounded cursor-pointer hover:bg-green-700">
                    Xác thực thông tin
                </button>
            </div>
        </form>
    @endif

    @if($step == 2)
        <form wire:submit.prevent="step2">
            <div>
                <label class="block text-sm font-medium mb-1">Mật khẩu mới</label>
                <div class="relative">
                    <input wire:model="new_password" 
                           type="password" 
                           id="new_password"
                           class="w-full border rounded px-3 py-2 pr-10" 
                           placeholder="Nhập mật khẩu mới" 
                           required>
                    <button type="button" onclick="togglePassword('new_password')" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                        <svg id="new_password-eye" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>
                @error('new_password') 
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                @enderror
            </div>

            <div class="mt-3">
                <label class="block text-sm font-medium mb-1">Xác nhận mật khẩu mới</label>
                <div class="relative">
                    <input wire:model="confirm_password" 
                           type="password" 
                           id="confirm_password"
                           class="w-full border rounded px-3 py-2 pr-10" 
                           placeholder="Nhập lại mật khẩu mới" 
                           required>
                    <button type="button" onclick="togglePassword('confirm_password')" class="absolute inset-y-0 right-0 pr-3 flex items-center">
                        <svg id="confirm_password-eye" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>
                @error('confirm_password') 
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p> 
                @enderror
            </div>

            <div class="flex space-x-2 mt-4">
                <button type="button" 
                        wire:click="backToStep1"
                        class="flex-1 bg-gray-200 text-black font-bold py-2 px-4 rounded cursor-pointer hover:bg-gray-100">
                    Quay lại
                </button>
                <button type="submit" 
                        class="flex-1 bg-green-600 text-white font-bold py-2 px-4 rounded cursor-pointer hover:bg-green-700">
                    Đặt lại mật khẩu
                </button>
            </div>
        </form>
    @endif

    @if($step == 3)
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100">
                <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <h3 class="mt-2 text-lg font-medium text-gray-900">Đặt lại mật khẩu thành công!</h3>
            <p class="mt-1 text-sm text-gray-500">
                Bạn có thể đăng nhập với mật khẩu mới.
            </p>
            <div class="mt-6">
                <a href="{{ route('login') }}" 
                   class="inline-flex items-center px-4 py-2 text-sm font-bold rounded-md text-white bg-green-600 hover:bg-green-700">
                    Đăng nhập ngay
                </a>
            </div>
        </div>
    @endif

    @if($step != 3)
        <div class="mt-4 text-center">
            <a href="{{ route('login') }}" class="text-black hover:underline">
                Quay lại đăng nhập
            </a>
        </div>
    @endif
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
