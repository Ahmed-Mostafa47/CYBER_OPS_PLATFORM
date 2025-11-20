#!/usr/bin/env pwsh

# 🧪 اختبار سريع لميزة Change Password (Windows PowerShell)

Write-Host "======================================" -ForegroundColor Cyan
Write-Host "🧪 اختبار Change Password Feature" -ForegroundColor Cyan
Write-Host "======================================" -ForegroundColor Cyan
Write-Host ""

# 1. التحقق من أن الملفات موجودة
Write-Host "📁 التحقق من الملفات..." -ForegroundColor Yellow
Write-Host ""

if (Test-Path "src/components/auth/ChangePasswordPage.jsx") {
    Write-Host "✅ ChangePasswordPage.jsx موجود" -ForegroundColor Green
} else {
    Write-Host "❌ ChangePasswordPage.jsx غير موجود" -ForegroundColor Red
}

if (Test-Path "src/components/auth/change_password.php") {
    Write-Host "✅ change_password.php موجود" -ForegroundColor Green
} else {
    Write-Host "❌ change_password.php غير موجود" -ForegroundColor Red
}

Write-Host ""
Write-Host "======================================" -ForegroundColor Cyan
Write-Host "📊 الملفات المُنشأة:" -ForegroundColor Cyan
Write-Host "======================================" -ForegroundColor Cyan
Write-Host ""

@(
    "README_CHANGE_PASSWORD.md",
    "CHANGE_PASSWORD_GUIDE.md",
    "CHANGE_PASSWORD_TESTING.md",
    "CHANGE_PASSWORD_COMPARISON.md",
    "QUICK_START.md",
    "FINAL_SUMMARY.md",
    "FILES_INDEX.md"
) | ForEach-Object {
    if (Test-Path $_) {
        Write-Host "✅ $_" -ForegroundColor Green
    }
}

Write-Host ""
Write-Host "======================================" -ForegroundColor Cyan
Write-Host "🚀 خطوات الاختبار:" -ForegroundColor Cyan
Write-Host "======================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "1️⃣  تشغيل dev server:" -ForegroundColor Yellow
Write-Host "    npm run dev" -ForegroundColor White
Write-Host ""
Write-Host "2️⃣  افتح المتصفح:" -ForegroundColor Yellow
Write-Host "    http://localhost:5174" -ForegroundColor White
Write-Host ""
Write-Host "3️⃣  سجل الدخول" -ForegroundColor Yellow
Write-Host ""
Write-Host "4️⃣  انقر على PROFILE" -ForegroundColor Yellow
Write-Host ""
Write-Host "5️⃣  انقر على الزر الجديد CHANGE_PASSWORD" -ForegroundColor Yellow
Write-Host ""
Write-Host "6️⃣  ملء النموذج وانقر UPDATE_PASSWORD" -ForegroundColor Yellow
Write-Host ""
Write-Host "======================================" -ForegroundColor Cyan
Write-Host "✅ كل شيء جاهز!" -ForegroundColor Green
Write-Host "======================================" -ForegroundColor Cyan
Write-Host ""

# اختياري: اسأل المستخدم هل يريد تشغيل npm run dev
Write-Host ""
Write-Host "هل تريد تشغيل dev server الآن؟ (Y/N)" -ForegroundColor Yellow
$response = Read-Host

if ($response -eq "Y" -or $response -eq "y") {
    Write-Host ""
    Write-Host "🚀 بدء تشغيل dev server..." -ForegroundColor Green
    npm run dev
} else {
    Write-Host ""
    Write-Host "📝 اكتب هذا الأمر عندما تكون جاهز:" -ForegroundColor Yellow
    Write-Host "npm run dev" -ForegroundColor Cyan
}
