@section('title', 'Đơn vay')

<div class="w-full p-4">
    <div>
        <h1 class="text-lg font-semibold mb-4 text-center">Đơn vay</h1>
    </div>

    @if ($message)
        <div class="bg-red-500 text-white p-4 rounded mb-4">
            {{ $message }}
        </div>
    @endif
    
    <style>
        @media (max-width: 414px) {
            .loan-tabs .tab { font-size: 10px; padding: 0.25rem 0.25rem; white-space: nowrap; }
        }
    </style>
    <div class="tabs tabs-boxed mb-2 overflow-x-auto whitespace-nowrap loan-tabs">
        <div class="flex gap-1 justify-center mx-auto w-max">
        <a class="tab inline-flex {{ $tab == 'pending' ? 'tab-active border-b-4 border-blue-500' : '' }} text-[12px] px-3 py-1"
            wire:click.prevent="changeTab('pending')">Đang đợi hoàn trả</a>
        <a class="tab inline-flex {{ $tab == 'approved' ? 'tab-active border-b-4 border-blue-500' : '' }} text-[12px] px-3 py-1"
            wire:click.prevent="changeTab('approved')">Kết quả xét duyệt</a>
        <a class="tab inline-flex {{ $tab == 'paid' ? 'tab-active border-b-4 border-blue-500' : '' }} text-[12px] px-3 py-1"
            wire:click.prevent="changeTab('paid')">Trả nợ thành công</a>
        </div>
    </div>
    
        <div class="w-full px-4 pb-20">
            @if ($tab === 'pending')
                @forelse ($loanLogs as $log)
                    <div class="bg-white rounded-lg p-4 mb-4 shadow-sm">
                        <div class="flex items-start space-x-4">
                            
                            <div class="flex-1">
                                @php
                                    $base = (float) ($log->principal_due + $log->interest_due);
                                    $isOverdue = (int) $log->status === \App\Utils\Constants\LoanLogStatus::OVERDUE->value;
                                    $totalClientDue = $isOverdue ? $base + (float) $log->fee_due : $base;
                                @endphp
                                
                                <div class="space-y-2">
                                    <div class="flex items-center space-x-2">
                                        <span class="text-sm text-gray-600">Trạng thái:</span>
                                        @if($isOverdue)
                                            <span class="text-sm text-red-600 bg-red-100 px-2 py-1 rounded">Đã quá hạn</span>
                                        @else
                                            <span class="text-sm text-orange-600 bg-orange-100 px-2 py-1 rounded">Đang đợi thanh toán</span>
                                        @endif
                                    </div>
                                    
                                    <div class="flex items-center space-x-2">
                                        <span class="text-sm text-gray-600">Số tiền phải trả:</span>
                                        <span class="text-sm font-semibold text-gray-800">{{ number_format($totalClientDue, 0, ',', '.') }} VND</span>
                                    </div>
                                    
                                    <div class="flex items-center space-x-2">
                                        <span class="text-sm text-gray-600">Thời hạn cho vay:</span>
                                        <span class="text-sm text-gray-500">
                                            @if($log->due_date)
                                                {{ $log->due_date->format('d-m-Y') }}
                                            @elseif($log->userLoan && $log->userLoan->due_date)
                                                {{ $log->userLoan->due_date->format('d-m-Y') }}
                                            @else
                                                <span class="text-red-500">Chưa có ngày</span>
                                            @endif
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <button wire:click="viewLoanDetail('{{ $log->id }}')" 
                                class="w-full mt-4 bg-green-600 text-white font-semibold py-3 rounded-lg hover:bg-green-700 transition-colors cursor-pointer">
                            Thanh toán ngay
                        </button>
                    </div>
            @empty
                <div class="bg-white rounded-lg p-8 text-center">
                    <div class="text-gray-400 mb-4">
                        <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <p class="text-gray-600 text-lg">Không có kỳ cần thanh toán</p>
                </div>
            @endforelse
        @elseif ($tab === 'paid')
            @forelse ($loanLogs as $log)
                <div class="bg-white rounded-lg p-4 mb-4 shadow-sm">
                    <div class="flex items-start space-x-4">
                        <div class="flex-1">
                            <div class="space-y-2">
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm text-gray-600">Trạng thái:</span>
                                    <span class="text-sm text-green-600 bg-green-100 px-2 py-1 rounded">Đã thanh toán</span>
                                </div>
                                
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm text-gray-600">Số tiền đã trả:</span>
                                    <span class="text-sm font-semibold text-gray-800">{{ number_format($log->total_paid, 0, ',', '.') }} VND</span>
                                </div>
                                
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm text-gray-600">Ngày thanh toán:</span>
                                    <span class="text-sm text-gray-500">{{ $log->actual_due_date ? $log->actual_due_date->format('d-m-Y H:i') : 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-lg p-8 text-center">
                    <div class="text-gray-400 mb-4">
                        <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <p class="text-gray-600 text-lg">Không có giao dịch thanh toán</p>
                </div>
            @endforelse
        @else
            @forelse ($loans as $loan)
                <div class="bg-white rounded-lg p-4 mb-4 shadow-sm">
                    <div class="flex items-start space-x-4">
                        
                        
                        <div class="flex-1">
                            <div class="space-y-2">
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm text-gray-600">Trạng thái:</span>
                                    <span class="text-sm {{ $tab == 'approved' ? 'text-green-600 bg-green-100' : 'text-blue-600 bg-blue-100' }} px-2 py-1 rounded">{{ $loan->getStatusTextAttribute() }}</span>
                                </div>
                                
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm text-gray-600">Số tiền cần vay:</span>
                                    <span class="text-sm font-semibold text-gray-800">{{ number_format($loan->principal_amount, 0, ',', '.') }} VND</span>
                                </div>
                                
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm text-gray-600">
                                        @if($tab == 'approved')
                                            Ngày tạo đơn:
                                        @elseif($tab == 'paid')
                                            Thời gian kết thúc:
                                        @endif
                                    </span>
                                    <span class="text-sm text-gray-500">
                                        @if($tab == 'approved')
                                            {{ $loan->created_at->format('d-m-Y H:i') }}
                                        @elseif($tab == 'paid')
                                            @if($loan->updated_at)
                                                {{ $loan->updated_at->format('d-m-Y H:i') }}
                                            @else
                                                {{ $loan->created_at->format('d-m-Y H:i') }}
                                            @endif
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-lg p-8 text-center">
                    <div class="text-gray-400 mb-4">
                        <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <p class="text-gray-600 text-lg">Không có dữ liệu</p>
                </div>
            @endforelse
        @endif
        </div>
    </div>
</div>
