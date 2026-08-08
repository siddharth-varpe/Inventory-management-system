# StockManager Enterprise ERP — Comprehensive Architecture & Technical Specification

> **System Source of Truth**: Permanent architecture specification for StockManager Enterprise ERP.

---

## 1. Project Overview
StockManager Enterprise ERP is an enterprise-grade resource planning system designed to manage the full lifecycle of commercial operations: from CRM lead acquisition and quotation pricing, to sales order management, stock reservation, warehouse fulfillment (WMS), procurement and 3-way invoice matching, transport fleet dispatch, and mobile driver execution. The application enforces single-source-of-truth (SSOT) inventory ledgers and role-based access control (RBAC).

---

## 2. Technology Stack
- **Backend Framework**: Laravel (PHP 8.1+ / 8.2+) using Eloquent ORM.
- **Architecture Pattern**: Domain-Driven Design (DDD) with independent portal boundaries connected via an Event-Driven Orchestration Engine.
- **Frontend Architecture**: Server-Side Rendered Blade Templates, Vanilla CSS Design System with dark mode support, and Vanilla JS / Ajax dynamic workspaces.
- **Database Engine**: MySQL / PostgreSQL / SQLite compatible via standard SQL migrations (66 Migrations, 71 Eloquent Models).
- **Domain State & Events**: Event Bus (`EnterpriseEventBus`), Orchestration Engine (`EnterpriseOrchestrationEngine`), State Machine (`WorkflowStateMachine`), Real-time Stock Sync (`EnterpriseSyncEngine`).
- **Asynchronous Processing**: Laravel Queues (`GenerateProcurementPdfJob`, `SendSupplierPoEmailJob`, `RecalculateVendorPerformanceJob`).

---

## 3. Folder Structure
```
Inventory-management-system/
├── app/
│   ├── Core/                           # Infrastructure & base classes
│   ├── Domain/                         # DDD Business Core
│   │   ├── Audit/                      # Enterprise Audit Coordinator
│   │   ├── Communication/              # Customer Communication Engine (CCE)
│   │   ├── Contracts/                  # Domain Interfaces
│   │   ├── DTO/                        # Domain Event Data Transfer Objects
│   │   ├── EventBus/                   # Enterprise Event Bus (Pub/Sub)
│   │   ├── Events/                     # Domain Events
│   │   ├── Notifications/              # Enterprise Notification Engine
│   │   ├── Orchestrator/               # Central Orchestration Engine
│   │   ├── Procurement/                # Procurement Engines & Services
│   │   ├── Sales/                      # Sales, Pricing & Reservation Engines
│   │   ├── StateMachine/               # Domain State Machines
│   │   ├── Synchronization/            # Real-time Stock & Occupancy Sync Engine
│   │   ├── Tasks/                      # WMS Automated Task Generator
│   │   ├── Transport/                  # Fleet & Dispatch Execution Engines
│   │   └── Warehouse/                  # WMS & Fulfillment Station Engines
│   ├── Http/Controllers/               # Root Controllers & Central APIs
│   ├── Models/                         # Eloquent Models (71 active models)
│   ├── Modules/                        # Modular Portal Controllers (OrganizeStock, Procurement, Sales)
│   ├── Providers/                      # Service Providers (Bindings & Singletons)
│   ├── Repositories/                   # Repository Pattern Contracts & Eloquent
│   └── Services/                       # Application Services
├── config/                             # Laravel System Configuration
├── database/                           # Migrations (66), Seeders & Factories
├── docs/                               # Permanent Project Documentation Memory
│   ├── PROJECT_ARCHITECTURE.md
│   ├── PROJECT_CONTEXT.md
│   ├── ROADMAP.md
│   └── DEVELOPMENT_RULES.md
├── resources/                          # Blade Views & UI Blade Components
│   └── views/components/           # 28 Reusable UI Blade Components
├── routes/                             # Modular Route Files (web, sales, procurement, organize-stock, transport, driver, api)
└── tests/                              # Unit & Feature Test Suites
```

---

## 4. Backend Architecture
The backend follows Domain-Driven Design (DDD) principles combined with the Repository Pattern. 
- Core domain logic resides in `app/Domain/`, completely decoupled from HTTP controllers.
- `EnterpriseOrchestrationServiceProvider` binds domain contracts to concrete implementations (`OrchestratorEngineInterface`, `EventBusInterface`, `SyncEngineInterface`, `StateMachineInterface`, `TaskGeneratorInterface`, `AuditCoordinatorInterface`).
- Transactions are managed atomically via `DB::transaction()` inside domain services to prevent data corruption.

---

## 5. Frontend Architecture
- Server-side Blade rendering enriched with reusable UI components located in `resources/views/components/`.
- Dynamic UI updates (e.g., live picking verification, barcode validation, transport status updates) use lightweight AJAX calls returning structured JSON payloads.
- Modular sidebars (`stock-sidebar`, `sales-sidebar`, `procurement-sidebar`, `organize-sidebar`) reflect current portal context.

---

## 6. Database Architecture
- 66 migration files defining 71 Eloquent Models.
- Referential integrity is enforced with foreign key constraints.
- Financial fields use high-precision decimals (`decimal(12,2)` / `decimal(15,2)`).
- Inventory tracking is split into `physical_stock`, `reserved_stock`, and `available_stock` on the `products` table.
- Warehouse location occupancy (`current_occupancy`) is recalculated dynamically against capacity limits.

---

## 7. Authentication Architecture
- Built on standard Laravel Authentication middleware (`auth`, `verified`).
- Password hashing uses bcrypt / Argon2.
- User sessions are tracked in standard Laravel session stores.
- Login history is tracked via `PortalLoginHistory`.

---

## 8. Authorization & Role-Based Access Control (RBAC)
- Multi-layered RBAC: Spatie-style roles (`roles`, `permissions`, `model_has_roles`, `role_has_permissions`).
- Fine-grained portal access is controlled via `portal_modules`, `portal_permissions`, and `user_portal_access`.
- Middleware checks protect portal route groups (`/sales`, `/procurement`, `/organize-stock`, `/transport`, `/driver`).

---

## 9. API Architecture
- Web AJAX APIs: Stateful, JSON-returning endpoints for live barcode validation, exception reporting, status transitions, and timeline logging.
- External REST API: Stateless endpoints under `/api/v1` throttled at 60 req/min (`routes/api.php`).

---

## 10. State Management
- Database Single Source of Truth (SSOT).
- Domain State Machine ([WorkflowStateMachine](file:///c:/Users/shara/Desktop/Inventory-management-system/app/Domain/StateMachine/WorkflowStateMachine.php)) enforces allowed transitions:
  `pending` $\rightarrow$ `assigned`/`in_progress`/`cancelled` $\rightarrow$ `completed`/`failed` $\rightarrow$ `verified`/`closed`.
- Communication State Machine ([CommunicationStateMachine](file:///c:/Users/shara/Desktop/Inventory-management-system/app/Domain/Communication/CommunicationStateMachine.php)) manages CCE record lifecycles (`draft` $\rightarrow$ `prepared` $\rightarrow$ `dispatched` $\rightarrow$ `delivered`/`failed` $\rightarrow$ `archived`).

---

## 11. Shared Components
28 reusable Blade components in `resources/views/components/`:
- UI Indicators: `status-badge`, `stock-badge`, `priority-badge`, `kpi-card`, `empty-state`, `error-state`.
- Input & Controls: `search-bar`, `searchable-select`, `data-table`, `export-button`, `import-button`.
- Containers & Layouts: `master-detail-layout`, `linked-status-modals`, `modal-confirmation`, `modal-delete`, `universal-timeline`.
- Domain Widgets: `warehouse-tree` (5-level storage hierarchy), `location-card`, `location-selector`, `task-card`, `checklist`, `portal-card`.

---

## 12. Shared Services
- Domain Services: `EnterpriseOrchestrationEngine`, `EnterpriseEventBus`, `EnterpriseSyncEngine`, `TaskGenerator`, `WarehouseExecutionEngine`, `FulfillmentStationEngine`, `TransportManagementEngine`, `DispatchExecutionEngine`, `DriverExecutionEngine`, `QuotationService`, `SalesOrderService`, `ReservationEngine`, `SendGoodsConnector`, `CrmAutomationEngine`, `ProcurementOrchestratorService`, `CommunicationEngineService`.
- Application Services: `BaseService`, `EloquentBaseRepository`, `BarcodeService`, `StockImportService`, `StockExportService`, `SalesGstCalculator`, `CustomerPricingService`.

---

## 13. Business Logic Summary
- **Stock Reservation**: Sales Order creation/approval reserves stock automatically via `ReservationEngine`.
- **WMS Short Pick Threshold**: Missing items costing > **₹10,000** escalate to `manager_approval_required`. Under ₹10,000, inventory is automatically decremented and adjusted.
- **High-Value Quotation Rule**: Quotations $\ge$ **₹5,00,000** or with $\ge$ **20% discount** require manager approval (`pending_approval`).
- **Procurement 3-Way Match**: Reconciles Purchase Order, Goods Receipt Note (GRN), and Supplier Invoice.
- **Pre-Dispatch Verification**: 9-point checklist must be 100% completed before manifest issuance (`MAN-YYYY-XXXXXX`).
- **Automatic Fleet Release**: Completing/closing a trip automatically releases `Vehicle` and `Driver` to `available`.

---

## 14. Module Architecture
- **Manage Stock**: `/stock/*` — Catalog, categories, brands, units, taxes, attributes, opening stock, adjustments, batches, barcodes.
- **Sales & CRM**: `/sales/*` — Leads, customer master, quotations, GST calculation, sales orders, stock reservation.
- **Organize Stock (WMS)**: `/organize-stock/*` — Pick & pack fulfillment station, put-away tasks, locations, transfers, exceptions.
- **Order Supplies (Procurement)**: `/procurement/*` — Suppliers, requisitions, POs, inbound shipments, GRN receiving, WAC updates, 3-way matching.
- **Transport**: `/transport/*` — Fleet management, transport intake, checklist verification, manifest generation, trip dispatch.
- **Driver Terminal**: `/driver/*` — Mobile driver desk, delivery status execution, CRM/stock update on delivery.
- **CCE**: Customer Communication Engine — Central communication ledger (`COM-YYYY-XXXXXX`).

---

## 15. Cross-Module Communication
Modules communicate asynchronously via `EnterpriseEventBus` or synchronously via domain engine injection (`SendGoodsConnector`, `FulfillmentStationEngine`, `TransportManagementEngine`). Direct database coupling between modules is strictly forbidden.

---

## 16. Enterprise Order Lifecycle
1. Lead stage updated to `won` $\rightarrow$ Customer Master created ([CrmAutomationEngine](file:///c:/Users/shara/Desktop/Inventory-management-system/app/Domain/Sales/CrmAutomationEngine.php)).
2. Commercial Quotation generated, approved, and sent via CCE.
3. Quotation converted to Sales Order (`SO-YYYY-XXXXX`).
4. Stock reserved via `ReservationEngine`; `SendGoodsConnector` triggers WMS Picking Task + Transport Request.
5. WMS operator verifies barcodes & seals package (`seal_ready`).
6. Sales order advances to `ready_for_dispatch`; Transport intake accepts custody.
7. Fleet assigned, 9-point checklist verified, Manifest issued (`MAN-YYYY-XXXXXX`).
8. Driver accepts trip on Driver Terminal, updates delivery status (`delivered`).
9. Physical stock decremented, Sales Order status set to `dispatched`/`delivered`, CRM activity logged.
10. Transport trip closed; Vehicle and Driver released to `available`.

---

## 17. Inventory Lifecycle
Opening Stock $\rightarrow$ Physical / Reserved / Available Ledger $\rightarrow$ Reservation on Sales Order $\rightarrow$ Physical Decrement on Fulfillment Seal/Dispatch $\rightarrow$ Automated WMS Put-Away on Inbound GRN Receipt $\rightarrow$ WAC recalculation $\rightarrow$ Expiry Monitoring & Stock Adjustments.

---

## 18. Procurement Lifecycle
Purchase Requisition (`PR-YYYY-XXXXX`) $\rightarrow$ Approval Engine (RFQ vs Direct PO) $\rightarrow$ Purchase Order (`PO-YYYY-XXXXX`) $\rightarrow$ Supplier Inbound Dispatch $\rightarrow$ Arrival $\rightarrow$ Goods Receipt Note (`GRN-YYYY-XXXXX`) $\rightarrow$ Automated Put-Away Task Generation & WAC Update $\rightarrow$ 3-Way Invoice Match (`INV-YYYY-XXXXX`).

---

## 19. Warehouse Lifecycle
Inbound Storage Request $\rightarrow$ Put-Away Location Assignment $\rightarrow$ Occupancy Increment $\rightarrow$ Outbound Picking Task (`PICK-YYYY-XXXXX`) $\rightarrow$ Fulfillment Station Barcode Scanning $\rightarrow$ Short Pick Exception Handling $\rightarrow$ Package Sealing $\rightarrow$ Handoff to Transport.

---

## 20. CRM Lifecycle
Lead Capture $\rightarrow$ Opportunity Pipeline Stage Movement $\rightarrow$ Lead Won Conversion $\rightarrow$ Customer Account Setup $\rightarrow$ Activity/Meeting/Followup Loggers $\rightarrow$ Quotation History $\rightarrow$ Order Fulfillment History tracking.

---

## 21. Transport Lifecycle
Warehouse Handoff Intake $\rightarrow$ Driver & Vehicle Allocation $\rightarrow$ Trip Creation $\rightarrow$ Custody Acceptance $\rightarrow$ Mandatory 9-Point Verification Checklist $\rightarrow$ Manifest Issuance $\rightarrow$ Departure Dispatch $\rightarrow$ Driver Execution $\rightarrow$ Delivery Confirmation $\rightarrow$ Fleet Resource Auto-Release $\rightarrow$ Operational Analytics.

---

## 22. Billing Lifecycle
Order Delivery $\rightarrow$ Audit Log Notification for Invoicing Eligibility $\rightarrow$ Supplier 3-Way Match Verification for AP $\rightarrow$ Future AR/AP Ledger Integration.

---

## 23. Notification Architecture
- System Notifications (`notifications` table).
- Role and channel notification dispatcher (`EnterpriseNotificationEngine`).
- CCE Communication records (`COM-YYYY-XXXXXX`) for customer notifications.

---

## 24. Audit Logging
- System Activity Log ([ActivityLog](file:///c:/Users/shara/Desktop/Inventory-management-system/app/Models/ActivityLog.php)): User UI actions.
- Immutable Audit Log ([AuditLog](file:///c:/Users/shara/Desktop/Inventory-management-system/app/Models/AuditLog.php)): System data mutations, status changes, custody acceptance, checklist verifications, and manifest generations.

---

## 25. Error Handling
- Custom Exceptions (`DomainException`, `InvalidArgumentException`).
- WMS Exception Center (`WarehouseException` model) for reporting short picks and storage discrepancies.

---

## 26. Testing Architecture
- Feature Tests: `tests/Feature/` (`AuthTest`, `FoundationTest`, `ManageStockPortalTest`).
- Unit Tests: `tests/Unit/` (`ExampleTest.php`).
- Extension target: Unit test suites for all Domain execution engines.

---

## 27. Deployment Architecture
- Standard Laravel web server configuration (Nginx / Apache + PHP-FPM).
- Database: MySQL 8.0+ / PostgreSQL 14+.
- Background Worker: `php artisan queue:work` for PDF, email, and metric recalculations.

---

## 28. Environment Configuration
- Configuration via `.env`.
- Key parameters: `APP_KEY`, `APP_URL`, `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`.

---

## 29. Known Issues
- Absence of CLI tools (`php`, `composer`, `node`, `git`) in system PATH on current environment host.
- API v1 in `routes/api.php` is incomplete.

---

## 30. Incomplete Features
- External REST API endpoints under `/api/v1`.
- Comprehensive domain engine unit tests.
- Dedicated Accounts Receivable / Billing module.
- Procurement RFQ supplier bidding portal.

---

## 31. Technical Debt
- Minor duplication of legacy route fallbacks in `routes/organize-stock.php`.
- Test coverage concentrated in feature integration rather than domain unit level.

---

## 32. Future Extension Points
- Dedicated Accounts Receivable & Payable (AR/AP) Financial Module.
- Mobile Application REST API integration layer (`/api/v1`).
- Real-time WebSocket notifications for driver location tracking.
- Interactive RFQ vendor quotation submission portal.
