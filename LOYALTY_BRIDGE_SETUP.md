# 🌉 دليل إعداد Loyalty Wallet Bridge

## 📋 نظرة عامة
نظام الـ Bridge يعمل كوسيط مركزي بين الداشبورد المحلي وبطاقات Apple Wallet
موجود على السيرفر: **http://192.168.8.143/**

## 🔧 خطوات الإعداد

### 1. رفع الملف على السيرفر

#### أ) رفع الملف
```bash
# رفع public/loyalty_wallet_bridge.php إلى السيرفر
# المسار: http://192.168.8.143/loyalty_wallet_bridge.php
```

#### ب) إعداد الصلاحيات
```bash
chmod 755 loyalty_wallet_bridge.php
chmod 666 loyalty_bridge.log # للكتابة في السجلات
```

### 2. إعداد الداشبورد المحلي

#### أ) تحديث ملف .env
```env
# إضافة هذه الأسطر لملف .env
WALLET_BRIDGE_URL=http://192.168.8.143/loyalty_wallet_bridge.php
WALLET_BRIDGE_SECRET=loyalty-bridge-secret-2024
```

### 3. اختبار النظام

#### أ) اختبار الملف المركزي
```bash
# فحص حالة النظام
curl http://192.168.8.143/loyalty_wallet_bridge.php/status

# يجب أن يرجع:
{
    "bridge_status": "active",
    "bridge_name": "Loyalty Wallet Bridge v2.0",
    "bridge_version": "2.0.0",
    "dashboard_connection": "connected",
    "timestamp": "2024-08-01 19:30:00",
    "server_ip": "192.168.8.143"
}
```

#### ب) اختبار الاتصال من الداشبورد
```bash
# من داخل مجلد المشروع
php artisan tinker

# اختبار الاتصال
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://192.168.8.143/loyalty_wallet_bridge.php/status');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
echo $response;
```

## 🔄 كيفية عمل النظام

### 1. تدفق البيانات
```
[الداشبورد المحلي: 192.168.8.143:8000] 
       ↓ (إرسال التحديثات)
[الملف المركزي: 192.168.8.143/loyalty_wallet_bridge.php] 
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
- `POST /update-pass` - تحديث بيانات البطاقة

## 🛠️ الملفات المطلوبة

### 1. loyalty_wallet_bridge.php
```php
// موجود في: public/loyalty_wallet_bridge.php
// يجب رفعه إلى: http://192.168.8.143/loyalty_wallet_bridge.php
```

### 2. إعدادات الداشبورد
```php
// config/loyalty.php
'bridge_url' => 'http://192.168.8.143/loyalty_wallet_bridge.php',
'bridge_secret' => 'loyalty-bridge-secret-2024',
```

## 🔐 الأمان

### 1. مفتاح الأمان
- المفتاح: `loyalty-bridge-secret-2024`
- يتم إرساله في header: `X-Bridge-Secret`
- يحمي جميع الطلبات الإدارية

### 2. التحقق من التوكن
- التحقق من صحة Apple Wallet authentication token
- التحقق من وجود البطاقة في قاعدة البيانات

### 3. السجلات
- ملف السجلات: `loyalty_bridge.log`
- تسجيل جميع الطلبات والاستجابات
- تسجيل الأخطاء والمشاكل

## 📊 المراقبة والإدارة

### 1. حالة النظام
```bash
curl http://192.168.8.143/loyalty_wallet_bridge.php/status
```

### 2. السجلات
```bash
# جلب آخر 100 سطر
curl http://192.168.8.143/loyalty_wallet_bridge.php/logs?lines=100 \
  -H "X-Bridge-Secret: loyalty-bridge-secret-2024"

# مسح السجلات
curl -X DELETE http://192.168.8.143/loyalty_wallet_bridge.php/logs \
  -H "X-Bridge-Secret: loyalty-bridge-secret-2024"
```

### 3. الإحصائيات
```bash
curl http://192.168.8.143/loyalty_wallet_bridge.php/statistics \
  -H "X-Bridge-Secret: loyalty-bridge-secret-2024"
```

### 4. اختبار الاتصال
```bash
curl http://192.168.8.143/loyalty_wallet_bridge.php/test-connection \
  -H "X-Bridge-Secret: loyalty-bridge-secret-2024"
```

## 🚀 التحسينات في v2.0

### 1. بنية محسنة
- اسم مميز: `Loyalty Wallet Bridge v2.0`
- سجلات مفصلة لكل طلب
- إدارة أفضل للأخطاء

### 2. أمان محسن
- مفتاح أمان فريد
- تحقق محسن من التوكن
- سجلات مفصلة

### 3. وظائف جديدة
- إحصائيات النظام
- اختبار الاتصال
- إعادة تشغيل الخدمة
- إدارة السجلات
- تحديث بيانات البطاقات

## 🔧 استكشاف الأخطاء

### 1. مشاكل الاتصال
```bash
# اختبار الاتصال بالداشبورد
curl http://192.168.8.143/loyalty_wallet_bridge.php/test-connection \
  -H "X-Bridge-Secret: loyalty-bridge-secret-2024"
```

### 2. مشاكل السجلات
```bash
# فحص السجلات
tail -f loyalty_bridge.log
```

### 3. مشاكل الأمان
```bash
# التحقق من مفتاح الأمان
curl -H "X-Bridge-Secret: wrong-key" \
  http://192.168.8.143/loyalty_wallet_bridge.php/status
```

## 📝 ملاحظات مهمة

1. **تأكد من صحة الرابط**: يجب أن يكون الداشبورد يعمل على `192.168.8.143:8000`
2. **مفتاح الأمان**: استخدم المفتاح `loyalty-bridge-secret-2024`
3. **الصلاحيات**: تأكد من صلاحيات الكتابة لملف السجلات
4. **النسخ الاحتياطية**: احتفظ بنسخة احتياطية من الإعدادات
5. **المراقبة**: راقب السجلات بانتظام للكشف عن المشاكل

## 🎯 الاستخدام في البطاقات

في ملف `pass.json` للبطاقة:
```json
{
  "webServiceURL": "http://192.168.8.143/loyalty_wallet_bridge.php",
  "authenticationToken": "your-auth-token"
}
```

## 📞 معلومات الاتصال

- **السيرفر**: http://192.168.8.143/
- **الجسر**: http://192.168.8.143/loyalty_wallet_bridge.php
- **الداشبورد**: http://192.168.8.143:8000
- **مفتاح الأمان**: loyalty-bridge-secret-2024
- **ملف السجلات**: loyalty_bridge.log 