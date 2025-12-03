#!/usr/bin/env pwsh

# ≡ƒº¬ ╪º╪«╪¬╪¿╪º╪▒ ╪│╪▒┘è╪╣ ┘ä┘à┘è╪▓╪⌐ Change Password (Windows PowerShell)

Write-Host "======================================" -ForegroundColor Cyan
Write-Host "≡ƒº¬ ╪º╪«╪¬╪¿╪º╪▒ Change Password Feature" -ForegroundColor Cyan
Write-Host "======================================" -ForegroundColor Cyan
Write-Host ""

# 1. ╪º┘ä╪¬╪¡┘é┘é ┘à┘å ╪ú┘å ╪º┘ä┘à┘ä┘ü╪º╪¬ ┘à┘ê╪¼┘ê╪»╪⌐
Write-Host "≡ƒôü ╪º┘ä╪¬╪¡┘é┘é ┘à┘å ╪º┘ä┘à┘ä┘ü╪º╪¬..." -ForegroundColor Yellow
Write-Host ""

if (Test-Path "src/components/auth/ChangePasswordPage.jsx") {
    Write-Host "Γ£à ChangePasswordPage.jsx ┘à┘ê╪¼┘ê╪»" -ForegroundColor Green
} else {
    Write-Host "Γ¥î ChangePasswordPage.jsx ╪║┘è╪▒ ┘à┘ê╪¼┘ê╪»" -ForegroundColor Red
}

if (Test-Path "src/components/auth/change_password.php") {
    Write-Host "Γ£à change_password.php ┘à┘ê╪¼┘ê╪»" -ForegroundColor Green
} else {
    Write-Host "Γ¥î change_password.php ╪║┘è╪▒ ┘à┘ê╪¼┘ê╪»" -ForegroundColor Red
}

Write-Host ""
Write-Host "======================================" -ForegroundColor Cyan
Write-Host "≡ƒôè ╪º┘ä┘à┘ä┘ü╪º╪¬ ╪º┘ä┘à┘Å┘å╪┤╪ú╪⌐:" -ForegroundColor Cyan
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
        Write-Host "Γ£à $_" -ForegroundColor Green
    }
}

Write-Host ""
Write-Host "======================================" -ForegroundColor Cyan
Write-Host "≡ƒÜÇ ╪«╪╖┘ê╪º╪¬ ╪º┘ä╪º╪«╪¬╪¿╪º╪▒:" -ForegroundColor Cyan
Write-Host "======================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "1∩╕ÅΓâú  ╪¬╪┤╪║┘è┘ä dev server:" -ForegroundColor Yellow
Write-Host "    npm run dev" -ForegroundColor White
Write-Host ""
Write-Host "2∩╕ÅΓâú  ╪º┘ü╪¬╪¡ ╪º┘ä┘à╪¬╪╡┘ü╪¡:" -ForegroundColor Yellow
Write-Host "    http://localhost:5174" -ForegroundColor White
Write-Host ""
Write-Host "3∩╕ÅΓâú  ╪│╪¼┘ä ╪º┘ä╪»╪«┘ê┘ä" -ForegroundColor Yellow
Write-Host ""
Write-Host "4∩╕ÅΓâú  ╪º┘å┘é╪▒ ╪╣┘ä┘ë PROFILE" -ForegroundColor Yellow
Write-Host ""
Write-Host "5∩╕ÅΓâú  ╪º┘å┘é╪▒ ╪╣┘ä┘ë ╪º┘ä╪▓╪▒ ╪º┘ä╪¼╪»┘è╪» CHANGE_PASSWORD" -ForegroundColor Yellow
Write-Host ""
Write-Host "6∩╕ÅΓâú  ┘à┘ä╪í ╪º┘ä┘å┘à┘ê╪░╪¼ ┘ê╪º┘å┘é╪▒ UPDATE_PASSWORD" -ForegroundColor Yellow
Write-Host ""
Write-Host "======================================" -ForegroundColor Cyan
Write-Host "Γ£à ┘â┘ä ╪┤┘è╪í ╪¼╪º┘ç╪▓!" -ForegroundColor Green
Write-Host "======================================" -ForegroundColor Cyan
Write-Host ""

# ╪º╪«╪¬┘è╪º╪▒┘è: ╪º╪│╪ú┘ä ╪º┘ä┘à╪│╪¬╪«╪»┘à ┘ç┘ä ┘è╪▒┘è╪» ╪¬╪┤╪║┘è┘ä npm run dev
Write-Host ""
Write-Host "┘ç┘ä ╪¬╪▒┘è╪» ╪¬╪┤╪║┘è┘ä dev server ╪º┘ä╪ó┘å╪ƒ (Y/N)" -ForegroundColor Yellow
$response = Read-Host

if ($response -eq "Y" -or $response -eq "y") {
    Write-Host ""
    Write-Host "≡ƒÜÇ ╪¿╪»╪í ╪¬╪┤╪║┘è┘ä dev server..." -ForegroundColor Green
    npm run dev
} else {
    Write-Host ""
    Write-Host "≡ƒô¥ ╪º┘â╪¬╪¿ ┘ç╪░╪º ╪º┘ä╪ú┘à╪▒ ╪╣┘å╪»┘à╪º ╪¬┘â┘ê┘å ╪¼╪º┘ç╪▓:" -ForegroundColor Yellow
    Write-Host "npm run dev" -ForegroundColor Cyan
}
