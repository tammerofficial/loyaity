#!/bin/bash

# سكريبت إعداد Loyalty Wallet Bridge على السيرفر
# استخدم: ./setup_bridge_on_server.sh

echo "=== إعداد Loyalty Wallet Bridge على السيرفر ==="

# معلومات السيرفر
SERVER_USER="alalawi310"
SERVER_IP="192.168.8.143"
BRIDGE_FILE="loyalty_wallet_bridge.php"
LOG_FILE="loyalty_bridge.log"
TARGET_PATH="/var/www/html/applecards"

echo "🔄 رفع الملف على السيرفر..."
scp public/$BRIDGE_FILE $SERVER_USER@$SERVER_IP:/home/$SERVER_USER/

if [ $? -eq 0 ]; then
    echo "✅ تم رفع الملف بنجاح"
else
    echo "❌ فشل في رفع الملف"
    exit 1
fi

echo "🔄 إعداد الملف على السيرفر..."

# إنشاء سكريبت إعداد على السيرفر
cat > setup_bridge_remote.sh << 'EOF'
#!/bin/bash

BRIDGE_FILE="loyalty_wallet_bridge.php"
LOG_FILE="loyalty_bridge.log"
TARGET_PATH="/var/www/html/applecards"

echo "=== إعداد الجسر على السيرفر ==="

# التحقق من وجود الملف
if [ ! -f "/home/alalawi310/$BRIDGE_FILE" ]; then
    echo "❌ الملف غير موجود"
    exit 1
fi

echo "✅ الملف موجود"

# التحقق من وجود مجلد الهدف
if [ ! -d "$TARGET_PATH" ]; then
    echo "🔄 إنشاء مجلد $TARGET_PATH..."
    sudo mkdir -p $TARGET_PATH
    sudo chown alalawi310:alalawi310 $TARGET_PATH
    sudo chmod 755 $TARGET_PATH
fi

# نقل الملف إلى المجلد المطلوب
echo "🔄 نقل الملف إلى $TARGET_PATH..."

# محاولة بدون sudo أولاً
if mv /home/alalawi310/$BRIDGE_FILE $TARGET_PATH/ 2>/dev/null; then
    echo "✅ تم نقل الملف بنجاح"
    BRIDGE_PATH="$TARGET_PATH/$BRIDGE_FILE"
else
    echo "🔄 محاولة مع sudo..."
    if sudo mv /home/alalawi310/$BRIDGE_FILE $TARGET_PATH/; then
        echo "✅ تم نقل الملف بنجاح مع sudo"
        sudo chown alalawi310:alalawi310 $TARGET_PATH/$BRIDGE_FILE
        BRIDGE_PATH="$TARGET_PATH/$BRIDGE_FILE"
    else
        echo "❌ فشل في نقل الملف"
        echo "⚠️  سيتم استخدام المجلد المنزلي"
        BRIDGE_PATH="/home/alalawi310/$BRIDGE_FILE"
    fi
fi

# إنشاء ملف السجلات
echo "🔄 إنشاء ملف السجلات..."
touch /home/alalawi310/$LOG_FILE
chmod 666 /home/alalawi310/$LOG_FILE

# إعداد الصلاحيات
echo "🔄 إعداد الصلاحيات..."
chmod 755 $BRIDGE_PATH

echo "✅ تم إعداد الجسر بنجاح"
echo "📍 مسار الجسر: $BRIDGE_PATH"
echo "📍 مسار السجلات: /home/alalawi310/$LOG_FILE"

# اختبار الجسر
echo "🧪 اختبار الجسر..."
if curl -s "http://192.168.8.143/applecards/$BRIDGE_FILE/status" > /dev/null; then
    echo "✅ الجسر يعمل بشكل صحيح"
else
    echo "⚠️  الجسر قد يحتاج إلى إعداد إضافي"
fi

echo "=== انتهى الإعداد ==="
EOF

# رفع سكريبت الإعداد
scp setup_bridge_remote.sh $SERVER_USER@$SERVER_IP:/home/$SERVER_USER/

if [ $? -eq 0 ]; then
    echo "✅ تم رفع سكريبت الإعداد"
else
    echo "❌ فشل في رفع سكريبت الإعداد"
    exit 1
fi

# تنفيذ سكريبت الإعداد على السيرفر
echo "🔄 تنفيذ سكريبت الإعداد على السيرفر..."
ssh $SERVER_USER@$SERVER_IP "chmod +x /home/$SERVER_USER/setup_bridge_remote.sh && /home/$SERVER_USER/setup_bridge_remote.sh"

# تنظيف الملفات المؤقتة
rm -f setup_bridge_remote.sh

echo "✅ تم إعداد الجسر بنجاح!"
echo ""
echo "📋 معلومات الجسر:"
echo "• السيرفر: http://192.168.8.143/"
echo "• الجسر: http://192.168.8.143/applecards/loyalty_wallet_bridge.php"
echo "• مفتاح الأمان: loyalty-bridge-secret-2024"
echo ""
echo "🧪 اختبار الجسر:"
echo "curl http://192.168.8.143/applecards/loyalty_wallet_bridge.php/status" 