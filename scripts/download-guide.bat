@echo off
echo ================================================
echo     DOWNLOAD GUIDE - GET FILES FROM TEST SERVER
echo ================================================
echo.
echo This script guides you through downloading required files
echo from your test server that are NOT tracked in git.
echo.
echo ========================================
echo  REQUIRED DOWNLOADS FROM TEST SERVER
echo ========================================
echo.
echo 📁 1. DATABASE BACKUP
echo    File: Export database from test server
echo    Save as: peptidology_backup.sql
echo    Place in: scripts\ folder
echo    Import with: scripts\import-database.bat peptidology_backup.sql
echo.
echo 📁 2. UPLOADS FOLDER (Media Files)
echo    Download: /wp-content/uploads/ (entire folder)
echo    Extract to: wp-content\uploads\
echo    Size: Usually 100MB - 2GB
echo.
echo 📁 3. PREMIUM PLUGINS (High Priority)
echo    These are required for the site to function:
echo.
echo    a) Gravity Forms:
echo       Download: /wp-content/plugins/gravityforms/
echo       Extract to: wp-content\plugins\gravityforms\
echo.
echo    b) Advanced Custom Fields Pro:
echo       Download: /wp-content/plugins/advanced-custom-fields-pro/
echo       Extract to: wp-content\plugins\advanced-custom-fields-pro\
echo.
echo 📁 4. WOOCOMMERCE PAYMENT GATEWAYS
echo    Download these plugin folders and extract to wp-content\plugins\:
echo    - auxpay-payment-gateway-2
echo    - coinbase-commerce-for-woocommerce-premium  
echo    - wc-zelle-pro
echo    - edebit-direct-draft-plaid-gateway
echo    - wp-nmi-gateway-pci-woocommerce
echo    - checkout-fees-for-woocommerce
echo    - woo-coupon-usage-pro
echo.
echo 📁 5. MARKETING & ANALYTICS PLUGINS
echo    Download these plugin folders:
echo    - wp-marketing-automations
echo    - funnel-builder
echo    - funnel-builder-pro
echo    - facebook-for-woocommerce
echo    - google-listings-and-ads
echo    - duracelltomi-google-tag-manager
echo    - omnisend-connect
echo    - triple-whale
echo.
echo 📁 6. OTHER PLUGINS (Optional but recommended)
echo    - aftership-woocommerce-tracking
echo    - simple-banner
echo    - wp-2fa
echo    - wp-mail-smtp-pro
echo    - wp-security-audit-log
echo.
echo ========================================
echo  DOWNLOAD CHECKLIST
echo ========================================
echo.
echo [ ] Database exported and saved as peptidology_backup.sql
echo [ ] Uploads folder downloaded to wp-content\uploads\
echo [ ] Gravity Forms plugin downloaded
echo [ ] ACF Pro plugin downloaded
echo [ ] Payment gateway plugins downloaded
echo [ ] Marketing plugins downloaded
echo.
echo ========================================
echo  AFTER DOWNLOADING
echo ========================================
echo.
echo 1. Import database: scripts\import-database.bat peptidology_backup.sql
echo 2. Finalize setup: scripts\finalize-setup.bat
echo 3. Test site: http://peptest.local
echo.
echo ========================================
echo  FILE SIZE ESTIMATES
echo ========================================
echo.
echo Database: 10-100MB
echo Uploads: 100MB - 2GB
echo Premium Plugins: 50-200MB total
echo Payment Gateways: 20-50MB total
echo Marketing Plugins: 30-100MB total
echo.
echo Total download: ~200MB - 2.5GB
echo.
pause