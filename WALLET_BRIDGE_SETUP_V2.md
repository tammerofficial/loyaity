# 🌉 دليل إعداد Wallet Bridge System v2.0

## 📋 نظرة عامة
نظام الـ Bridge المحسن يعمل كحلقة وصل بين الداشبورد المحلي وبطاقات Apple Wallet على السيرفر الخارجي.

## 🔧 خطوات الإعداد

### 1. إعداد الملف المركزي على السيرفر الخارجي

#### أ) رفع الملف
```bash
# رفع public/wallet_bridge.php إلى السيرفر الخارجي
# مثال: https://your-server.com/wallet_bridge.php
```

#### ب) تعديل الإعدادات في wallet_bridge.php
```php
// ===== CONFIGURATION =====
$DASHBOARD_URL = 'http://192.168.8.143:8000'; // رابط الداشبورد المحلي
$BRIDGE_SECRET = 'your-unique-secret-key-2024'; // مفتاح أمان فريد
$LOG_FILE = 'wallet_bridge.log'; // ملف السجلات
```

#### ج) إعداد الصلاحيات
```bash
chmod 755 wallet_bridge.php
chmod 666 wallet_bridge.log # للكتابة في السجلات
```

### 2. إعداد الداشبورد المحلي

#### أ) تحديث ملف .env
```env
# إضافة هذه الأسطر لملف .env
WALLET_BRIDGE_URL=https://your-server.com/wallet_bridge.php
WALLET_BRIDGE_SECRET=your-unique-secret-key-2024
```

#### ب) تحديث config/loyalty.php
```php
'bridge_url' => env('WALLET_BRIDGE_URL', 'https://your-server.com/wallet_bridge.php'),
'bridge_secret' => env('WALLET_BRIDGE_SECRET', 'your-secret-key-here'),
```

### 3. اختبار النظام

#### أ) اختبار الملف المركزي
```bash
# فحص حالة النظام
curl https://your-server.com/wallet_bridge.php/status

# يجب أن يرجع:
{
    "bridge_status": "active",
    "bridge_version": "2.0.0",
    "dashboard_connection": "connected",
    "timestamp": "2024-08-01 19:30:00"
}
```

#### ب) اختبار الاتصال من الداشبورد
```bash
# من داخل مجلد المشروع
php artisan tinker

# اختبار الاتصال
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://your-server.com/wallet_bridge.php/status');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
echo $response;
```

## 🔄 كيفية عمل النظام

### 1. تدفق البيانات
```
[الداشبورد المحلي] 
       ↓ (إرسال التحديثات)
[الملف المركزي على السيرفر] 
       ↓ (توزيع على البطاقات)
[بطاقات Apple Wallet]
```

### 2. المسارات المتاحة في Bridge

#### للبطاقات (Apple Wallet Web Service):
- `GET /passes/{passTypeId}/{serialNumber}` - جلب بيانات البطاقة
- `POST /devices/{deviceId}/registrations/{passTypeId}/{serialNumber}` - تسجيل جهاز
- `GET /devices/{deviceId}/registrations/{passTypeId}` - جلب التحديثات
- `DELETE /devices/{deviceId}/registrations/{passTypeId}/{serialNumber}` - إلغاء تسجيل
- `POST /log` - سجلات Apple Wallet

#### للإدارة (تتطلب مفتاح الأمان):
- `GET /status` - حالة النظام
- `GET /logs` - جلب السجلات
- `DELETE /logs` - مسح السجلات
- `GET /test-connection` - اختبار الاتصال
- `GET /statistics` - إحصائيات النظام
- `POST /restart` - إعادة تشغيل الخدمة
- `POST /push-notification` - إرسال إشعار push

## 🛠️ الملفات الجديدة

### 1. WalletBridgeService.php
```php
// app/Services/WalletBridgeService.php
// خدمة الوسيط الرئيسية مع جميع الوظائف
```

### 2. WalletBridgeController.php
```php
// app/Http/Controllers/API/WalletBridgeController.php
// Controller للتعامل مع طلبات الوسيط
```

### 3. BridgeAuthMiddleware.php
```php
// app/Http/Middleware/BridgeAuthMiddleware.php
// Middleware للتحقق من مفتاح الأمان
```

### 4. wallet_bridge.php
```php
// public/wallet_bridge.php
// الملف الخارجي للرفع على السيرفر الخارجي
```

## 🔐 الأمان

### 1. مفتاح الأمان
- يجب استخدام مفتاح أمان فريد وقوي
- يتم إرساله في header: `X-Bridge-Secret`
- يحمي جميع الطلبات الإدارية

### 2. التحقق من التوكن
- التحقق من صحة Apple Wallet authentication token
- التحقق من وجود البطاقة في قاعدة البيانات

### 3. السجلات
- تسجيل جميع الطلبات والاستجابات
- تسجيل الأخطاء والمشاكل
- إمكانية مسح السجلات

## 📊 المراقبة والإدارة

### 1. حالة النظام
```bash
curl https://your-server.com/wallet_bridge.php/status
```

### 2. السجلات
```bash
# جلب آخر 100 سطر
curl https://your-server.com/wallet_bridge.php/logs?lines=100

# مسح السجلات
curl -X DELETE https://your-server.com/wallet_bridge.php/logs \
  -H "X-Bridge-Secret: your-secret-key"
```

### 3. الإحصائيات
```bash
curl https://your-server.com/wallet_bridge.php/statistics \
  -H "X-Bridge-Secret: your-secret-key"
```

## 🚀 التحسينات في v2.0

### 1. بنية محسنة
- فصل الخدمات والـ Controllers
- استخدام Laravel HTTP Client
- إدارة أفضل للأخطاء

### 2. أمان محسن
- Middleware للتحقق من مفتاح الأمان
- تحقق محسن من التوكن
- سجلات مفصلة

### 3. وظائف جديدة
- إحصائيات النظام
- اختبار الاتصال
- إعادة تشغيل الخدمة
- إدارة السجلات

### 4. توثيق محسن
- دليل إعداد شامل
- أمثلة عملية
- استكشاف الأخطاء

## 🔧 استكشاف الأخطاء

### 1. مشاكل الاتصال
```bash
# اختبار الاتصال بالداشبورد
curl https://your-server.com/wallet_bridge.php/test-connection \
  -H "X-Bridge-Secret: your-secret-key"
```

### 2. مشاكل السجلات
```bash
# فحص السجلات
tail -f wallet_bridge.log
```

### 3. مشاكل الأمان
```bash
# التحقق من مفتاح الأمان
curl -H "X-Bridge-Secret: wrong-key" \
  https://your-server.com/wallet_bridge.php/status
```

## 📝 ملاحظات مهمة

1. **تأكد من صحة الرابط**: يجب أن يكون `$DASHBOARD_URL` صحيحاً وقابلاً للوصول
2. **مفتاح الأمان**: استخدم مفتاح أمان فريد وقوي
3. **الصلاحيات**: تأكد من صلاحيات الكتابة لملف السجلات
4. **النسخ الاحتياطية**: احتفظ بنسخة احتياطية من الإعدادات
5. **المراقبة**: راقب السجلات بانتظام للكشف عن المشاكل

## 🎯 الاستخدام في البطاقات

في ملف `pass.json` للبطاقة:
```json
{
  "webServiceURL": "https://your-server.com/wallet_bridge.php",
  "authenticationToken": "your-auth-token"
}
``` 