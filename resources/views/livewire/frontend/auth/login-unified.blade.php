<div class="max-w-md mx-auto mt-10 bg-white shadow rounded p-6">
    <h1 class="text-2xl font-bold mb-2 text-center">Đăng nhập</h1>
    <p class="text-center text-gray-600 mb-4">Đăng nhập bằng số điện thoại</p>

    <div class="border rounded p-4 border-gray-200 shadow-sm">
        <h2 class="font-semibold mb-2">Đăng nhập</h2>
        <div wire:ignore>
            <div id="phone_input">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <label class="block text-sm font-medium mb-1">Số điện thoại</label>
                <input type="tel" id="phoneNumber" class="w-full border rounded px-3 py-2 border-gray-200 shadow-sm mb-3" placeholder="0912345678" required>
                <button type="button" id="login_phone_btn" onclick="loginWithPhone()" class="w-full bg-green-600 text-white font-semibold py-2 rounded transition hover:bg-green-700 disabled:opacity-60 cursor-pointer">Đăng nhập</button>
            </div>
        </div>
        @vite('resources/js/login-phone.js')
    </div>

    <div class="mt-4 text-center text-sm text-gray-700">
        Chưa có tài khoản? <a href="{{ route('register') }}" class="text-blue-600 hover:underline">Đăng ký ngay</a>
    </div>
    <div class="text-center text-sm">
        <div>
            <a href="{{ route('forgot-password') }}" class="hover:underline">Quên mật khẩu?</a>
        </div>
    </div>
</div>

<script>
</script>
