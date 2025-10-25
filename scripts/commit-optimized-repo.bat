@echo off
echo ================================================
echo    PREPARING OPTIMIZED REPOSITORY FOR COMMIT
echo ================================================
echo.

cd /d "c:\wamp64\www\peptidology"

echo 📊 Current repository status:
git status --porcelain | Measure-Object | ForEach-Object { "Files to add: " + $_.Count }

echo.
echo 📁 File breakdown:
echo Themes: 161 files (Peptidology custom theme)
echo Plugins: 2 files (essential WordPress files only)
echo Scripts: 12+ setup and maintenance scripts
echo Config: Plugin manifest, .env template, etc.
echo Docs: README.md, templates

echo.
echo 🔒 Running security check...
call "%~dp0pre-commit-check.bat"
if %errorlevel% neq 0 (
    echo.
    echo ❌ Security check failed! Please review and fix issues before committing.
    pause
    exit /b 1
)

echo.
echo 🎯 Ready to commit optimized repository!
echo.
echo Before committing, verify:
echo [ ] README.md updated with new workflow
echo [ ] All scripts tested and working
echo [ ] .gitignore properly excludes large files
echo [ ] Plugin manifest is complete
echo [ ] Download guide is accurate
echo.

set /p COMMIT="Commit the optimized repository? (y/n): "
if /i "%COMMIT%"=="y" (
    echo.
    echo Adding files to git...
    git add .
    
    echo.
    echo Committing optimized repository...
    git commit -m "🚀 Repository optimization: 99.4%% size reduction

- Reduced from 30,666 to 188 files
- Track only essential custom code (Peptidology theme)
- Added comprehensive setup automation
- Created plugin manifest system  
- Added download guides for production files
- Optimized for fast cloning and development

Files tracked:
- Peptidology theme (161 files)
- Essential WP files (2 files)
- Setup scripts (12+ files)
- Configuration and docs

Benefits:
- Lightning fast git operations
- Clean development workflow
- Professional deployment process
- Maintainable codebase"

    echo.
    echo ✅ Repository optimized and committed!
    echo.
    echo Next steps:
    echo 1. Push to remote: git push
    echo 2. Test deployment on clean machine
    echo 3. Update team documentation
    echo 4. Train developers on new workflow
    
) else (
    echo Commit cancelled. Review files and run again when ready.
)

echo.
pause