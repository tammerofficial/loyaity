@extends('layouts.admin')

@section('title', 'إشعارات العميل')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">📧 إشعارات العميل</h1>
                <p class="text-gray-600 mt-1">{{ $customer->name }} - {{ $customer->membership_number }}</p>
            </div>
            <div class="flex space-x-2">
                <button onclick="markAllAsRead({{ $customer->id }})" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
                    ✅ تحديد الكل كمقروء
                </button>
                <a href="{{ route('admin.customers.edit', $customer) }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                    🔙 العودة للعميل
                </a>
            </div>
        </div>

        @if($notifications->count() > 0)
            <div class="space-y-4">
                @foreach($notifications as $notification)
                    <div class="border rounded-lg p-4 {{ $notification->read_at ? 'bg-gray-50' : 'bg-blue-50 border-blue-200' }}">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 mb-2">
                                    @if($notification->type === 'App\Notifications\WalletPassCreatedNotification')
                                        <span class="text-green-600">🎉</span>
                                        <span class="font-semibold text-gray-800">إنشاء بطاقة جديدة</span>
                                    @elseif($notification->type === 'App\Notifications\WalletDesignUpdatedNotification')
                                        <span class="text-blue-600">🎨</span>
                                        <span class="font-semibold text-gray-800">تحديث التصميم</span>
                                    @else
                                        <span class="text-gray-600">📢</span>
                                        <span class="font-semibold text-gray-800">إشعار عام</span>
                                    @endif
                                    
                                    @if(!$notification->read_at)
                                        <span class="bg-red-500 text-white text-xs px-2 py-1 rounded-full">جديد</span>
                                    @endif
                                </div>
                                
                                <div class="text-gray-600 mb-2">
                                    @php
                                        $data = json_decode($notification->data, true);
                                    @endphp
                                    
                                    @if(isset($data['message']))
                                        {{ $data['message'] }}
                                    @endif
                                    
                                    @if(isset($data['design_changes']) && is_array($data['design_changes']))
                                        <br><strong>التحديثات:</strong>
                                        <ul class="list-disc list-inside mt-1">
                                            @foreach($data['design_changes'] as $change)
                                                <li>{{ $change }}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                    
                                    @if(isset($data['pass_url']))
                                        <br><a href="{{ $data['pass_url'] }}" target="_blank" class="text-blue-600 hover:text-blue-800 underline">
                                            📱 عرض QR Code للبطاقة
                                        </a>
                                    @endif
                                </div>
                                
                                <div class="text-sm text-gray-500">
                                    {{ \Carbon\Carbon::parse($notification->created_at)->diffForHumans() }}
                                </div>
                            </div>
                            
                            <div class="flex space-x-2">
                                @if(!$notification->read_at)
                                    <button onclick="markAsRead('{{ $notification->id }}')" class="text-blue-600 hover:text-blue-800 text-sm">
                                        ✅ تحديد كمقروء
                                    </button>
                                @endif
                                <button onclick="deleteNotification('{{ $notification->id }}')" class="text-red-600 hover:text-red-800 text-sm">
                                    🗑️ حذف
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <div class="mt-6">
                {{ $notifications->links() }}
            </div>
        @else
            <div class="text-center py-8">
                <div class="text-6xl mb-4">📭</div>
                <h3 class="text-xl font-semibold text-gray-600 mb-2">لا توجد إشعارات لهذا العميل</h3>
                <p class="text-gray-500">ستظهر هنا جميع الإشعارات المرسلة لهذا العميل</p>
            </div>
        @endif
    </div>
</div>

<script>
function markAsRead(notificationId) {
    fetch(`/admin/notifications/${notificationId}/mark-read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}

function markAllAsRead(customerId) {
    if (!confirm('هل تريد تحديد جميع إشعارات هذا العميل كمقروءة؟')) {
        return;
    }
    
    fetch('/admin/notifications/mark-all-read', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            customer_id: customerId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}

function deleteNotification(notificationId) {
    if (!confirm('هل تريد حذف هذا الإشعار؟')) {
        return;
    }
    
    fetch(`/admin/notifications/${notificationId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}
</script>
@endsection 