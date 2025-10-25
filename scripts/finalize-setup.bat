@echo off
echo Finalizing local setup...

cd /d "c:\wamp64\www\peptidology"

echo Step 1: Updating WordPress URLs...
c:\wp-cli\wp.bat option update home "http://peptest.local"
c:\wp-cli\wp.bat option update siteurl "http://peptest.local"

echo Step 2: Clearing all caches...
c:\wp-cli\wp.bat cache flush
c:\wp-cli\wp.bat transient delete --all

echo Step 3: Deactivating problematic plugins for local development...
c:\wp-cli\wp.bat plugin deactivate litespeed-cache --quiet
c:\wp-cli\wp.bat plugin deactivate wordfence --quiet

echo Step 4: Updating user permissions...
c:\wp-cli\wp.bat user list --field=user_login | findstr /v "^$" > temp_users.txt
for /f %%i in (temp_users.txt) do (
    c:\wp-cli\wp.bat user update %%i --user_pass=admin123 --quiet
    echo Updated password for user: %%i
)
del temp_users.txt

echo Step 5: Regenerating .htaccess...
c:\wp-cli\wp.bat rewrite flush

echo Step 6: Final URL cleanup...
c:\wp-cli\wp.bat search-replace "https://peptidology.co" "http://peptest.local" --quiet
c:\wp-cli\wp.bat search-replace "http://172.235.40.151" "http://peptest.local" --quiet
c:\wp-cli\wp.bat search-replace "172.235.40.151" "peptest.local" --quiet

echo Step 7: Verifying setup...
c:\wp-cli\wp.bat option get home
c:\wp-cli\wp.bat option get siteurl

echo.
echo =========================================
echo        SETUP FINALIZED!
echo =========================================
echo.
echo Your local WordPress site is ready!
echo.
echo Access your site: http://peptest.local
echo WordPress Admin: http://peptest.local/wp-admin
echo Default password: admin123 (for all users)
echo.
echo IMPORTANT: Change passwords after first login!
echo.
pause