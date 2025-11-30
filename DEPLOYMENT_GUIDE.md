# School ERP Deployment Guide

## Deployment Package Created
- **File**: `school-erp-deployment.zip`
- **Size**: ~24 MB (reduced from 2.2 GB)
- **Contains**: Production-ready Laravel application with built assets

## What's Included
- ✅ Laravel application code (`app/`)
- ✅ Configuration files (`config/`)
- ✅ Database migrations and seeders (`database/`)
- ✅ Built frontend assets (`public/build/`)
- ✅ Blade views (`resources/views/`)
- ✅ Routes (`routes/`)
- ✅ Production composer packages (`vendor/` - no dev dependencies)
- ✅ Essential bootstrap files
- ✅ Environment example file

## What's Excluded (Space Optimized)
- ❌ `node_modules/` (saved ~500 MB)
- ❌ Development dependencies from `vendor/` (saved ~1.5 GB)
- ❌ `.git/` repository files
- ❌ `tests/` directory
- ❌ Log files and caches
- ❌ Source SCSS/JS files (only built assets included)

## cPanel Deployment Steps

### 1. Upload & Extract
1. Upload `school-erp-deployment.zip` to your cPanel file manager
2. Extract the ZIP file in your domain's root directory (usually `public_html/`)

### 2. Configure Environment
1. Copy `.env.example` to `.env`
2. Update database settings in `.env`:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=your_database_name
   DB_USERNAME=your_database_user
   DB_PASSWORD=your_database_password
   ```

### 3. Set Permissions
In cPanel File Manager, set these permissions:
- `storage/` directory: 755
- `bootstrap/cache/` directory: 755
- `vendor/` directory: 755

### 4. Run Database Migration
If you have SSH access, run:
```bash
php artisan migrate --force
php artisan db:seed --class=SuperAdminSeeder
```

If no SSH access, import the database manually via phpMyAdmin.

### 5. Optimize Application
Run these commands if you have SSH access:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 6. Set Document Root
Ensure your domain's document root points to the `public/` folder of the Laravel application.

## Built Assets Information
- **CSS**: `public/build/assets/app-ZxWf6FO3.css` (1.2 MB, gzipped: 116 KB)
- **JS**: `public/build/assets/app-hKSwO6z_.js` (254 KB, gzipped: 80 KB)
- **Manifest**: `public/build/manifest.json`

## Post-Deployment Checklist
- [ ] Application loads without errors
- [ ] Database is populated with seed data
- [ ] Admin login works (default: admin@example.com / password)
- [ ] All modules display correctly
- [ ] Teacher subjects show names instead of IDs
- [ ] Routes work properly (no 404 errors)

## Troubleshooting
- **500 Internal Server Error**: Check `.env` file permissions and database connection
- **Missing CSS/JS**: Ensure `public/build/` directory exists and contains files
- **Permission Errors**: Set proper permissions on `storage/` and `bootstrap/cache/` directories
- **Database Errors**: Run migrations and ensure database credentials are correct

## Support
The deployment package includes all necessary files for a production environment. The application is optimized for cPanel hosting with limited space requirements.