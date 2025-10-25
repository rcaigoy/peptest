@echo off
echo Setting up database and user...

set MYSQL_PATH=C:\wamp64\bin\mysql\mysql8.0.37\bin
set MYSQL="%MYSQL_PATH%\mysql.exe"

REM Test MySQL connection
%MYSQL% -u root -e "SELECT 1;" >nul 2>&1
if %errorlevel% neq 0 (
    echo ERROR: Cannot connect to MySQL. Make sure WAMP is running.
    exit /b 1
)

echo Creating database 'defaultdb'...
%MYSQL% -u root -e "CREATE DATABASE IF NOT EXISTS defaultdb;"

echo Creating user 'localuser'...
%MYSQL% -u root -e "CREATE USER IF NOT EXISTS 'localuser'@'localhost' IDENTIFIED BY 'guest';"

echo Granting privileges...
%MYSQL% -u root -e "GRANT ALL PRIVILEGES ON defaultdb.* TO 'localuser'@'localhost';"
%MYSQL% -u root -e "FLUSH PRIVILEGES;"

REM Test the new user
%MYSQL% -u localuser -pguest -e "USE defaultdb; SELECT 'Database connection successful' AS status;" >nul 2>&1
if %errorlevel% equ 0 (
    echo Database setup completed successfully!
    echo Database: defaultdb
    echo User: localuser
    echo Password: guest
) else (
    echo ERROR: Database user test failed
    exit /b 1
)

echo.