# SAP B1 Web AddOn

A modern, highly-responsive web application built with Laravel 11, Tailwind CSS v4, and Alpine.js. This application serves as a seamless integration layer with **SAP Business One Service Layer**, providing an elegant user interface for administration and daily operations.

## ✨ Key Features

### 1. Dynamic SAP B1 Configuration
- Configure SAP Service Layer Base URL, Database, and Period Indicators on the fly.
- **Smart Fetching**: Dynamically connects to SAP Service Layer to fetch and display the live list of Company Databases before saving.
- Securely caches Service Layer session tokens to drastically improve API performance.

### 2. Item Master Data Synchronization
- Local `items` database table acts as a high-performance cache for SAP Item Master Data.
- **Smart Sync Engine**: Bypasses SAP pagination limits by automatically looping through OData next links.
- **Upsert Logic**: Automatically updates existing items and inserts new ones based on the `ItemCode`.
- **Item Groups Mapping**: Automatically fetches and maps `ItmsGrpCod` to human-readable Group Names.
- Beautiful Administration UI with instant search, field filtering, and column sorting.

### 3. Purchase Request Module
- Create Purchase Requests seamlessly with live vendor lookups.
- **Vendor Modal**: Glassmorphism fullscreen popup to search and select vendors directly from SAP Business One.
- **Period Indicator & Series**: Dynamically restricts and fetches correct document series based on the active Period Indicator.

### 4. User & Role Management
- Granular permission system allowing access control over `Administrator` and `Purchase` modules.
- Secure authentication system built on Laravel.

## 🚀 Tech Stack
- **Backend**: Laravel 11, PHP 8.2+
- **Frontend**: TailwindCSS v4, Alpine.js, Blade Templates
- **Database**: PostgreSQL (via Docker/Laravel Sail)
- **Integration**: SAP Business One Service Layer (OData v4)

## 🛠️ Setup Instructions
1. Clone the repository.
2. Run `composer install` and `npm install`.
3. Copy `.env.example` to `.env` and set up your database credentials.
4. Run `php artisan migrate` to set up tables (`users`, `configs`, `items`, `roles`, etc.).
5. Run `php artisan db:seed` to populate the default admin user.
6. Run `npm run build` to compile the Tailwind CSS assets.
7. Login using the default admin credentials and navigate to the **Config** page to link your SAP server!

---
## 📜 Automated Changelog
*This section is automatically updated with your latest Git commits.*

- **2026-08-10 16:51**: docs: Update README with project summary and changelog
- **2026-08-10 16:55**: chore: Add database backup
- **2026-08-12 10:17**: feat: complete SAP B1 web app addon features, modal centering, layout alignment, item sync & scheduler sync-all
- **2026-08-12 10:58**: feat: add UoM master data & conversions, Chart of Accounts, Warehouses & Bins, and PR UoM/Warehouse support
