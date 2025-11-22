Automation & Electrical E‑commerce API (PHP + MySQL)
====================================================

Overview
--------
A lightweight, deploy-ready PHP API for an industrial automation e‑commerce website. It supports products, categories, brands, contact messages, admin authentication (JWT), and image uploads. Designed for shared hosting (cPanel) with PHP 8+ and MySQL.

Key Features
- Clean REST-ish endpoints under /api
- JWT-based admin authentication (file-based .env config)
- Secure input validation and prepared statements
- Image uploads to /api/uploads (publicly accessible)
- SEO-friendly slugs for brands/categories
- Specs stored as JSON
- CORS headers configurable

Directory Structure (place on shared hosting)
- api/
  - public/ (optional if your host points here) 
  - uploads/ (writable, for product images)
  - config.php
  - db.php
  - helpers.php
  - middleware.php
  - index.php (router)
  - routes/
    - public.php
    - admin.php
  - controllers/
    - ProductsController.php
    - CategoriesController.php
    - BrandsController.php
    - AuthController.php
    - UploadController.php
    - ContactController.php
  - .env (copy from .env.example and fill)

Endpoints
Public
- GET /api/products
- GET /api/product?id=123
- GET /api/categories
- GET /api/brands
- POST /api/contact-message

Admin (require Bearer token from /api/login)
- POST /api/login
- POST /api/add-product
- POST /api/update-product
- POST /api/delete-product
- POST /api/upload-image

Database Schema
See schema.sql in this folder. Import using phpMyAdmin (Import tab). Then create an admin user with a hashed password:
- Default admin seed in schema.sql (username: admin, password: admin123) — change after first login.

Setup (Shared Hosting)
1) Create a MySQL database and user; grant all privileges.
2) Upload the api/ folder to your hosting (e.g., public_html/api or api subfolder).
3) Copy .env.example to .env and fill DB credentials and JWT secret.
4) Ensure api/uploads is writable (Permissions 755 or 775, depending on host).
5) In phpMyAdmin, import schema.sql.
6) Test the API:
   - GET https://your-domain.com/api/brands
   - POST https://your-domain.com/api/login with JSON {"username":"admin","password":"admin123"}
7) Point your React app’s API base URL to https://your-domain.com/api.

CORS
Set ALLOWED_ORIGINS in config.php or allow all (*) for quick tests, then restrict to your domain in production.

Security Notes
- Always use HTTPS
- Change JWT_SECRET and admin password immediately
- Validate images (type/size) and consider enabling a CDN for uploads
- Rate-limit login on your host (e.g., via ModSecurity rules)

Troubleshooting
- 500 errors? Enable APP_DEBUG=true in .env temporarily and check error logs (error_log or host control panel).
- 403 on upload? Fix folder permissions for uploads.

License
MIT
