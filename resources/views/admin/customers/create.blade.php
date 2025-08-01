@extends('layouts.admin')

@section('title', 'إضافة عميل جديد')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">إضافة عميل جديد</h1>
                <p class="text-gray-600">إنشاء حساب عميل جديد مع بطاقة ولاء</p>
            </div>
            <a href="{{ route('admin.customers.index') }}" 
               class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                العودة للقائمة
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white shadow-md rounded-lg p-6">
        <form method="POST" action="{{ route('admin.customers.store') }}" class="space-y-6">
            @csrf
            
            <!-- Customer Information -->
            <div>
                <h2 class="text-xl font-semibold text-gray-800 mb-4">معلومات العميل</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            الاسم الكامل <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               value="{{ old('name') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               required>
                    </div>
                    
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                            البريد الإلكتروني <span class="text-red-500">*</span>
                        </label>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               value="{{ old('email') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                               required>
                    </div>
                    
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                            رقم الهاتف
                        </label>
                        <input type="tel" 
                               id="phone" 
                               name="phone" 
                               value="{{ old('phone') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div>
                        <label for="birth_date" class="block text-sm font-medium text-gray-700 mb-2">
                            تاريخ الميلاد
                        </label>
                        <input type="date" 
                               id="birth_date" 
                               name="birth_date" 
                               value="{{ old('birth_date') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>

            <!-- Loyalty Card Information -->
            <div class="border-t pt-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">بطاقة الولاء</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="card_number" class="block text-sm font-medium text-gray-700 mb-2">
                            رقم البطاقة
                        </label>
                        <input type="text" 
                               id="card_number" 
                               name="card_number" 
                               value="{{ old('card_number') }}"
                               placeholder="سيتم إنشاؤه تلقائياً"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 bg-gray-50"
                               readonly>
                        <p class="text-sm text-gray-500 mt-1">سيتم إنشاء رقم البطاقة تلقائياً</p>
                    </div>
                    
                    <div>
                        <label for="initial_points" class="block text-sm font-medium text-gray-700 mb-2">
                            النقاط الأولية
                        </label>
                        <input type="number" 
                               id="initial_points" 
                               name="initial_points" 
                               value="{{ old('initial_points', 0) }}"
                               min="0"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
            </div>

            <!-- Additional Information -->
            <div class="border-t pt-6">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">معلومات إضافية</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                            العنوان
                        </label>
                        <textarea id="address" 
                                  name="address" 
                                  rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('address') }}</textarea>
                    </div>
                    
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                            ملاحظات
                        </label>
                        <textarea id="notes" 
                                  name="notes" 
                                  rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="border-t pt-6">
                <div class="flex justify-end space-x-4 space-x-reverse">
                    <a href="{{ route('admin.customers.index') }}" 
                       class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        إلغاء
                    </a>
                    <button type="submit" 
                            class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        إنشاء العميل
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
// Generate card number automatically
document.addEventListener('DOMContentLoaded', function() {
    const cardNumberInput = document.getElementById('card_number');
    
    if (!cardNumberInput.value) {
        // Generate a random 8-digit card number
        const cardNumber = 'CARD' + Math.random().toString().substr(2, 8);
        cardNumberInput.value = cardNumber;
    }
});
</script>
@endsection 