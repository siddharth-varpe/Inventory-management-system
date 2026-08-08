# StockManager Enterprise ERP — Project Development Memory & Context

> **System Memory File**: Current project state, business rules, identifiers, and architectural guidelines.

---

## 1. Current Project State
- **Core Framework**: Laravel Enterprise ERP with Domain-Driven Design (DDD) architecture.
- **Portals Implemented**:
  1. Central Admin & Settings (`/`, `/settings`) — Completed
  2. Manage Stock Catalog (`/stock/*`) — Completed
  3. Sales & CRM Portal (`/sales/*`) — Completed
  4. Organize Stock WMS (`/organize-stock/*`) — Completed
  5. Order Supplies Procurement (`/procurement/*`) — Completed
  6. Transport Logistics Control Tower (`/transport/*`) — Completed
  7. Driver Terminal (`/driver/*`) — Completed
  8. Customer Communication Engine (CCE Phase 1-3) — Completed

---

## 2. Important Immutable Identifiers
- **Customer Code**: `CUST-YYYY-XXXX`
- **Product SKU**: `SKU-XXXX`
- **Quotation Number**: `QTN-YYYY-XXXXX`
- **Sales Order Number**: `SO-YYYY-XXXXX`
- **Purchase Requisition Number**: `PR-YYYY-XXXXX`
- **Purchase Order Number**: `PO-YYYY-XXXXX`
- **Goods Receipt Note Number**: `GRN-YYYY-XXXXX`
- **Supplier Invoice Number**: `INV-YYYY-XXXXX`
- **Picking Task Number**: `PICK-YYYY-XXXXX`
- **Storage Request Number**: `REQ-PA-XXXXXX`
- **Warehouse Exception Number**: `EXC-YYYY-XXXXX`
- **Transport Task / Request Number**: `TRN-YYYY-XXXXXX`
- **Dispatch Manifest Number**: `MAN-YYYY-XXXXXX`
- **Communication Record Number**: `COM-YYYY-XXXXXX`

---

## 3. Mandatory Business Rules & Thresholds
1. **WMS Short Pick Loss Threshold**: Financial loss exceeding **₹10,000** triggers `manager_approval_required`. Losses below ₹10,000 auto-adjust stock.
2. **Quotation Manager Approval Rule**: Quotation grand total $\ge$ **₹5,00,000** or max discount $\ge$ **20%** triggers `pending_approval`.
3. **Mandatory 9-Point Pre-Dispatch Verification**: Vehicle, packages, labels, documents, door seals, driver docs, loading, and supervisor approval must all be verified before manifest generation.
4. **Automatic Fleet Resource Release**: Closing or completing a transport trip automatically releases `Vehicle` and `Driver` to `available` status.

---

## 4. Development Conventions
- **No Direct Database Coupling**: Cross-portal actions must go through `EnterpriseEventBus` or dedicated Domain Execution Engines.
- **Preserve Existing Code**: Never delete working features, models, or migrations.
- **Atomic Operations**: Wrap multi-step mutations in `DB::transaction()`.
- **Immutable References**: Enterprise Order references (`SO-YYYY-XXXXX`) must persist across WMS, Transport, Driver, and CRM portals.
