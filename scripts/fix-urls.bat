@echo off
echo Fixing URLs and clearing cache...

cd /d "c:\wamp64\www\peptidology"

echo Forcing correct URLs...
c:\wp-cli\wp.bat option update home "http://peptest.local" 
c:\wp-cli\wp.bat option update siteurl "http://peptest.local"

echo Searching and replacing any remaining production URLs...
c:\wp-cli\wp.bat search-replace "https://peptidology.co" "http://peptest.local" --quiet
c:\wp-cli\wp.bat search-replace "http://172.235.40.151" "http://peptest.local" --quiet  
c:\wp-cli\wp.bat search-replace "172.235.40.151" "peptest.local" --quiet

echo Clearing all caches...
c:\wp-cli\wp.bat cache flush
c:\wp-cli\wp.bat transient delete --all

echo Deactivating cache plugins...
c:\wp-cli\wp.bat plugin deactivate litespeed-cache --quiet

echo Regenerating permalinks...
c:\wp-cli\wp.bat rewrite flush

echo URLs fixed! Try accessing http://peptest.local now.
pause