@echo off
if "%~1"=="" (
    echo Usage: import-database.bat [database-file.sql]
    echo Example: import-database.bat peptidology_backup.sql
    pause
    exit /b 1
)

set DATABASE_FILE=%~1
cd /d "c:\wamp64\www\peptidology"

if not exist "%DATABASE_FILE%" (
    echo ERROR: Database file '%DATABASE_FILE%' not found
    echo Please make sure the file exists in the current directory
    pause
    exit /b 1
)

echo Importing database from %DATABASE_FILE%...
echo This may take a few minutes for large databases...

REM Import database using WP-CLI
c:\wp-cli\wp.bat db import "%DATABASE_FILE%"

if %errorlevel% equ 0 (
    echo Database imported successfully!
    echo.
    echo Running URL replacement...
    c:\wp-cli\wp.bat search-replace "https://peptidology.co" "http://peptest.local" --dry-run
    
    echo.
    set /p CONTINUE="Continue with URL replacement? (y/n): "
    if /i "%CONTINUE%"=="y" (
        c:\wp-cli\wp.bat search-replace "https://peptidology.co" "http://peptest.local"
        c:\wp-cli\wp.bat search-replace "http://172.235.40.151" "http://peptest.local"
        c:\wp-cli\wp.bat search-replace "172.235.40.151" "peptest.local"
        echo URL replacement complete!
    )
) else (
    echo ERROR: Database import failed
    pause
    exit /b 1
)

echo.
echo Database import complete!
echo Next step: Copy uploads folder and run finalize-setup.bat
echo.