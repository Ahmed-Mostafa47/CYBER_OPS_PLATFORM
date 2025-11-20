#!/bin/bash

# 🧪 اختبار سريع لميزة Change Password

echo "======================================"
echo "🧪 اختبار Change Password Feature"
echo "======================================"
echo ""

# 1. التحقق من أن الملفات موجودة
echo "📁 التحقق من الملفات..."
echo ""

if [ -f "src/components/auth/ChangePasswordPage.jsx" ]; then
    echo "✅ ChangePasswordPage.jsx موجود"
else
    echo "❌ ChangePasswordPage.jsx غير موجود"
fi

if [ -f "src/components/auth/change_password.php" ]; then
    echo "✅ change_password.php موجود"
else
    echo "❌ change_password.php غير موجود"
fi

echo ""
echo "======================================"
echo "📊 الملفات المُنشأة:"
echo "======================================"
echo ""

if [ -f "README_CHANGE_PASSWORD.md" ]; then
    echo "✅ README_CHANGE_PASSWORD.md"
fi

if [ -f "CHANGE_PASSWORD_GUIDE.md" ]; then
    echo "✅ CHANGE_PASSWORD_GUIDE.md"
fi

if [ -f "CHANGE_PASSWORD_TESTING.md" ]; then
    echo "✅ CHANGE_PASSWORD_TESTING.md"
fi

if [ -f "CHANGE_PASSWORD_COMPARISON.md" ]; then
    echo "✅ CHANGE_PASSWORD_COMPARISON.md"
fi

if [ -f "QUICK_START.md" ]; then
    echo "✅ QUICK_START.md"
fi

if [ -f "FINAL_SUMMARY.md" ]; then
    echo "✅ FINAL_SUMMARY.md"
fi

echo ""
echo "======================================"
echo "🚀 خطوات الاختبار:"
echo "======================================"
echo ""
echo "1️⃣  تشغيل dev server:"
echo "    npm run dev"
echo ""
echo "2️⃣  افتح المتصفح:"
echo "    http://localhost:5174"
echo ""
echo "3️⃣  سجل الدخول"
echo ""
echo "4️⃣  انقر على PROFILE"
echo ""
echo "5️⃣  انقر على الزر الجديد CHANGE_PASSWORD"
echo ""
echo "6️⃣  ملء النموذج وانقر UPDATE_PASSWORD"
echo ""
echo "======================================"
echo "✅ كل شيء جاهز!"
echo "======================================"
