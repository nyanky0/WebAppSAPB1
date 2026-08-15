# IBT Request Fulfillment WebApp - Project System Architecture & Base Brain Configuration

This document serves as the authoritative System Knowledge Base and "Brain Configuration" for the SAP B1 AddOn WebApp project. All future AI assistant chat sessions and developers MUST adhere to these architectural standards and guidelines.

---

## 1. System Technology Stack
- **Framework**: Laravel 13 (PHP 8.4)
- **Database**: PostgreSQL (`pgsql`) running in Docker container (`webappsapb1_db`)
- **Frontend / Styling**: Tailwind CSS v4 + Vanilla CSS + Alpine.js v3 (with collapse plugin)
- **Containerization**: Docker & Docker Compose (`docker-compose.yml`) running on WSL / Windows

---

## 2. Core Architectural Principles & Conventions

### A. Database Primary Keys & Foreign Keys (`uid7`)
- **Primary Key Standard**: Primary key for users is a string `uid7` (e.g. `'018f...''`).
- **Foreign Key Standard**: All foreign key references to users MUST be string `uid7` columns (e.g. `created_by`, `originator_id`, `user_id`, `approver_user_ids`).

### B. SAP Business One Service Layer Integration (`SapServiceLayerController`)
- **Centralized Controller & Manager**: ALL SAP B1 Service Layer communications MUST be routed through [`SapServiceLayerController`](file:///d:/Metrodata/AddOn/WebAppSAPB1/app/Http/Controllers/SapServiceLayerController.php) and [`SapServiceLayerManager`](file:///d:/Metrodata/AddOn/WebAppSAPB1/app/Services/SapServiceLayerManager.php).
- **No Inline Service Layer Calls in Page Controllers**: Page controllers (`PurchaseOrderController`, `ItemController`, `BusinessPartnerController`, `BranchController`, `ChartOfAccountController`, `CostCenterController`, `DimensionController`, `ItemGroupController`, `TaxController`, `UomController`, `WarehouseController`, `WithholdingTaxController`, `ConfigController`) MUST NOT write inline cURL / Service Layer request code. They MUST delegate `sync()` actions to `SapServiceLayerController`.
- **Exclusive SAP Document Sync**: **Purchase Order** (`PurchaseOrder`) is the SINGLE, EXCLUSIVE transaction document that syncs into SAP Service Layer (`/b1s/v2/PurchaseOrders`).
- **Web App Exclusive Documents**:
  - **Purchase Requisitions (`PurchaseRequest`)**: Web App exclusive (statuses `draft`, `open`, `close`, `cancel`, triggers Web App exclusive approval workflow).
  - **Purchase Quotations (`PurchaseQuotation`)**: Web App exclusive (copies from PRs, records vendor pricing/quantities).

### C. Tailwind CSS Class Grouping Standards
- **Component Classes (`resources/css/app.css`)**: Use `@layer components` to group Tailwind utility strings into clean, reusable CSS classes:
  - Buttons: `.btn-primary`, `.btn-secondary`, `.btn-danger`, `.btn-light-indigo`
  - Form Fields: `.input-field`, `.input-field-sm`, `.form-label`
  - Cards: `.card-box`, `.card-box-header`
  - Badges: `.badge-success`, `.badge-warning`, `.badge-danger`, `.badge-info`, `.badge-gray`
  - Modals: `.modal-backdrop`, `.modal-container`
  - Tables: `.table-header`, `.table-row`
- **Class Grouping Helper (`cn()`)**: Use the global `cn(...$classes)` helper in [`app/helpers.php`](file:///d:/Metrodata/AddOn/WebAppSAPB1/app/helpers.php) to conditionally join class names cleanly in Blade templates:
  ```html
  <button class="{{ cn('btn-primary', ['opacity-50 cursor-not-allowed' => $isDisabled]) }}">
  ```

### D. Web App Exclusive Approval Workflows
- **Approval Stages**: Defined in `ApprovalStage` with stage name, description, user list (`approver_user_ids`), and required approval threshold (`required_approvals`).
- **Approval Templates**: Defined in `ApprovalTemplate` with template name, description, `active` toggle, `originator_id`, `document_type`, stage flow order, and conditional terms list (`terms`).
- **Approval Decisions**: Tracked in `ApprovalRequest` and `ApprovalDecision` for user voting (`approved` / `rejected`).

### E. User Cockpit Dashboard (SAP B1 HANA Fiori Style)
- **Modular Box-by-Box Grid Layout**: Dashboard widgets rendered inside `grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6` as modular KPI cards (Pending Approvals, High Urgency PRs, PR Summary, PQ Summary, PO Summary).
- **Per-User Preferences**: User widget preferences stored in `users.dashboard_widgets` JSON column and configurable via cockpit customization modal.

### F. Interactive Button Hover & Micro-Animation Standards
- **Mandatory Hover & Click Effects**: ALL interactive buttons across the application MUST feature visual hover elevation, shadow depth, and active click scaling feedback:
  - Enabled Buttons: `hover:shadow-md hover:-translate-y-0.5 active:scale-95 transition-all cursor-pointer`
  - Disabled Buttons: `border border-gray-200 text-gray-400 bg-gray-100 cursor-not-allowed shadow-none` with lock icon indicator (`🔒`).

---

## 3. Deployment & Control Commands
- **Start App**: `.\start.bat` or `docker compose up -d`
- **Stop App**: `.\stop.bat` or `docker compose down`
- **Composer Autoload**: `docker compose exec -T backend composer dump-autoload`
- **Environment**: Configured via `.env` (`APP_DEBUG=true` in development, `APP_ENV=local`).
