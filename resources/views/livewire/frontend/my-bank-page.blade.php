@section('title', 'Tài khoản nhận tiền')

<div class="h-full w-full overflow-y-auto sm:overflow-x-hidden">
    <div class="w-full flex flex-col justify-start items-start">
        <div class="bg-[#800080] w-full flex items-center justify-between px-4 py-4">
            <a href="{{ route('profile') }}" class="text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <h1 class="text-white text-lg font-medium">Tài khoản nhận tiền</h1>
            <div class="w-6 h-6"></div>
        </div>
        @if(Auth::check())
            <div class="w-full bg-gray-50 min-h-screen">
                @if($bankAccount)
                    <div class="p-4 space-y-4">
                            <div class="bg-white rounded-lg p-4 shadow-sm">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <div class="flex justify-between items-center mb-2">
                                            <span class="text-gray-600 text-sm">Ngân hàng nhận tiền</span>
                                            <span class="font-bold text-black">{{ $bankAccount['bank']['name'] ?? 'N/A' }}</span>
                                        </div>
                                        <div class="border-t border-gray-200 pt-2">
                                            <div class="flex justify-between items-center">
                                                <span class="text-gray-600 text-sm">Số tài khoản nhận tiền</span>
                                                <span class="font-bold text-black">{{ $bankAccount['account_number'] }}</span>
                                            </div>
                                        </div>
                                        <div class="border-t border-gray-200 pt-2 mt-2">
                                            <div class="flex justify-between items-center">
                                                <span class="text-gray-600 text-sm">Tên chủ tài khoản</span>
                                                <span class="font-bold text-black">{{ $bankAccount['account_name'] }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="ml-4 flex flex-col space-y-2">

                                        <button wire:click="showEditForm({{ $bankAccount['id'] }})" 
                                                class="text-blue-600 text-xs hover:text-blue-800">
                                            Sửa
                                        </button>
                                    
                                    </div>
                                </div>
                            </div>
                    </div>
                @else
                    <div class="p-4">
                        <div class="bg-white rounded-lg p-8 text-center">
                            <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Chưa có tài khoản ngân hàng</h3>
                            <p class="text-gray-600 mb-4">Thêm tài khoản ngân hàng để nhận tiền vay</p>
                        </div>
                    </div>
                @endif
                @if(empty($bankAccount))
                <div class="p-4">
                    <button wire:click="showAddForm" 
                            class="w-full bg-[#800080] text-white py-3 px-4 rounded-lg font-medium hover:bg-[#6a006a] transition-colors">
                        Thêm tài khoản ngân hàng
                    </button>
                </div>
            @endif
            </div>
        </div>

        @if($showForm)
        <div class="modal modal-open">
            <div class="modal-box w-full mx-4">
                <h3 class="text-lg font-medium mb-4">
                    {{ $editingAccount ? 'Sửa tài khoản ngân hàng' : 'Thêm tài khoản ngân hàng' }}
                </h3>
                
                <form wire:submit.prevent="save">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ngân hàng</label>
                            <select wire:model="bank_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                <option value="">Chọn ngân hàng</option>
                                @foreach($banks as $bank)
                                    <option value="{{ $bank['id'] }}">{{ $bank['name'] }}</option>
                                @endforeach
                            </select>
                            @error('bank_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Số tài khoản</label>
                            <input type="text" wire:model="account_number" 
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                placeholder="Nhập số tài khoản">
                            @error('account_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tên chủ tài khoản</label>
                            <input type="text" wire:model="account_name" 
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                placeholder="Nhập tên chủ tài khoản">
                            @error('account_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="flex space-x-3 mt-6">
                        <button type="button" wire:click="cancel" 
                                class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-400 transition-colors">
                            Hủy
                        </button>
                        <button type="submit" 
                                class="flex-1 bg-[#800080] text-white py-2 px-4 rounded-lg hover:bg-[#6a006a] transition-colors">
                            {{ $editingAccount ? 'Cập nhật' : 'Thêm' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif


        @if (session()->has('success'))
            <div class="fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50">
                {{ session('success') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="fixed top-4 right-4 bg-red-500 text-white px-4 py-2 rounded-lg shadow-lg z-50">
                {{ session('error') }}
            </div>
        @endif
    @else
        <div class="w-full p-4">
            <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 px-4 py-3 rounded">
                {{ $message }}
                <a href="" class="underline ml-1">Đăng nhập</a>
            </div>
        </div>
    @endif
</div>
