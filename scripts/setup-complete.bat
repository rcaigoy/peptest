@echo off
setlocal enabledelayedexpansion

echo =========================================
echo    PEPTIDOLOGY LOCAL SETUP - COMPLETE
echo =========================================
echo.

REM Check if running as administrator
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo ERROR: This script must be run as Administrator
    echo Please right-click and select "Run as administrator"
    pause
    exit /b 1
)

echo Step 1: Installing WP-CLI...
call "%~dp0install-wp-cli.bat"
if %errorlevel% neq 0 (
    echo ERROR: WP-CLI installation failed
    pause
    exit /b 1
)

echo.
echo Step 2: Setting up WAMP virtual host...
call "%~dp0setup-vhost.bat"
if %errorlevel% neq 0 (
    echo ERROR: Virtual host setup failed
    pause
    exit /b 1
)

echo.
echo Step 3: Creating database and user...
call "%~dp0setup-database.bat"
if %errorlevel% neq 0 (
    echo ERROR: Database setup failed
    pause
    exit /b 1
)

echo.
echo Step 4: Setting up WordPress configuration...
call "%~dp0setup-wp-config.bat"
if %errorlevel% neq 0 (
    echo ERROR: WordPress configuration failed
    pause
    exit /b 1
)

echo.
echo Step 5: Installing WordPress plugins...
call "%~dp0install-plugins.bat"
if %errorlevel% neq 0 (
    echo ERROR: Plugin installation failed
    pause
    exit /b 1
)

echo.
echo Step 6: Setting up hosts file...
call "%~dp0setup-hosts.bat"
if %errorlevel% neq 0 (
    echo ERROR: Hosts file setup failed
    pause
    exit /b 1
)

echo.
echo Step 7: Setting up .htaccess file...
call "%~dp0setup-htaccess.bat"
if %errorlevel% neq 0 (
    echo ERROR: .htaccess setup failed
    pause
    exit /b 1
)

echo.
echo =========================================
echo           SETUP COMPLETE!
echo =========================================
echo.
echo Your local WordPress site is ready!
echo.
echo Next steps:
echo 1. Download database and uploads from production
echo 2. Run: .\scripts\import-database.bat [database-file.sql]
echo 3. Copy uploads to wp-content\uploads\
echo 4. Run: .\scripts\finalize-setup.bat
echo.
echo Access your site at: http://peptest.local
echo.
pause