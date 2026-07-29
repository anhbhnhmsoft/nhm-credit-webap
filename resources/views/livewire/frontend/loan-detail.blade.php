@section('title', 'Chi tiết đơn vay')

<div class="h-full w-full overflow-y-auto sm:overflow-x-hidden">
    <div class="w-full flex flex-col justify-start items-start">
        <div class="bg-green-100 w-full flex items-center justify-between px-4 py-4">
            <a href="{{ route('loan-application') }}" class="text-black">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </a>
            <h1 class="text-black text-lg font-medium">Chi tiết đơn vay</h1>
            <div class="w-6 h-6"></div>
        </div>

        @if ($message)
            <div class="bg-red-500 text-white p-4 rounded mb-4 mx-4 mt-4">
                {{ $message }}
            </div>
        @endif

        @if ($loan && $loanLog)
            <div class="bg-green-100 w-full relative h-[150px]">
                <div class="bg-white rounded-lg mx-4 mt-8 p-6 shadow-2xl relative z-20 transform -translate-y-4">
                    <div class="text-center">
                        <p class="text-sm text-gray-600 mb-2">Tổng số tiền cần phải trả</p>
                        @php
                            $totalDue = (float) $loanLog->principal_due + (float) $loanLog->interest_due + (float) $loanLog->fee_due;
                        @endphp
                        <div class="text-4xl font-bold text-black mb-6">{{ number_format($totalDue, 0, ',', '.') }} VND</div>
                        
                        <div class="flex items-center justify-between">
                            <div class="text-left">
                                <p class="text-sm text-gray-600">Ngày hoàn trả khoản vay</p>
                                <p class="text-sm font-medium text-gray-800">{{ optional($loanLog->due_date)->format('d-m-Y') }}</p>
                            </div>
                            <div class="text-center">
                                @if(!empty($loan->overdue_status))
                                    <span class="text-sm font-medium {{ 
                                        str_contains(strtolower($loan->overdue_status), 'quá hạn') ? 'text-red-600' : 
                                        (str_contains(strtolower($loan->overdue_status), 'gia hạn') ? 'text-yellow-600' : 'text-green-600')
                                    }}">{{ $loan->overdue_status }}</span>
                                @else
                                    <span class="text-sm text-green-600 font-medium"></span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="bg-gray-50 w-full h-1/3"></div>

            <div class="w-full px-4 py-6 pt-[85px]">
                <div class="bg-white border-2 border-gray-50 rounded-lg p-6 shadow-sm">
                    <div class="border border-gray-200 rounded-lg overflow-hidden">
                        <div class="flex border-b border-gray-200">
                            <div class="w-1/2 p-3 border-r border-gray-200 bg-gray-50">
                                <span class="text-sm text-gray-600">Họ tên</span>
                            </div>
                            <div class="w-1/2 p-3 border-r border-gray-200 bg-gray-50">
                                <span class="text-sm text-gray-600">Số CCCD</span>
                            </div>
                        </div>
                        <div class="flex border-b border-gray-200">
                            <div class="w-1/2 p-3 border-r border-gray-200">
                                <span class="text-sm font-medium text-gray-800">{{ $loan->user->name ?? $loan->user_name ?? 'N/A' }}</span>
                            </div>
                            <div class="w-1/2 p-3">
                                <span class="text-sm font-medium text-gray-800">{{ $loan->user->number_card ?? 'N/A' }}</span>
                            </div>
                        </div>

                        <div class="flex border-b border-gray-200">
                            <div class="w-1/2 p-3 border-r border-gray-200 bg-gray-50">
                                <span class="text-sm text-gray-600">Tài khoản nhận tiền</span>
                            </div>
                            <div class="w-1/2 p-3 border-r border-gray-200 bg-gray-50">
                                <span class="text-sm text-gray-600">Ngân hàng</span>
                            </div>
                        </div>
                        <div class="flex border-b border-gray-200">
                            <div class="w-1/2 p-3 border-r border-gray-200">
                                <span class="text-sm font-medium text-gray-800">{{ $bankAccount->account_number ?? 'N/A' }}</span>
                            </div>
                            <div class="w-1/2 p-3">
                                <span class="text-sm font-medium text-gray-800">{{ $bankAccount->bank_name ?? 'N/A' }}</span>
                            </div>
                        </div>

                        <div class="flex border-b border-gray-200">
                            <div class="w-1/2 p-3 border-r border-gray-200 bg-gray-50">
                                <span class="text-sm text-gray-600">Thời gian nộp đơn</span>
                            </div>
                            <div class="w-1/2 p-3 border-r border-gray-200 bg-gray-50">
                                <span class="text-sm text-gray-600">Số tiền giải ngân</span>
                            </div>
                        </div>
                        <div class="flex border-b border-gray-200">
                            <div class="w-1/2 p-3 border-r border-gray-200">
                                <span class="text-sm font-medium text-gray-800">{{ $loan->start_date ? $loan->start_date->format('d-m-Y') : 'N/A' }}</span>
                            </div>
                            <div class="w-1/2 p-3">
                                <span class="text-sm font-medium text-gray-800">{{ number_format($loan->disbursed_amount, 0, ',', '.') }} VND</span>
                            </div>
                        </div>

                        <div class="flex border-b border-gray-200">
                            <div class="w-1/2 p-3 border-r border-gray-200 bg-gray-50">
                                <span class="text-sm text-gray-600">Kỳ hạn xin vay tiền</span>
                            </div>
                            <div class="w-1/2 p-3 border-r border-gray-200 bg-gray-50">
                                <span class="text-sm text-gray-600">Số tiền đến hạn thanh toán</span>
                            </div>
                        </div>
                        <div class="flex border-b border-gray-200">
                            <div class="w-1/2 p-3 border-r border-gray-200">
                                <span class="text-sm font-medium text-gray-800">{{ $loan->term_months }}</span>
                            </div>
                            <div class="w-1/2 p-3">
                                <span class="text-sm font-medium text-gray-800">{{ number_format($totalDue, 0, ',', '.') }} VND</span>
                            </div>
                        </div>

                        <div class="flex border-b border-gray-200">
                            <div class="w-1/2 p-3 border-r border-gray-200 bg-gray-50">
                                <span class="text-sm text-gray-600">Phí quá thanh toán</span>
                            </div>
                            <div class="w-1/2 p-3 border-r border-gray-200 bg-gray-50">
                                <span class="text-sm text-gray-600">Tổng số tiền cần hoàn trả</span>
                            </div>
                        </div>
                        <div class="flex">
                            <div class="w-1/2 p-3 border-r border-gray-200">
                                <span class="text-sm font-medium text-gray-800">{{ number_format($loanLog->fee_due, 0, ',', '.') }} VND</span>
                            </div>
                            <div class="w-1/2 p-3">
                                <span class="text-sm font-bold text-black">{{ number_format($totalDue, 0, ',', '.') }} VND</span>
                            </div>
                        </div>
                    </div>

                    <button wire:click="payNow" 
                            class="w-full mt-6 bg-green-600 text-white py-4 rounded-lg font-medium text-lg hover:bg-green-700 transition-colors">
                        Lập tức thanh toán
                    </button>
                </div>
            </div>
        @else
            <div class="w-full px-4 py-6">
                <div class="bg-white rounded-lg p-8 text-center">
                    <div class="text-gray-400 mb-4">
                        <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                    <p class="text-gray-600 text-lg">{{ $message ?: 'Không tìm thấy thông tin khoản vay' }}</p>
                </div>
            </div>
        @endif
    </div>
</div>
