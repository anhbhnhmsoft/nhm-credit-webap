@section('title', 'Đăng nhập')

<div class="container mx-auto p-6">
    <h2 class="text-2xl font-semibold text-center mb-6">Đăng nhập</h2>

    <!-- Hiển thị thông báo lỗi hoặc thành công -->
    @if(session('error'))
        <div class="bg-red-500 text-white p-4 mb-4 rounded-md">
            {{ session('error') }}
        </div>
    @endif

    @if(session('success'))
        <div class="bg-green-500 text-white p-4 mb-4 rounded-md">
            {{ session('success') }}
        </div>
    @endif

    <!-- Form đăng nhập -->
    <form action="{{ route('login.submit') }}" method="POST">
        @csrf

        <div class="mb-4">
            <label for="phone" class="block text-sm font-medium text-gray-700">Số điện thoại</label>
            <input type="text" name="phone" id="phone" class="w-full p-3 border border-gray-300 rounded-md" 
                   value="{{ old('phone') }}" required>
            @error('phone')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-4">
            <label for="otp" class="block text-sm font-medium text-gray-700">Mã OTP</label>
            <input type="text" name="otp" id="otp" class="w-full p-3 border border-gray-300 rounded-md" 
                   value="{{ old('otp') }}" required>
            @error('otp')
                <span class="text-red-500 text-sm">{{ $message }}</span>
            @enderror
        </div>

        <div class="flex justify-center">
            <button type="submit" class="px-6 py-3 bg-blue-500 text-white rounded-md">Đăng nhập</button>
        </div>
    </form>
</div>