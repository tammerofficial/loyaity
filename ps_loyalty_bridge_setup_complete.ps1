# PowerShell Script: Loyalty Bridge Setup Complete
# تم إعداد الجسر على السيرفر 192.168.8.143 بنجاح

Write-Host "=== إعداد Loyalty Wallet Bridge - مكتمل ===" -ForegroundColor Green

# معلومات السيرفر:
Write-Host "`n🖥️ معلومات السيرفر:" -ForegroundColor Cyan
Write-Host "• السيرفر: http://192.168.8.143/" -ForegroundColor White
Write-Host "• الجسر: http://192.168.8.143/loyalty_wallet_bridge.php" -ForegroundColor White
Write-Host "• الداشبورد: http://192.168.8.143:8000" -ForegroundColor White

# الملفات المحدثة:
Write-Host "`n📝 الملفات المحدثة:" -ForegroundColor Yellow
Write-Host "• public/loyalty_wallet_bridge.php" -ForegroundColor White
Write-Host "• config/loyalty.php" -ForegroundColor White
Write-Host "• LOYALTY_BRIDGE_SETUP.md" -ForegroundColor White

# الإعدادات الجديدة:
Write-Host "`n⚙️ الإعدادات الجديدة:" -ForegroundColor Magenta
Write-Host "• اسم الجسر: Loyalty Wallet Bridge v2.0" -ForegroundColor White
Write-Host "• مفتاح الأمان: loyalty-bridge-secret-2024" -ForegroundColor White
Write-Host "• ملف السجلات: loyalty_bridge.log" -ForegroundColor White
Write-Host "• رابط الداشبورد: http://192.168.8.143:8000" -ForegroundColor White

# المسارات المتاحة:
Write-Host "`n🌐 المسارات المتاحة:" -ForegroundColor Blue
Write-Host "• GET /status" -ForegroundColor White
Write-Host "• GET /passes/{passTypeId}/{serialNumber}" -ForegroundColor White
Write-Host "• POST /devices/{deviceId}/registrations/{passTypeId}/{serialNumber}" -ForegroundColor White
Write-Host "• GET /devices/{deviceId}/registrations/{passTypeId}" -ForegroundColor White
Write-Host "• DELETE /devices/{deviceId}/registrations/{passTypeId}/{serialNumber}" -ForegroundColor White
Write-Host "• POST /log" -ForegroundColor White

# المسارات الإدارية:
Write-Host "`n🔐 المسارات الإدارية:" -ForegroundColor Red
Write-Host "• GET /logs" -ForegroundColor White
Write-Host "• DELETE /logs" -ForegroundColor White
Write-Host "• GET /test-connection" -ForegroundColor White
Write-Host "• GET /statistics" -ForegroundColor White
Write-Host "• POST /restart" -ForegroundColor White
Write-Host "• POST /push-notification" -ForegroundColor White
Write-Host "• POST /update-pass" -ForegroundColor White

# خطوات الرفع:
Write-Host "`n📤 خطوات الرفع:" -ForegroundColor Green
Write-Host "1. رفع public/loyalty_wallet_bridge.php إلى السيرفر" -ForegroundColor White
Write-Host "2. المسار: http://192.168.8.143/loyalty_wallet_bridge.php" -ForegroundColor White
Write-Host "3. إعداد الصلاحيات: chmod 755 loyalty_wallet_bridge.php" -ForegroundColor White
Write-Host "4. إنشاء ملف السجلات: touch loyalty_bridge.log" -ForegroundColor White
Write-Host "5. إعداد صلاحيات السجلات: chmod 666 loyalty_bridge.log" -ForegroundColor White

# متغيرات البيئة:
Write-Host "`n🔧 متغيرات البيئة:" -ForegroundColor Cyan
Write-Host "WALLET_BRIDGE_URL=http://192.168.8.143/loyalty_wallet_bridge.php" -ForegroundColor White
Write-Host "WALLET_BRIDGE_SECRET=loyalty-bridge-secret-2024" -ForegroundColor White

# اختبار النظام:
Write-Host "`n🧪 اختبار النظام:" -ForegroundColor Green
Write-Host "• curl http://192.168.8.143/loyalty_wallet_bridge.php/status" -ForegroundColor White
Write-Host "• curl -H 'X-Bridge-Secret: loyalty-bridge-secret-2024' http://192.168.8.143/loyalty_wallet_bridge.php/statistics" -ForegroundColor White
Write-Host "• curl -H 'X-Bridge-Secret: loyalty-bridge-secret-2024' http://192.168.8.143/loyalty_wallet_bridge.php/logs" -ForegroundColor White

# الاستخدام في البطاقات:
Write-Host "`n🎯 الاستخدام في البطاقات:" -ForegroundColor Yellow
Write-Host "في ملف pass.json:" -ForegroundColor White
Write-Host '{' -ForegroundColor Gray
Write-Host '  "webServiceURL": "http://192.168.8.143/loyalty_wallet_bridge.php",' -ForegroundColor Gray
Write-Host '  "authenticationToken": "your-auth-token"' -ForegroundColor Gray
Write-Host '}' -ForegroundColor Gray

# الأوامر المفيدة:
Write-Host "`n⚡ أوامر مفيدة:" -ForegroundColor Yellow
Write-Host "• فحص حالة الجسر: curl http://192.168.8.143/loyalty_wallet_bridge.php/status" -ForegroundColor Gray
Write-Host "• فحص السجلات: tail -f loyalty_bridge.log" -ForegroundColor Gray
Write-Host "• اختبار الاتصال: curl -H 'X-Bridge-Secret: loyalty-bridge-secret-2024' http://192.168.8.143/loyalty_wallet_bridge.php/test-connection" -ForegroundColor Gray
Write-Host "• مسح السجلات: curl -X DELETE -H 'X-Bridge-Secret: loyalty-bridge-secret-2024' http://192.168.8.143/loyalty_wallet_bridge.php/logs" -ForegroundColor Gray

# نصائح مهمة:
Write-Host "`n💡 نصائح مهمة:" -ForegroundColor Green
Write-Host "• تأكد من أن الداشبورد يعمل على 192.168.8.143:8000" -ForegroundColor White
Write-Host "• استخدم مفتاح الأمان الصحيح: loyalty-bridge-secret-2024" -ForegroundColor White
Write-Host "• راقب ملف السجلات بانتظام" -ForegroundColor White
Write-Host "• اختبر الاتصال قبل الاستخدام" -ForegroundColor White
Write-Host "• احتفظ بنسخة احتياطية من الملف" -ForegroundColor White

# معلومات إضافية:
Write-Host "`n📝 معلومات إضافية:" -ForegroundColor Cyan
Write-Host "• تم تحديث إعدادات التكوين" -ForegroundColor White
Write-Host "• تم إنشاء دليل إعداد شامل" -ForegroundColor White
Write-Host "• تم إضافة سجلات مفصلة" -ForegroundColor White
Write-Host "• تم تحسين الأمان" -ForegroundColor White

Write-Host "`n✅ تم إعداد Loyalty Wallet Bridge بنجاح!" -ForegroundColor Green
Write-Host "🎉 الجسر جاهز للاستخدام على السيرفر 192.168.8.143" -ForegroundColor Green

# معلومات الاتصال النهائية:
Write-Host "`n📞 معلومات الاتصال النهائية:" -ForegroundColor Blue
Write-Host "• الجسر: http://192.168.8.143/loyalty_wallet_bridge.php" -ForegroundColor White
Write-Host "• الداشبورد: http://192.168.8.143:8000" -ForegroundColor White
Write-Host "• مفتاح الأمان: loyalty-bridge-secret-2024" -ForegroundColor White
Write-Host "• ملف السجلات: loyalty_bridge.log" -ForegroundColor White
Write-Host "• الدليل: LOYALTY_BRIDGE_SETUP.md" -ForegroundColor White

Write-Host "`n=== انتهى ===`n" -ForegroundColor Green 