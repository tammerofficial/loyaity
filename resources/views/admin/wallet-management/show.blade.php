@extends('layouts.admin')

@section('title', 'تفاصيل العميل - ' . $customer->name)

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $customer->name }}</h1>
                <p class="text-gray-600">{{ $customer->email }}</p>
            </div>
            <a href="{{ route('admin.wallet-management.index') }}" 
               class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                العودة للقائمة
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Customer Info Card -->
        <div class="lg:col-span-1">
            <div class="bg-white shadow-md rounded-lg p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">معلومات العميل</h2>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">الاسم</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $customer->name }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">البريد الإلكتروني</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $customer->email }}</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700">تاريخ التسجيل</label>
                        <p class="mt-1 text-sm text-gray-900">{{ $customer->created_at->format('Y-m-d H:i') }}</p>
                    </div>
                    
                    @if($customer->loyaltyCard)
                        <div>
                            <label class="block text-sm font-medium text-gray-700">رقم البطاقة</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $customer->loyaltyCard->card_number }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700">النقاط الحالية</label>
                            <p class="mt-1 text-2xl font-bold text-green-600">{{ number_format($customer->loyaltyCard->points) }}</p>
                        </div>
                        
                        @php
                            $level = $customer->loyaltyCard->points >= 1000 ? 'Gold' : 
                                   ($customer->loyaltyCard->points >= 500 ? 'Silver' : 
                                   ($customer->loyaltyCard->points >= 100 ? 'Bronze' : 'New'));
                        @endphp
                        <div>
                            <label class="block text-sm font-medium text-gray-700">المستوى</label>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                                {{ $level === 'Gold' ? 'bg-yellow-100 text-yellow-800' : 
                                   ($level === 'Silver' ? 'bg-gray-100 text-gray-800' : 
                                   ($level === 'Bronze' ? 'bg-orange-100 text-orange-800' : 'bg-blue-100 text-blue-800')) }}">
                                {{ $level }}
                            </span>
                        </div>
                    @else
                        <div class="bg-red-50 border border-red-200 rounded-md p-4">
                            <p class="text-red-700 text-sm">العميل ليس لديه بطاقة ولاء</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Points Management Card -->
        @if($customer->loyaltyCard)
        <div class="lg:col-span-2">
            <div class="bg-white shadow-md rounded-lg p-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">إدارة النقاط</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <button onclick="openPointsModal()" 
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-3 rounded-lg text-center">
                        <div class="text-2xl mb-1">+</div>
                        <div class="text-sm">إضافة نقاط</div>
                    </button>
                    
                    <button onclick="openRedeemModal()" 
                            class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-3 rounded-lg text-center">
                        <div class="text-2xl mb-1">-</div>
                        <div class="text-sm">استبدال نقاط</div>
                    </button>
                    
                    <button onclick="openUpdateModal()" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded-lg text-center">
                        <div class="text-2xl mb-1">=</div>
                        <div class="text-sm">تحديث النقاط</div>
                    </button>
                </div>

                <!-- Send Notification -->
                <div class="border-t pt-6">
                    <h3 class="text-lg font-medium text-gray-800 mb-4">إرسال إشعار</h3>
                    <form method="POST" action="{{ route('admin.wallet-management.send-notification', $customer->id) }}" class="flex gap-4">
                        @csrf
                        <input type="text" name="message" placeholder="رسالة الإشعار..." 
                               class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-md">
                            إرسال
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Transactions History -->
    @if($customer->transactions->count() > 0)
    <div class="mt-8">
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-semibold text-gray-800">سجل المعاملات</h2>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                التاريخ
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                النوع
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                النقاط
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                الرصيد
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                الوصف
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($customer->transactions as $transaction)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $transaction->created_at->format('Y-m-d H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $typeColors = [
                                            'earn' => 'bg-green-100 text-green-800',
                                            'redeem' => 'bg-orange-100 text-orange-800',
                                            'manual_update' => 'bg-blue-100 text-blue-800'
                                        ];
                                        $typeLabels = [
                                            'earn' => 'إضافة',
                                            'redeem' => 'استبدال',
                                            'manual_update' => 'تحديث'
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $typeColors[$transaction->type] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ $typeLabels[$transaction->type] ?? $transaction->type }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <span class="{{ $transaction->points >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $transaction->points >= 0 ? '+' : '' }}{{ $transaction->points }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ number_format($transaction->balance) }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    {{ $transaction->description }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Add Points Modal -->
<div id="addPointsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">إضافة نقاط</h3>
            <form method="POST" action="{{ route('admin.wallet-management.add-points', $customer->id) }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">النقاط المراد إضافتها</label>
                    <input type="number" name="points" min="1" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">السبب</label>
                    <input type="text" name="reason" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex justify-end space-x-3 space-x-reverse">
                    <button type="button" onclick="closeModal('addPointsModal')" class="px-4 py-2 text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50">
                        إلغاء
                    </button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                        إضافة النقاط
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Redeem Points Modal -->
<div id="redeemPointsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">استبدال نقاط</h3>
            <form method="POST" action="{{ route('admin.wallet-management.redeem-points', $customer->id) }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">النقاط المراد استبدالها</label>
                    <input type="number" name="points" min="1" max="{{ $customer->loyaltyCard->points ?? 0 }}" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">السبب</label>
                    <input type="text" name="reason" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex justify-end space-x-3 space-x-reverse">
                    <button type="button" onclick="closeModal('redeemPointsModal')" class="px-4 py-2 text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50">
                        إلغاء
                    </button>
                    <button type="submit" class="px-4 py-2 bg-orange-600 text-white rounded-md hover:bg-orange-700">
                        استبدال النقاط
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Update Points Modal -->
<div id="updatePointsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">تحديث النقاط</h3>
            <form method="POST" action="{{ route('admin.wallet-management.update-points', $customer->id) }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">النقاط الجديدة</label>
                    <input type="number" name="points" min="0" value="{{ $customer->loyaltyCard->points ?? 0 }}" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">السبب</label>
                    <input type="text" name="reason" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex justify-end space-x-3 space-x-reverse">
                    <button type="button" onclick="closeModal('updatePointsModal')" class="px-4 py-2 text-gray-600 border border-gray-300 rounded-md hover:bg-gray-50">
                        إلغاء
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        تحديث النقاط
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openPointsModal() {
    document.getElementById('addPointsModal').classList.remove('hidden');
}

function openRedeemModal() {
    document.getElementById('redeemPointsModal').classList.remove('hidden');
}

function openUpdateModal() {
    document.getElementById('updatePointsModal').classList.remove('hidden');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}
</script>
@endsection 