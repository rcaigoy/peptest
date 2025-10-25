@echo off
echo Clearing all WordPress caches...

cd /d "c:\wamp64\www\peptidology"

echo Clearing WordPress object cache...
c:\wp-cli\wp.bat cache flush

echo Clearing transients...
c:\wp-cli\wp.bat transient delete --all

echo Clearing rewrite rules...
c:\wp-cli\wp.bat rewrite flush

echo Deactivating cache plugins temporarily...
c:\wp-cli\wp.bat plugin deactivate litespeed-cache --quiet
c:\wp-cli\wp.bat plugin deactivate wp-super-cache --quiet  
c:\wp-cli\wp.bat plugin deactivate w3-total-cache --quiet

echo Removing cache files...
if exist "wp-content\cache" (
    echo Removing wp-content\cache...
    rmdir /s /q "wp-content\cache" 2>nul
)

if exist "wp-content\litespeed" (
    echo Removing wp-content\litespeed...
    rmdir /s /q "wp-content\litespeed" 2>nul
)

echo Removing cache configuration files...
if exist "wp-content\advanced-cache.php" del "wp-content\advanced-cache.php" 2>nul
if exist "wp-content\object-cache.php" del "wp-content\object-cache.php" 2>nul
if exist "wp-content\.litespeed_conf.dat" del "wp-content\.litespeed_conf.dat" 2>nul

echo.
echo All caches cleared!
echo Try accessing your site now: http://peptest.local
echo.
pause