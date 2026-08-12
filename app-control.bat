@echo off
title SAP B1 AddOn - Control Panel (WSL / Docker)
:menu
cls
echo ===================================================
echo     SAP Business One AddOn WebApp Control Panel
echo ===================================================
echo.
echo     [1] Start Application (Turn ON)
echo     [2] Stop Application (Turn OFF)
echo     [3] Restart Application
echo     [4] Check Container Status
echo     [5] View Live Backend Logs
echo     [6] Exit
echo.
echo ===================================================
set /p choice="Select an option [1-6]: "

if "%choice%"=="1" goto start_app
if "%choice%"=="2" goto stop_app
if "%choice%"=="3" goto restart_app
if "%choice%"=="4" goto status_app
if "%choice%"=="5" goto logs_app
if "%choice%"=="6" goto end_app

echo Invalid option, please try again.
pause
goto menu

:start_app
cls
echo Starting containers in WSL / Docker...
cd /d "%~dp0"
wsl bash -c "cd \"$(wslpath '%~dp0')\" && (docker compose up -d || docker-compose up -d)" 2>nul || docker compose up -d
echo.
echo Application started! Access at http://localhost:8000
pause
goto menu

:stop_app
cls
echo Stopping containers in WSL / Docker...
cd /d "%~dp0"
wsl bash -c "cd \"$(wslpath '%~dp0')\" && (docker compose down || docker-compose down)" 2>nul || docker compose down
echo.
echo Application stopped successfully.
pause
goto menu

:restart_app
cls
echo Restarting containers in WSL / Docker...
cd /d "%~dp0"
wsl bash -c "cd \"$(wslpath '%~dp0')\" && (docker compose restart || docker-compose restart)" 2>nul || docker compose restart
echo.
echo Application restarted successfully.
pause
goto menu

:status_app
cls
echo Container status:
cd /d "%~dp0"
wsl bash -c "cd \"$(wslpath '%~dp0')\" && (docker compose ps || docker-compose ps)" 2>nul || docker compose ps
echo.
pause
goto menu

:logs_app
cls
echo Press Ctrl+C to stop viewing logs...
cd /d "%~dp0"
wsl bash -c "cd \"$(wslpath '%~dp0')\" && (docker compose logs -f backend || docker-compose logs -f backend)" 2>nul || docker compose logs -f backend
pause
goto menu

:end_app
exit
