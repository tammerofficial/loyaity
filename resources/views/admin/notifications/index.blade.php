@extends('layouts.admin')

@section('title', 'الإشعارات')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">📧 الإشعارات</h1>
            <div class="flex space-x-2">
                <button onclick="markAllAsRead()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors">
                    ✅ تحديد الكل كمقروء
                </button>
                <a href="{{ route('admin.dashboard') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors">
                    🔙 العودة للرئيسية
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
                                    
                                    @if(isset($data['customer_name']))
                                        <br><strong>العميل:</strong> {{ $data['customer_name'] }}
                                    @endif
                                    
                                    @if(isset($data['membership_number']))
                                        <br><strong>رقم العضوية:</strong> {{ $data['membership_number'] }}
                                    @endif
                                    
                                    @if(isset($data['design_changes']) && is_array($data['design_changes']))
                                        <br><strong>التحديثات:</strong>
                                        <ul class="list-disc list-inside mt-1">
                                            @foreach($data['design_changes'] as $change)
                                                <li>{{ $change }}</li>
                                            @endforeach
                                        </ul>
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
                <h3 class="text-xl font-semibold text-gray-600 mb-2">لا توجد إشعارات</h3>
                <p class="text-gray-500">ستظهر هنا جميع الإشعارات المرسلة للعملاء</p>
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

function markAllAsRead() {
    if (!confirm('هل تريد تحديد جميع الإشعارات كمقروءة؟')) {
        return;
    }
    
    fetch('/admin/notifications/mark-all-read', {
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