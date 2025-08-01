@extends('layouts.admin')

@section('title', 'إدارة البطاقات عبر الجسر')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-4">إدارة البطاقات عبر الجسر</h1>
        <p class="text-gray-600">إدارة نقاط العملاء وتحديث بطاقات Apple Wallet</p>
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

    <!-- Bridge Status Card -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-blue-900">حالة الجسر</h3>
                <p class="text-blue-700">مراقبة حالة الاتصال بالجسر المركزي</p>
            </div>
            <div class="flex space-x-4">
                <a href="{{ route('admin.wallet-management.bridge-statistics') }}" 
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                    إحصائيات الجسر
                </a>
            </div>
        </div>
    </div>

    <!-- Customers Table -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-xl font-semibold text-gray-800">العملاء والبطاقات</h2>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            العميل
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            رقم البطاقة
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            النقاط
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            المستوى
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            آخر تحديث
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                            الإجراءات
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($customers as $customer)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10">
                                        <div class="h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center">
                                            <span class="text-white font-semibold">
                                                {{ strtoupper(substr($customer->name, 0, 1)) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="mr-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $customer->name }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $customer->email }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($customer->loyaltyCards->first())
                                    {{ $customer->loyaltyCards->first()->card_number }}
                                @else
                                    <span class="text-red-500">لا توجد بطاقة</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($customer->loyaltyCards->first())
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ number_format($customer->available_points) }} نقطة
                                    </span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($customer->loyaltyCards->first())
                                    @php
                                        $level = $customer->available_points >= 1000 ? 'Gold' : 
                                               ($customer->available_points >= 500 ? 'Silver' : 
                                               ($customer->available_points >= 100 ? 'Bronze' : 'New'));
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        {{ $level === 'Gold' ? 'bg-yellow-100 text-yellow-800' : 
                                           ($level === 'Silver' ? 'bg-gray-100 text-gray-800' : 
                                           ($level === 'Bronze' ? 'bg-orange-100 text-orange-800' : 'bg-blue-100 text-blue-800')) }}">
                                        {{ $level }}
                                    </span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($customer->loyaltyCards->first())
                                    {{ $customer->loyaltyCards->first()->updated_at->diffForHumans() }}
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                @if($customer->loyaltyCards->first())
                                    <div class="flex space-x-2 space-x-reverse">
                                        <a href="{{ route('admin.wallet-management.show', $customer->id) }}" 
                                           class="text-blue-600 hover:text-blue-900">
                                            عرض التفاصيل
                                        </a>
                                        <button onclick="openPointsModal({{ $customer->id }}, '{{ $customer->name }}', {{ $customer->available_points }})" 
                                                class="text-green-600 hover:text-green-900">
                                            إضافة نقاط
                                        </button>
                                        <button onclick="openRedeemModal({{ $customer->id }}, '{{ $customer->name }}', {{ $customer->available_points }})" 
                                                class="text-orange-600 hover:text-orange-900">
                                            استبدال نقاط
                                        </button>
                                    </div>
                                @else
                                    <span class="text-gray-400">لا توجد بطاقة</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                لا يوجد عملاء
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $customers->links() }}
        </div>
    </div>
</div>

<!-- Add Points Modal -->
<div id="addPointsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 mb-4">إضافة نقاط</h3>
            <form id="addPointsForm" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">العميل</label>
                    <input type="text" id="customerName" readonly class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">النقاط الحالية</label>
                    <input type="number" id="currentPoints" readonly class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50">
                </div>
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
            <form id="redeemPointsForm" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">العميل</label>
                    <input type="text" id="redeemCustomerName" readonly class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">النقاط الحالية</label>
                    <input type="number" id="redeemCurrentPoints" readonly class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">النقاط المراد استبدالها</label>
                    <input type="number" name="points" min="1" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
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

<script>
function openPointsModal(customerId, customerName, currentPoints) {
    document.getElementById('customerName').value = customerName;
    document.getElementById('currentPoints').value = currentPoints;
    document.getElementById('addPointsForm').action = `{{ route('admin.wallet-management.add-points', '') }}/${customerId}`;
    document.getElementById('addPointsModal').classList.remove('hidden');
}

function openRedeemModal(customerId, customerName, currentPoints) {
    document.getElementById('redeemCustomerName').value = customerName;
    document.getElementById('redeemCurrentPoints').value = currentPoints;
    document.getElementById('redeemPointsForm').action = `{{ route('admin.wallet-management.redeem-points', '') }}/${customerId}`;
    document.getElementById('redeemPointsModal').classList.remove('hidden');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.add('hidden');
}
</script>
@endsection 