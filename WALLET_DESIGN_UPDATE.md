# تحديث تصميم البطاقة - اللون الأسود واسم العميل

## ✅ التغييرات المطبقة

### 1. تغيير لون خلفية البطاقة إلى الأسود

#### أ) تحديث الإعدادات الافتراضية
- **الملف**: `app/Models/WalletDesignSettings.php`
- **التغيير**: تحديث `getDefaultSettings()` method
- **الألوان الجديدة**:
  - `background_color`: `#000000` (أسود)
  - `background_color_secondary`: `#1a1a1a` (رمادي داكن)
  - `text_color`: `#ffffff` (أبيض)
  - `label_color`: `#ffffff` (أبيض)

#### ب) تحديث قاعدة البيانات
- **الملف**: `database/seeders/WalletDesignSeeder.php` (جديد)
- **الوظيفة**: تحديث الإعدادات الموجودة في قاعدة البيانات
- **التشغيل**: `php artisan db:seed --class=WalletDesignSeeder`

### 2. إضافة اسم العميل في البطاقة

#### أ) في AppleWalletService
- **الملف**: `app/Services/AppleWalletService.php`
- **الموقع**: موجود بالفعل في `primaryFields`
- **الكود**:
```php
'primaryFields' => [
    [
        'key' => 'name',
        'label' => 'Member',
        'value' => $customer->name
    ]
]
```

#### ب) في AdminCustomerController
- **الملف**: `app/Http/Controllers/Admin/AdminCustomerController.php`
- **التغيير**: إضافة حقل اسم العميل في `primaryFields`
- **الكود**:
```php
$pass->primaryFields = [
    new Field(
        key: 'name',
        value: $customer->name,
        label: 'Member'
    ),
    new Field(
        key: 'balance',
        value: number_format($customer->available_points),
        label: 'Available Points'
    )
];
```

### 3. التأكد من ظهور اسم العميل في جميع العروض

#### أ) صفحة معاينة البطاقة
- **الملف**: `resources/views/admin/customers/wallet-preview.blade.php`
- **الموقع**: موجود في السطر 570
- **الكود**: `<div class="customer-name-main" id="customerNameMain">{{ $customer->name }}</div>`

#### ب) صفحة QR Code
- **الملف**: `resources/views/admin/customers/wallet-qr.blade.php`
- **الموقع**: موجود في العنوان والسطر 194
- **الكود**: `<h2>👤 {{ $customer->name }}</h2>`

## 🎨 النتيجة النهائية

### البطاقة الجديدة ستظهر بـ:
1. **خلفية سوداء** (`#000000`)
2. **نص أبيض** (`#ffffff`)
3. **اسم العميل** في المقدمة
4. **نقاط العميل** المتاحة
5. **رقم العضوية**
6. **مستوى العميل** (Tier)

### الألوان المستخدمة:
- **الخلفية الرئيسية**: أسود (`#000000`)
- **الخلفية الثانوية**: رمادي داكن (`#1a1a1a`)
- **النص**: أبيض (`#ffffff`)
- **التسميات**: أبيض (`#ffffff`)

## 🔄 كيفية تطبيق التغييرات

### للبطاقات الجديدة:
- ستستخدم الإعدادات الجديدة تلقائياً

### للبطاقات الموجودة:
- يمكن تحديثها من خلال:
  1. إعادة إنشاء البطاقة
  2. أو تحديث التصميم من لوحة التحكم

## 📱 اختبار التصميم

يمكن اختبار التصميم الجديد من خلال:
1. الذهاب إلى صفحة العميل
2. الضغط على "🎨 Preview Design"
3. أو "📱 Show QR Code"
4. أو "Download Pass" لتحميل البطاقة الفعلية 