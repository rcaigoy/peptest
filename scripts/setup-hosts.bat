@echo off
echo Setting up hosts file entry...

set HOSTS_FILE=C:\Windows\System32\drivers\etc\hosts

REM Check if peptest.local already exists
findstr /C:"peptest.local" "%HOSTS_FILE%" >nul 2>&1
if %errorlevel% equ 0 (
    echo peptest.local entry already exists in hosts file.
    goto :end
)

REM Backup hosts file
copy "%HOSTS_FILE%" "%HOSTS_FILE%.backup" >nul 2>&1

REM Add peptest.local entry
echo Adding peptest.local to hosts file...
echo 127.0.0.1    peptest.local >> "%HOSTS_FILE%"

if %errorlevel% equ 0 (
    echo Hosts file updated successfully!
    echo Added: 127.0.0.1    peptest.local
) else (
    echo ERROR: Failed to update hosts file
    echo Please manually add this line to %HOSTS_FILE%:
    echo 127.0.0.1    peptest.local
    exit /b 1
)

:end
echo Hosts file setup complete!
echo.