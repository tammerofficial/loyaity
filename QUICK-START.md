# 🚀 Quick Start Guide - دليل البدء السريع

## للتثبيت السريع (أقل من 5 دقائق)

### 1️⃣ الطريقة السريعة - Composer Auto Install
```bash
composer install
```
سيقوم تلقائياً بتثبيت كل شيء!

### 2️⃣ التثبيت الكامل مع الاختبار
```bash
# تشغيل التثبيت الكامل
chmod +x install.sh
./install.sh

# اختبار التثبيت
php test-installation.php
```

### 3️⃣ تثبيت Cloudways مباشرة
```bash
chmod +x install-cloudways.sh
./install-cloudways.sh
```

### 4️⃣ تثبيت عبر المتصفح
رفع `quick-install.php` واذهب إلى `yoursite.com/quick-install.php`

---

## 🔧 إعدادات مطلوبة

### قاعدة البيانات
```env
DB_DATABASE=your_database_name
DB_USERNAME=your_username  
DB_PASSWORD=your_password
```

### Apple Wallet
```env
APPLE_WALLET_CERTIFICATE_PASSWORD=your_certificate_password
```

---

## ✅ فحص التثبيت

### اختبار سريع
```bash
curl http://yoursite.com/health
```

### اختبار شامل  
```bash
php test-installation.php
```

---

## 🔄 النشر التلقائي

```bash
# نشر تلقائي مع نسخ احتياطية
./auto-deploy.sh

# أو استخدام composer
composer deploy
```

---

## 🆘 حل المشاكل السريع

### خطأ 500
```bash
chmod -R 755 storage bootstrap/cache
php artisan optimize:clear
php artisan key:generate
```

### مشكلة قاعدة البيانات  
```bash
php artisan migrate:fresh --seed
```

### مشكلة Apple Wallet
- تأكد من وجود الشهادات في `certs/`
- تأكد من كلمة المرور في `.env`

---

## 📞 الدعم السريع

1. تشغيل `/health` للفحص
2. مراجعة `storage/logs/laravel.log`
3. تشغيل `php artisan about`

🎉 **انتهيت؟ موقعك جاهز للعمل!**