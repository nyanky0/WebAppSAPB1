@echo off
title Start SAP B1 AddOn WebApp
echo ===================================================
echo     Starting SAP Business One WebApp Containers...
echo ===================================================
echo.

cd /d "%~dp0"

docker compose up -d

echo.
echo ===================================================
echo     Application is starting up!
echo ===================================================
echo     Backend WebApp:  http://localhost:8000
echo     Frontend Assets: http://localhost:5173
echo ===================================================
echo.
pause
