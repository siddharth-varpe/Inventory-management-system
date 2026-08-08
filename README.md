# StockManager Enterprise ERP

[![Laravel Framework](https://img.shields.io/badge/Laravel-11.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP Engine](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![Database Engine](https://img.shields.io/badge/Database-SQLite%2FMySQL-003545?style=for-the-badge&logo=sqlite&logoColor=white)](https://www.sqlite.org)
[![PHPUnit Test Suite](https://img.shields.io/badge/Tests-103%2F103%20PASSED-28a745?style=for-the-badge&logo=phpunit&logoColor=white)](https://phpunit.de)
[![System Status](https://img.shields.io/badge/Status-Production--Ready-success?style=for-the-badge)](https://github.com/siddharth-varpe/Inventory-management-system)

StockManager Enterprise ERP is a modern, high-performance fleet management, logistics operations, warehouse fulfillment, and inventory control application built with Laravel 11 and enterprise UX/UI standards.

---

## 🚀 Key Modules & System Architecture

### 1. 🚛 Transport Department & Fleet Operations
- **Delivery Orders Master**: Responsive 3-column vertical card grid layout displaying order references, customer profiles, destination address, assigned drivers & vehicles, and expected delivery dates.
- **Driver Master Management**: Complete driver lifecycle (active, available, assigned, on delivery, suspended, inactive) with license expiry tracking and employee code binding.
- **Vehicle Master Management**: Vehicle load capacity ($kg$) and volume ($m^3$) enforcement, operational trip status (`available`, `on_trip`, `under_maintenance`, `breakdown`), and maintenance history.
- **Resource Assignment Engine**: Concurrency-safe atomic resource assignment with live capacity validation against order weight/volume limits.
- **Operational Dispatch Control**: Gate pass notes input, seal verification, automated dispatch ID (`DSP-YYYY-XXXXXX`) generation, and live delivery trip creation.

### 2. 📦 Organize Stock & Warehouse Fulfillment
- **Picking Queue**: FIFO and priority-based task queue (`urgent` > `high` > `medium` > `low`).
- **Item Verification Checklist**: Mandatory item quantity and SKU verification before sealing.
- **Packaging & Sealing**: Weight calculation, box count tracking, and seal tag assignment (`Seal & Ready to Dispatch`).

### 3. 💼 Sales & CRM Synchronization
- **Lead Pipeline**: Lead creation, conversion, and customer association.
- **Sales Order Generation**: Seamless integration between sales orders and warehouse picking queues.
- **Automated Transport Intake**: Automated handover from sealed warehouse orders to transport delivery orders.

---

## 🔄 Canonical 14-Step ERP Workflow Chain

$$\text{CRM Lead} \longrightarrow \text{Sales Order} \longrightarrow \text{Organize Stock} \longrightarrow \text{Pick \& Pack} \longrightarrow \text{Seal \& Ready to Dispatch} \longrightarrow \text{Transport Order Sync} \longrightarrow \text{Resource Assignment} \longrightarrow \text{Dispatch} \longrightarrow \text{Active Delivery} \longrightarrow \text{Driver Terminal} \longrightarrow \text{Delivery Completion} \longrightarrow \text{History}$$

1. **CRM Lead Creation**: Register lead details in `crm_leads`.
2. **Sales Order Generation**: Create sales order `SO-YYYY-XXXXXX` linked to customer.
3. **Warehouse Picking**: Assign picking task (`assigned` $\rightarrow$ `picking` $\rightarrow$ `picked`).
4. **Item Verification & Packing**: Verify items and assign seal tag (`Seal & Ready to Dispatch`).
5. **Transport Task Auto-Sync**: Automatically create transport task `TRN-YYYY-XXXXXX`.
6. **Driver & Vehicle Assignment**: Assign eligible driver and vehicle with weight/volume capacity validation.
7. **Dispatch Execution**: Release shipment under canonical dispatch ID `DSP-YYYY-XXXXXX`.
8. **Live Active Delivery**: Transition driver to `ON DELIVERY` and vehicle to `ON TRIP`.
9. **Driver Terminal Handover**: Digital handover & proof-of-delivery (POD) verification.
10. **Trip Closure & History**: Mark order as `completed` and record immutable audit logs.

---

## 🛠️ Technology Stack

- **Backend Framework**: Laravel 11.x (PHP 8.2+)
- **Domain Layer**: Clean Architecture (Domain Engines for Transport, Sales, Warehouse)
- **Frontend Architecture**: Blade Templates, Vanilla CSS Design System, Bootstrap 5
- **Database Support**: SQLite, MySQL 8.0+
- **Test Automation**: PHPUnit 10.x (103 Feature & Unit Tests)

---

## ⚙️ Installation & Setup Guide

### Prerequisites
- PHP 8.2 or higher
- Composer 2.x
- Node.js & NPM (optional for frontend assets)

### Step 1: Clone Repository
```bash
git clone https://github.com/siddharth-varpe/Inventory-management-system.git
cd Inventory-management-system
```

### Step 2: Install PHP Dependencies
```bash
composer install
```

### Step 3: Configure Environment
Copy the example `.env` file and set up configuration:
```bash
cp .env.example .env
php artisan key:generate
```

### Step 4: Run Migrations & Seed Database
```bash
php artisan migrate --seed
```

### Step 5: Start Local Development Server
```bash
php artisan serve
```
Access the application in your browser at `http://127.0.0.1:8000`.

---

## 🧪 Running Automated Tests

Run the complete 103-test PHPUnit suite to verify system stability and workflow integrity:

```bash
php artisan test
```
or via PHPUnit directly:
```bash
vendor/bin/phpunit
```

---

## 📁 Core Directory Structure

```
Inventory-management-system/
├── app/
│   ├── Domain/                 # Domain Engines (TransportPlanningEngine, DispatchExecutionEngine)
│   ├── Http/
│   │   ├── Controllers/        # TransportController, ProductController, SalesOrderController
│   │   └── Middleware/         # AutoAuthenticate, Role/Permissions Middleware
│   └── Models/                 # Eloquent Models (TransportRequest, Driver, Vehicle, SalesOrder)
├── database/
│   ├── migrations/             # System Migration Files
│   └── seeders/                # Enterprise Seeder Data
├── resources/
│   └── views/                  # Blade Templates & Component Layouts
│       └── transport/          # Delivery Orders, Driver Master & Vehicle Master Views
└── tests/
    └── Feature/                # Automated System & Workflow Integration Tests
```

---

## 📄 License & Attribution

Developed for **StockManager Enterprise ERP**. Distributed under the MIT License.
