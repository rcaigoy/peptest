@echo off
echo Creating local database backup...

cd /d "c:\wamp64\www\peptidology"

REM Create backups directory if it doesn't exist
if not exist "backups" mkdir "backups"

REM Generate filename with timestamp
for /f "tokens=2 delims==" %%a in ('wmic OS Get localdatetime /value') do set "dt=%%a"
set "YY=%dt:~2,2%" & set "YYYY=%dt:~0,4%" & set "MM=%dt:~4,2%" & set "DD=%dt:~6,2%"
set "HH=%dt:~8,2%" & set "Min=%dt:~10,2%" & set "Sec=%dt:~12,2%"
set "timestamp=%YYYY%%MM%%DD%_%HH%%Min%%Sec%"

set BACKUP_FILE=backups\peptidology_local_%timestamp%.sql

echo Backing up database to %BACKUP_FILE%...
c:\wp-cli\wp.bat db export "%BACKUP_FILE%"

if %errorlevel% equ 0 (
    echo Backup created successfully: %BACKUP_FILE%
) else (
    echo ERROR: Backup failed
    pause
    exit /b 1
)

echo.
echo Backup complete!
echo File: %BACKUP_FILE%
echo.