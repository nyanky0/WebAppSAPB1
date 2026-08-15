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
- **2026-08-12 11:02**: refactor: clean up seeders to rely on live SAP sync for master data, refresh database dump
- **2026-08-12 11:09**: fix: add popup loading modal to all master data sync buttons and make UoM/COA sync endpoint calls resilient
- **2026-08-12 11:19**: fix: add max_retries config setting, page chunk transactions for COA/Items/Taxes, and extract default UoMs for Item Groups
- **2026-08-12 11:25**: fix: remove leftover uncommitted outer DB::beginTransaction() in ItemController causing items to be rolled back
- **2026-08-12 11:30**: feat: add per_page dropdown selection (20, 50, 100) to Items, Business Partners, COA, and Taxes pages
- **2026-08-12 11:47**: feat: add Item vs Service DocType, Dimensions & Cost Centers Master Data, Cost Center Line Integration, and fix Vendor selection JS
- **2026-08-12 12:13**: feat: add all new master data pages to Roles & Access Control matrix and update Super Admin permissions
- **2026-08-12 12:20**: fix: update SAP logo with new base64 image asset
- **2026-08-12 12:22**: fix: restore working SVG vector SAP logo asset
- **2026-08-12 12:28**: fix: replace logo with official Wikimedia vector SAP 2011/2014 logo
- **2026-08-12 12:33**: fix: prioritize DimensionDescription over DimensionName when syncing Dimensions from SAP
- **2026-08-12 13:23**: fix: hide system dummy cost centers (Centr_z%) from Cost Centers list and Purchase Request dropdowns
- **2026-08-12 13:32**: feat: default Taxes page to filter by Active taxes first, with options for Locked and All Statuses
- **2026-08-12 13:41**: feat: add SAP Withholding Tax (WTax) Master Data module with sync, active/inactive filters, and role permissions
- **2026-08-12 14:20**: feat: add Purchase Order module, PR to PO Copy To workflow with base/target document linkage matching SAP B1 v10 HANA
- **2026-08-12 15:35**: feat: add start.bat, stop.bat, and app-control.bat scripts to manage application lifecycle
- **2026-08-12 15:48**: fix: reduce login page logo sizes and add WSL support to start/stop batch scripts
- **2026-08-14 10:15**: feat: add SAP Branches (b1s/v2/Branches) Master Data module with sync, status filters, and role permissions
- **2026-08-14 11:16**: feat: add Approval Engine, Purchase Requisition redesign with Duplicate & stock tracking, Purchase Quotations, and logo resize
- **2026-08-14 11:29**: fix: resolve Approval Stage modal backdrop, format terms into horizontal table list, and add customizable per-user dashboard
- **2026-08-14 11:32**: fix: correct namespace syntax backslash in DashboardController.php
- **2026-08-14 11:34**: fix: add status column to purchase_requests migration and safely query metrics in DashboardController
- **2026-08-14 11:36**: feat: redesign dashboard widgets into SAP B1 HANA Fiori modular box-by-box grid cards
- **2026-08-14 11:39**: refactor: configure Purchase Order as the exclusive transaction document syncing into SAP
- **2026-08-14 11:41**: config: set APP_DEBUG=false and APP_ENV=production in environment configuration
- **2026-08-15 11:27**: config: restore APP_DEBUG=true and APP_ENV=local in environment configuration
- **2026-08-15 11:30**: security: add rate limiting brute-force protection and failed login audit logging to AuthController
- **2026-08-15 11:32**: feat: add grouped Tailwind CSS component utilities and cn() classname grouping helper
- **2026-08-15 11:36**: refactor: decouple SAP Service Layer communication into dedicated SapServiceLayerController and SapServiceLayerManager
- **2026-08-15 11:37**: fix: correct namespace backslash in PurchaseOrderController
- **2026-08-15 11:38**: fix: update syncBranches column mapping to code in SapServiceLayerController
- **2026-08-15 11:47**: feat: centralize all SAP Service Layer master data integrations and add debounced navigation search
- **2026-08-15 11:56**: feat: enhance sidebar search UI (right icon, match highlight, auto-expand matching folders, reset on clear) and create PROJECT_BRAIN.md base knowledge file
- **2026-08-15 13:03**: style: add spacer padding and subtle border separator between search icon and input text
- **2026-08-15 13:06**: style: separate search input box and search logo button into distinct elements with flex gap space
- **2026-08-15 13:09**: fix: resolve unmatched brace in PurchaseOrderController and update PHP 8.4 nullable type hints
- **2026-08-15 13:11**: style: update web application title to IBT Request Fulfillment across sidebar, login page, and browser tab
- **2026-08-15 13:16**: feat: add 'Sync Everything From SAP' 1-click bulk master data synchronization button with live task counter and progress bar
- **2026-08-15 13:19**: refactor: consolidate all table migrations into clean primary creation files and reset database schema fresh
- **2026-08-15 13:22**: style: move 'Sync Everything From SAP' button directly inside SAP B1 Config card without box panel
- **2026-08-15 13:24**: feat: disable Save Configuration button when form values match saved database state
- **2026-08-15 13:30**: style: update disabled Save Configuration button visual style with lock icon and grey container matching disabled buttons
