# StockManager Enterprise ERP

## Overview

StockManager is an enterprise inventory and operations management system designed to coordinate stock control, warehouse fulfillment, sales order processing, procurement, and transportation logistics from a centralized platform.

The system handles core operational workflows across multiple departments:

- **Inventory**: Stock item tracking, product categories, brands, unit costs, and reorder levels.
- **Sales & CRM**: Lead management, customer profiles, quotes, sales orders, and status tracking.
- **Warehouse**: Picking queues, item verification, packing workflows, and dispatch readiness.
- **Transport**: Delivery order management, driver assignments, vehicle master tracking, and active delivery monitoring.
- **Driver Operations**: Driver terminal workflows, delivery status updates, and proof-of-delivery records.
- **Administration**: User roles, permissions, system configuration, and audit logging.

---

## Technology Stack

- **Backend**: Laravel 11.x / PHP 8.2+
- **Database**: SQLite (default for development) / MySQL
- **Frontend**: Blade Templates, Vanilla CSS, Javascript
- **Testing**: PHPUnit 10.x

---

## Main Modules

- **Sales & CRM**: Lead tracking, customer management, and sales order creation.
- **Inventory Management**: Stock catalog, category management, and inventory tracking.
- **Warehouse Operations**: Pick & pack task processing, item verification, and shipment sealing.
- **Transport & Logistics**: Card-based transport order planning, driver assignment, and vehicle scheduling.
- **Driver Operations**: Active trip execution, delivery confirmation, and status reporting.
- **Audit & History**: Timeline history and system-wide activity logs.

---

## Setup Instructions

### Prerequisites
- PHP 8.2+
- Composer 2.x

### Quickstart

1. **Clone the repository**:
   ```bash
   git clone https://github.com/siddharth-varpe/Inventory-management-system.git
   cd Inventory-management-system
   ```

2. **Install dependencies**:
   ```bash
   composer install
   ```

3. **Configure environment**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Run migrations and seed baseline data**:
   ```bash
   php artisan migrate --seed
   ```

5. **Start local development server**:
   ```bash
   php artisan serve
   ```
   Open `http://127.0.0.1:8000` in your web browser.

---

## Running Tests

Execute the automated test suite with PHPUnit:

```bash
php artisan test
```
or
```bash
vendor/bin/phpunit
```
