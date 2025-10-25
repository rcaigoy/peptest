@echo off
echo Setting up WordPress configuration...

cd /d "c:\wamp64\www\peptidology"

REM Check if wp-config.php already exists
if exist "wp-config.php" (
    echo wp-config.php already exists. Creating backup...
    copy "wp-config.php" "wp-config.php.backup" >nul
)

REM Create wp-config.php from sample if it doesn't exist
if not exist "wp-config.php" (
    if exist "wp-config-sample.php" (
        echo Creating wp-config.php from sample...
        copy "wp-config-sample.php" "wp-config.php" >nul
    ) else (
        echo ERROR: wp-config-sample.php not found
        exit /b 1
    )
)

REM Use WP-CLI to configure WordPress
echo Configuring WordPress settings...

c:\wp-cli\wp.bat config set DB_NAME defaultdb --type=constant
c:\wp-cli\wp.bat config set DB_USER localuser --type=constant  
c:\wp-cli\wp.bat config set DB_PASSWORD guest --type=constant
c:\wp-cli\wp.bat config set DB_HOST localhost --type=constant

c:\wp-cli\wp.bat config set WP_HOME http://peptest.local --type=constant
c:\wp-cli\wp.bat config set WP_SITEURL http://peptest.local --type=constant

c:\wp-cli\wp.bat config set WP_DEBUG true --type=constant
c:\wp-cli\wp.bat config set WP_DEBUG_LOG true --type=constant
c:\wp-cli\wp.bat config set WP_DEBUG_DISPLAY false --type=constant
c:\wp-cli\wp.bat config set SCRIPT_DEBUG true --type=constant
c:\wp-cli\wp.bat config set WP_ENVIRONMENT_TYPE local --type=constant

c:\wp-cli\wp.bat config set WP_MEMORY_LIMIT 1024M --type=constant
c:\wp-cli\wp.bat config set WP_MAX_MEMORY_LIMIT 2048M --type=constant
c:\wp-cli\wp.bat config set FS_METHOD direct --type=constant

REM Disable cache for initial setup
c:\wp-cli\wp.bat config set WP_CACHE false --type=constant

echo WordPress configuration complete!
echo Local URLs: http://peptest.local
echo Database: defaultdb (localuser/guest)
echo Debug logging: enabled
echo.