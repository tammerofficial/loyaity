@extends('layouts.admin')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold">Edit Customer: {{ $customer->name }}</h1>
                    <div class="flex space-x-2">
                        <a href="{{ route('admin.notifications.customer', $customer) }}" class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 flex items-center gap-2">
                            📧 الإشعارات
                        </a>
                        <button type="button" onclick="forceUpdateWallet()" class="px-4 py-2 bg-orange-500 text-white rounded-md hover:bg-orange-600 flex items-center gap-2">
                            🔄 تحديث البطاقة
                        </button>
                    </div>
                </div>

                <form action="{{ route('admin.customers.update', $customer) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                            <input type="text" name="name" id="name" value="{{ $customer->name }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" readonly>
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" name="email" id="email" value="{{ $customer->email }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" readonly>
                        </div>
                        <div>
                            <label for="available_points" class="block text-sm font-medium text-gray-700">Available Points</label>
                            <input type="text" name="available_points" id="available_points" value="{{ number_format($customer->available_points) }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" readonly>
                        </div>
                        <div>
                            <label for="tier" class="block text-sm font-medium text-gray-700">Tier</label>
                            <input type="text" name="tier" id="tier" value="{{ $customer->tier }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" readonly>
                        </div>
                    </div>

                    <div class="mt-8">
                        <h2 class="text-xl font-semibold mb-4">Adjust Points</h2>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label for="points_to_add" class="block text-sm font-medium text-gray-700">Add Points</label>
                                <input type="number" name="points_to_add" id="points_to_add" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="points_to_redeem" class="block text-sm font-medium text-gray-700">Redeem Points</label>
                                <input type="number" name="points_to_redeem" id="points_to_redeem" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700">Description (Optional)</label>
                                <input type="text" name="description" id="description" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            </div>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end">
                        <a href="{{ route('admin.customers.index') }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 mr-2">Cancel</a>
                        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">Update Customer</button>
                    </div>
                </form>

                        <div class="mt-12">
            <h2 class="text-xl font-semibold mb-4">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" style="display: inline-block; vertical-align: middle; margin-right: 8px;">
                    <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z" fill="#333"/>
                </svg>
                Apple Wallet
            </h2>
            <p class="text-gray-600 mb-4">Add this customer's loyalty card to Apple Wallet on iPhone.</p>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <a href="{{ route('admin.customers.wallet-preview', $customer) }}" 
                   class="px-4 py-3 bg-purple-600 text-white rounded-md hover:bg-purple-700 flex items-center justify-center gap-2 transition-colors">
                    🎨 Preview Design
                </a>
                <a href="{{ route('admin.customers.wallet-qr', $customer) }}" 
                   class="px-4 py-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 flex items-center justify-center gap-2 transition-colors">
                    📱 Show QR Code
                </a>
                <a href="{{ route('admin.customers.wallet-pass', $customer) }}" 
                   class="px-4 py-3 bg-black text-white rounded-md hover:bg-gray-800 flex items-center justify-center gap-2 transition-colors">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="white" style="margin-right: 4px;">
                        <path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.81-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11z"/>
                    </svg>
                    Download Pass
                </a>
            </div>
        </div>
            </div> 
        </div>
    </div>
</div>

<script>
function forceUpdateWallet() {
    if (!confirm('هل تريد تحديث البطاقة مع التصميم والبيانات الجديدة؟')) {
        return;
    }
    
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '⏳ جاري التحديث...';
    button.disabled = true;
    
    fetch(`/admin/customers/{{ $customer->id }}/force-update-wallet`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(`✅ تم تحديث البطاقة بنجاح!\n\nتم إشعار ${data.devices_notified} جهاز\nالنقاط الجديدة: ${data.customer.points}\nالمستوى: ${data.customer.tier}`);
        } else {
            alert('❌ فشل في تحديث البطاقة: ' + (data.error || 'خطأ غير معروف'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('❌ فشل في تحديث البطاقة: ' + error.message);
    })
    .finally(() => {
        button.innerHTML = originalText;
        button.disabled = false;
    });
}
</script>
@endsection
