@echo off
echo Setting up .htaccess file...

cd /d "c:\wamp64\www\peptidology"

if not exist ".htaccess" (
    if exist ".htaccess-sample" (
        echo Creating .htaccess from sample template...
        copy ".htaccess-sample" ".htaccess"
        echo ✅ .htaccess created from sample template
        echo.
        echo ⚠️  IMPORTANT: Review and customize .htaccess for your environment
    ) else (
        echo Creating basic .htaccess...
        echo # BEGIN WordPress > .htaccess
        echo ^<IfModule mod_rewrite.c^> >> .htaccess
        echo RewriteEngine On >> .htaccess
        echo RewriteRule .* - [E=HTTP_AUTHORIZATION:%%{HTTP:Authorization}] >> .htaccess
        echo RewriteBase / >> .htaccess
        echo RewriteRule ^^index\.php$ - [L] >> .htaccess
        echo RewriteCond %%{REQUEST_FILENAME} !-f >> .htaccess
        echo RewriteCond %%{REQUEST_FILENAME} !-d >> .htaccess
        echo RewriteRule . /index.php [L] >> .htaccess
        echo ^</IfModule^> >> .htaccess
        echo # END WordPress >> .htaccess
        echo ✅ Basic .htaccess created
    )
) else (
    echo .htaccess already exists - skipping creation
)

echo.
echo 📝 NOTES:
echo - LiteSpeed Cache will auto-configure .htaccess when activated
echo - Don't manually edit cache-related rules
echo - Customize security settings as needed
echo - Review file before production deployment
echo.
pause