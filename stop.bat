@echo off
title Stop SAP B1 AddOn WebApp (WSL / Docker)
echo ===================================================
echo     Stopping SAP Business One WebApp Containers...
echo ===================================================
echo.

cd /d "%~dp0"

wsl bash -c "cd \"$(wslpath '%~dp0')\" && (docker compose down || docker-compose down)" 2>nul || docker compose down

echo.
echo ===================================================
echo     Application stopped successfully.
echo ===================================================
echo.
pause
