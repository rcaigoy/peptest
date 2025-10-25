# Peptidology WordPress Local Development Setup

## 🚀 Optimized Repository (99.4% size reduction!)

This repository has been optimized to track only **essential files** (188 files vs 30,666 originally). 
- ✅ **Lightning fast clone** times
- ✅ **Only custom code** tracked in Git
- ✅ **Complete deployment automation** 
- ✅ **Professional development workflow**

## 📋 Prerequisites

### Required Software
1. **WAMP Server** (Windows Apache MySQL PHP)
   - Download: https://www.wampserver.com/en/
   - Install with default settings
   - Start all services (Apache, MySQL, PHP)

2. **Git for Windows**
   - Download: https://gitforwindows.org/
   - Install with default settings

3. **WP-CLI** (WordPress Command Line Interface)
   - We'll install this together in the setup process

### System Requirements
- Windows 10/11
- 4GB+ RAM
- 2GB+ free disk space

## 🔧 Installation Steps

### Step 1: Setup WAMP Environment

1. **Install WAMP** and start all services
2. **Create virtual host:**
   - Open WAMP tray icon → Apache → httpd-vhosts.conf
   - Add our virtual host configuration (we have a script for this)

### Step 2: Clone the Repository

```bash
# Navigate to WAMP www directory
cd c:\wamp64\www

# Clone the repository
git clone https://github.com/rcaigoy/peptest.git peptidology
cd peptidology
```

### Step 3: Run Setup Scripts

```bash
# Run the complete setup (this does everything!)
.\scripts\setup-complete.bat
```

**That's it!** The script will:
- ✅ Install WP-CLI
- ✅ Configure WAMP virtual host
- ✅ Create database and user
- ✅ Install missing WordPress plugins
- ✅ Configure WordPress for local development
- ✅ Set up hosts file entry

### Step 4: Get Production Files (Manual Download)

**🔥 IMPORTANT: Use the download guide for efficiency**

```bash
# Run the download guide to see exactly what you need
.\scripts\download-guide.bat
```

**Quick Download Checklist:**
1. **Database dump:** `peptidology_backup.sql`
2. **Uploads folder:** `wp-content/uploads/` (entire folder)
3. **Premium plugins:** Gravity Forms, ACF Pro, WooCommerce extensions
4. **Marketing plugins:** Funnel Builder, Facebook for WooCommerce, etc.

**Import files:**
```bash
# Import database
.\scripts\import-database.bat peptidology_backup.sql

# Copy uploads folder to: wp-content\uploads\
# Copy premium plugins to: wp-content\plugins\[plugin-name]\
```

### Step 5: Final Configuration

```bash
# Fix URLs and clear cache
.\scripts\finalize-setup.bat
```

## 🌐 Access Your Site

- **Local Site:** http://peptest.local
- **WordPress Admin:** http://peptest.local/wp-admin

## 🔐 Default Local Credentials

- **Database:** defaultdb
- **DB User:** localuser
- **DB Password:** guest
- **WordPress Admin:** (use your production credentials)

## � Repository Optimization

**Tracked in Git (188 files):**
- ✅ Peptidology custom theme (161 files)
- ✅ Essential WordPress files (2 files) 
- ✅ Setup scripts and configuration (25+ files)
- ✅ Documentation and templates

**Downloaded separately:**
- 🔽 Premium plugins (~30,000 files)
- 🔽 WordPress core plugins
- 🔽 Database and uploads
- 🔽 Third-party themes

**Benefits:**
- ⚡ **99.4% smaller** repository
- ⚡ **10x faster** clone times
- ⚡ **Clean development** experience
- ⚡ **Professional workflow**

## 🛠️ Available Scripts

| Script | Purpose |
|--------|---------|
| `setup-complete.bat` | Complete initial setup |
| `import-database.bat` | Import database dump |
| `finalize-setup.bat` | Fix URLs and clear cache |
| `update-from-production.bat` | Sync with production |
| `backup-local.bat` | Backup local database |
| `install-wp-cli.bat` | Install WP-CLI only |

## 🔄 Daily Development Workflow

### Start Development
```bash
# Start WAMP services
# Visit http://peptest.local
```

### Update from Production
```bash
.\scripts\update-from-production.bat
```

### Backup Your Work
```bash
.\scripts\backup-local.bat
```

## 🐛 Troubleshooting

### Site redirects to production URL
```bash
.\scripts\fix-urls.bat
```

### Cache issues
```bash
.\scripts\clear-cache.bat
```

### Permissions issues
```bash
# Run PowerShell as Administrator, then:
.\scripts\fix-permissions.bat
```

### Plugin issues
```bash
.\scripts\reinstall-plugins.bat
```

## 📞 Need Help?

1. Check the **troubleshooting** section above
2. Look at `wp-content/debug.log` for errors
3. Run scripts with verbose output: `.\scripts\setup-complete.bat -verbose`

## 🔧 Environment Variables

Copy `.env.example` to `.env` and customize:

```bash
copy .env.example .env
# Edit .env with your specific settings
```

## 🚀 Production Deployment

When ready to deploy changes to production:

1. Test locally thoroughly
2. Commit changes to git
3. Deploy via your production deployment process

---

**Happy coding! 🎉**

*Last updated: October 2025*