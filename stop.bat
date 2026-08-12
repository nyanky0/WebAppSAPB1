@echo off
title Stop SAP B1 AddOn WebApp
echo ===================================================
echo     Stopping SAP Business One WebApp Containers...
echo ===================================================
echo.

cd /d "%~dp0"

docker compose down

echo.
echo ===================================================
echo     Application stopped successfully.
echo ===================================================
echo.
pause
