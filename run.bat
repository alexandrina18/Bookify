@echo off
title Bookify - Dev Server
echo ========================================
echo   BOOKIFY - Starting Development Server
echo ========================================
echo.
cd /d C:\laragon\www\bookify\Bookify
set PATH=C:\laragon\bin\nodejs\node-v22;%PATH%
echo Server starting... tunggu sebentar ya!
echo Setelah muncul "Ready", buka browser ke:
echo http://localhost:3000
echo.
echo [Jangan tutup window ini selama pakai website]
echo ========================================
echo.
npm.cmd run dev
pause
