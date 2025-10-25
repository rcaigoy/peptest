@echo off
echo Installing WP-CLI...

REM Create wp-cli directory if it doesn't exist
if not exist "c:\wp-cli" mkdir "c:\wp-cli"

REM Download WP-CLI
echo Downloading WP-CLI...
powershell -Command "Invoke-WebRequest -Uri 'https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar' -OutFile 'c:\wp-cli\wp-cli.phar'"

if not exist "c:\wp-cli\wp-cli.phar" (
    echo ERROR: Failed to download WP-CLI
    exit /b 1
)

REM Create wp.bat wrapper
echo Creating WP-CLI wrapper...
echo @echo off > c:\wp-cli\wp.bat
echo C:\wamp64\bin\php\php8.2.26\php.exe "C:\wp-cli\wp-cli.phar" %%* >> c:\wp-cli\wp.bat

REM Add to PATH for current session
set PATH=%PATH%;c:\wp-cli

REM Test WP-CLI
echo Testing WP-CLI installation...
c:\wp-cli\wp.bat --info

if %errorlevel% equ 0 (
    echo WP-CLI installed successfully!
) else (
    echo ERROR: WP-CLI installation verification failed
    exit /b 1
)

echo.
echo WP-CLI is ready to use!
echo You can run: c:\wp-cli\wp.bat [command]
echo.