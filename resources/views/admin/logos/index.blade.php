@extends('layouts.admin')

@section('title', 'إدارة الشعارات')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white shadow rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">🎨 إدارة الشعارات</h1>
                        <p class="text-gray-600 mt-1">إدارة شعارات البطاقات مع المعاينة المباشرة</p>
                    </div>
                    <button onclick="openUploadModal()" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition-colors">
                        ➕ رفع شعار جديد
                    </button>
                </div>
            </div>
        </div>

        <!-- Current Active Logo Preview -->
        <div class="bg-white shadow rounded-lg mb-6" id="currentLogoPreview">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">🔥 الشعار النشط حالياً</h2>
            </div>
            <div class="p-6">
                <div id="activeLogoDisplay" class="flex items-center justify-center bg-gray-100 rounded-lg p-8 min-h-[200px]">
                    @if($logos->where('is_active', true)->first())
                        @php $activeLogo = $logos->where('is_active', true)->first(); @endphp
                        <div class="text-center">
                            <img src="{{ $activeLogo->url }}" alt="{{ $activeLogo->name }}" class="max-h-32 max-w-full mx-auto mb-3 rounded-lg shadow-sm">
                            <h3 class="text-lg font-medium text-gray-900">{{ $activeLogo->name }}</h3>
                            <p class="text-gray-500 text-sm">{{ $activeLogo->width }}x{{ $activeLogo->height }} px</p>
                        </div>
                    @else
                        <div class="text-center text-gray-500">
                            <svg class="w-16 h-16 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <p>لا يوجد شعار نشط حالياً</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Logos Grid -->
        <div class="bg-white shadow rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">📁 جميع الشعارات</h2>
            </div>
            <div class="p-6">
                @if($logos->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        @foreach($logos as $logo)
                            <div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow" id="logo-card-{{ $logo->id }}">
                                <!-- Logo Image -->
                                <div class="aspect-square bg-gray-50 rounded-lg mb-3 flex items-center justify-center overflow-hidden">
                                    <img src="{{ $logo->url }}" alt="{{ $logo->name }}" class="max-w-full max-h-full object-contain">
                                </div>
                                
                                <!-- Logo Info -->
                                <div class="space-y-2">
                                    <h3 class="font-medium text-gray-900 truncate">{{ $logo->name }}</h3>
                                    <p class="text-sm text-gray-500">{{ $logo->width }}x{{ $logo->height }} px</p>
                                    <p class="text-xs text-gray-400">{{ $logo->formatted_size }}</p>
                                    
                                    <!-- Status Badges -->
                                    <div class="flex flex-wrap gap-1">
                                        @if($logo->is_active)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                🔥 نشط
                                            </span>
                                        @endif
                                        @if($logo->is_default)
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                ⭐ افتراضي
                                            </span>
                                        @endif
                                    </div>
                                    
                                    <!-- Actions -->
                                    <div class="flex flex-wrap gap-2 pt-2">
                                        @if(!$logo->is_active)
                                            <button onclick="activateLogo({{ $logo->id }})" class="text-xs bg-green-100 hover:bg-green-200 text-green-800 px-2 py-1 rounded transition-colors">
                                                تفعيل
                                            </button>
                                        @endif
                                        
                                        @if(!$logo->is_default)
                                            <button onclick="makeDefault({{ $logo->id }})" class="text-xs bg-blue-100 hover:bg-blue-200 text-blue-800 px-2 py-1 rounded transition-colors">
                                                افتراضي
                                            </button>
                                        @endif
                                        
                                        <button onclick="editLogo({{ $logo->id }})" class="text-xs bg-yellow-100 hover:bg-yellow-200 text-yellow-800 px-2 py-1 rounded transition-colors">
                                            تعديل
                                        </button>
                                        
                                        @if(!$logo->is_active && !$logo->is_default)
                                            <button onclick="deleteLogo({{ $logo->id }})" class="text-xs bg-red-100 hover:bg-red-200 text-red-800 px-2 py-1 rounded transition-colors">
                                                حذف
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <!-- Pagination -->
                    <div class="mt-6">
                        {{ $logos->links() }}
                    </div>
                @else
                    <div class="text-center py-12">
                        <svg class="w-24 h-24 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">لا توجد شعارات</h3>
                        <p class="text-gray-500 mb-4">ابدأ برفع أول شعار لاستخدامه في البطاقات</p>
                        <button onclick="openUploadModal()" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded-md transition-colors">
                            ➕ رفع شعار جديد
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Upload Modal -->
<div id="uploadModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">📤 رفع شعار جديد</h3>
            </div>
            <form id="uploadForm" enctype="multipart/form-data" class="p-6">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">اسم الشعار</label>
                        <input type="text" name="name" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">ملف الشعار</label>
                        <input type="file" name="logo_file" accept="image/*" required class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="text-xs text-gray-500 mt-1">JPG, PNG, GIF, SVG, WebP - حد أقصى 2MB</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">وصف (اختياري)</label>
                        <textarea name="description" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                    
                    <div class="space-y-2">
                        <label class="flex items-center">
                            <input type="checkbox" name="is_active" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700">تفعيل هذا الشعار</span>
                        </label>
                        
                        <label class="flex items-center">
                            <input type="checkbox" name="is_default" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700">جعله الشعار الافتراضي</span>
                        </label>
                    </div>
                </div>
                
                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" onclick="closeUploadModal()" class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md transition-colors">
                        إلغاء
                    </button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md transition-colors">
                        رفع الشعار
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Status Messages -->
<div id="statusMessage" class="fixed top-4 right-4 z-50 hidden"></div>

<script>
function openUploadModal() {
    document.getElementById('uploadModal').classList.remove('hidden');
}

function closeUploadModal() {
    document.getElementById('uploadModal').classList.add('hidden');
    document.getElementById('uploadForm').reset();
}

function showMessage(message, type = 'success') {
    const messageDiv = document.getElementById('statusMessage');
    messageDiv.className = `fixed top-4 right-4 z-50 px-4 py-3 rounded-md shadow-md ${
        type === 'success' ? 'bg-green-100 border border-green-400 text-green-700' : 'bg-red-100 border border-red-400 text-red-700'
    }`;
    messageDiv.textContent = message;
    messageDiv.classList.remove('hidden');
    
    setTimeout(() => {
        messageDiv.classList.add('hidden');
    }, 5000);
}

function activateLogo(id) {
    fetch(`/admin/logos/${id}/activate`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage(data.message);
            location.reload();
        } else {
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        showMessage('حدث خطأ غير متوقع', 'error');
    });
}

function makeDefault(id) {
    fetch(`/admin/logos/${id}/make-default`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage(data.message);
            location.reload();
        } else {
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        showMessage('حدث خطأ غير متوقع', 'error');
    });
}

function deleteLogo(id) {
    if (confirm('هل أنت متأكد من حذف هذا الشعار؟')) {
        fetch(`/admin/logos/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showMessage(data.message);
                document.getElementById(`logo-card-${id}`).remove();
            } else {
                showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            showMessage('حدث خطأ غير متوقع', 'error');
        });
    }
}

// Handle form submission
document.getElementById('uploadForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch('{{ route("admin.logos.store") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showMessage(data.message);
            closeUploadModal();
            if (data.redirect) {
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 1000);
            }
        } else {
            showMessage(data.message, 'error');
        }
    })
    .catch(error => {
        showMessage('حدث خطأ أثناء رفع الشعار', 'error');
    });
});

// Close modal when clicking outside
document.getElementById('uploadModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeUploadModal();
    }
});
</script>
@endsection