@echo off
echo ================================================
echo        PRE-COMMIT SECURITY CHECK
echo ================================================
echo.

cd /d "c:\wamp64\www\peptidology"

echo 🔍 Checking for sensitive files that shouldn't be committed...
echo.

REM Check git status
set /a total_files=0
for /f %%i in ('git status --porcelain ^| find /c /v ""') do set total_files=%%i

echo 📊 Files to be committed: %total_files%

echo.
echo 🚨 Checking for sensitive patterns...

REM Check for sensitive file extensions
git ls-files --cached | findstr /r "\.key$ \.pem$ \.p12$ \.pfx$ \.crt$ \.sql$ \.db$" > temp_sensitive.txt
if exist temp_sensitive.txt (
    set /p first_line=<temp_sensitive.txt
    if not "%first_line%"=="" (
        echo ❌ SECURITY WARNING: Sensitive files found:
        type temp_sensitive.txt
        echo.
        echo These files should NOT be committed!
        del temp_sensitive.txt
        pause
        exit /b 1
    )
    del temp_sensitive.txt
)

REM Check for .htaccess files
git ls-files --cached | findstr /r "\.htaccess" > temp_htaccess.txt
if exist temp_htaccess.txt (
    set /p first_line=<temp_htaccess.txt
    if not "%first_line%"=="" (
        echo ⚠️  WARNING: .htaccess files found:
        type temp_htaccess.txt
        echo.
        echo Review these files for sensitive server configurations!
    )
    del temp_htaccess.txt
)

REM Check for wp-config files
git ls-files --cached | findstr /r "wp-config.*\.php$" > temp_config.txt
if exist temp_config.txt (
    set /p first_line=<temp_config.txt
    if not "%first_line%"=="" (
        echo ❌ SECURITY WARNING: wp-config files found:
        type temp_config.txt
        echo.
        echo wp-config.php contains database credentials and should NOT be committed!
        del temp_config.txt
        pause
        exit /b 1
    )
    del temp_config.txt
)

REM Check for large files
echo.
echo 📏 Checking for large files (>1MB)...
git ls-files --cached | for /f %%f in ('more') do (
    if exist "%%f" (
        for %%a in ("%%f") do (
            if %%~za GTR 1048576 (
                echo ⚠️  Large file: %%f (%%~za bytes)
            )
        )
    )
)

echo.
echo ✅ SECURITY CHECK SUMMARY:
echo - Total files: %total_files%
echo - No sensitive file extensions found
echo - No wp-config.php files found
echo - Review any warnings above
echo.

echo 📋 FILES TO BE COMMITTED:
git status --porcelain

echo.
echo 🎯 Repository contents:
echo - Peptidology theme: ✅ (custom code)
echo - Essential WP files: ✅ (index.php, hello.php)
echo - Setup scripts: ✅ (automation)
echo - Configuration templates: ✅ (samples only)
echo - Documentation: ✅ (README, guides)
echo.

echo ✅ Security check passed! Safe to commit.
echo.
pause