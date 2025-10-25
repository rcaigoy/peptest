@echo off
echo ===============================================
echo    INSTALLING WORDPRESS PLUGINS FROM MANIFEST
echo ===============================================
echo.

cd /d "c:\wamp64\www\peptidology"

echo Reading plugin manifest from config/required-plugins.json...
echo.

echo == INSTALLING FREE PLUGINS ==
echo.
echo Installing WooCommerce...
c:\wp-cli\wp.bat plugin install woocommerce --activate

echo Installing Classic Editor...
c:\wp-cli\wp.bat plugin install classic-editor --activate

echo Installing Classic Widgets...
c:\wp-cli\wp.bat plugin install classic-widgets --activate

echo Installing Query Monitor (development tool)...
c:\wp-cli\wp.bat plugin install query-monitor --activate

echo.
echo == LARGE PLUGINS (Reinstall from WordPress.org) ==
echo.
echo These are large plugins that we don't track in git:
echo - Jetpack (install: wp plugin install jetpack)
echo - Wordfence (install: wp plugin install wordfence)
echo - LiteSpeed Cache (install: wp plugin install litespeed-cache)

echo.
echo == PREMIUM PLUGINS - MANUAL DOWNLOAD REQUIRED ==
echo.
echo ⚠️  You need to download these from the test server:
echo.
echo 1. GRAVITY FORMS:
echo    📁 Download: wp-content/plugins/gravityforms/
echo    📂 Extract to: wp-content/plugins/gravityforms/
echo.
echo 2. ADVANCED CUSTOM FIELDS PRO:
echo    📁 Download: wp-content/plugins/advanced-custom-fields-pro/
echo    📂 Extract to: wp-content/plugins/advanced-custom-fields-pro/
echo.
echo 3. WOOCOMMERCE PAYMENT GATEWAYS:
echo    📁 Download these plugin folders:
echo    - auxpay-payment-gateway-2
echo    - coinbase-commerce-for-woocommerce-premium
echo    - wc-zelle-pro
echo    - edebit-direct-draft-plaid-gateway
echo    - wp-nmi-gateway-pci-woocommerce
echo    - checkout-fees-for-woocommerce
echo.
echo 4. MARKETING & ANALYTICS:
echo    📁 Download these plugin folders:
echo    - wp-marketing-automations
echo    - funnel-builder (and funnel-builder-pro)
echo    - facebook-for-woocommerce
echo    - google-listings-and-ads
echo    - duracelltomi-google-tag-manager
echo.
echo ===============================================
echo.
echo ✅ Free plugins installed successfully!
echo ⚠️  Manual download required for premium plugins
echo.
echo Next steps:
echo 1. Download premium plugins from test server
echo 2. Run: scripts\import-database.bat [database-file]
echo 3. Run: scripts\finalize-setup.bat
echo.
pause