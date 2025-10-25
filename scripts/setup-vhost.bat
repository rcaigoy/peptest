@echo off
echo Setting up WAMP virtual host for peptest.local...

set APACHE_CONF=C:\wamp64\bin\apache\apache2.4.58\conf\extra\httpd-vhosts.conf

REM Backup existing vhosts file
copy "%APACHE_CONF%" "%APACHE_CONF%.backup" >nul 2>&1

REM Check if peptest.local already exists
findstr /C:"peptest.local" "%APACHE_CONF%" >nul 2>&1
if %errorlevel% equ 0 (
    echo Virtual host for peptest.local already exists.
    goto :restart_apache
)

REM Add virtual host configuration
echo Adding virtual host configuration...
echo. >> "%APACHE_CONF%"
echo # peptest.local >> "%APACHE_CONF%"
echo ^<VirtualHost *:80^> >> "%APACHE_CONF%"
echo     ServerName peptest.local >> "%APACHE_CONF%"
echo     DocumentRoot "C:/wamp64/www/peptidology" >> "%APACHE_CONF%"
echo     ^<Directory "C:/wamp64/www/peptidology"^> >> "%APACHE_CONF%"
echo         AllowOverride All >> "%APACHE_CONF%"
echo         Require all granted >> "%APACHE_CONF%"
echo     ^</Directory^> >> "%APACHE_CONF%"
echo ^</VirtualHost^> >> "%APACHE_CONF%"

echo Virtual host configuration added successfully.

:restart_apache
echo Restarting Apache...
net stop wampapache64 >nul 2>&1
timeout /t 2 >nul
net start wampapache64 >nul 2>&1

if %errorlevel% equ 0 (
    echo Apache restarted successfully.
) else (
    echo WARNING: Could not restart Apache automatically.
    echo Please restart Apache manually through WAMP.
)

echo Virtual host setup complete!