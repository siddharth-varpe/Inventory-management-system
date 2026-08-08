# StockManager Enterprise ERP — Development Roadmap

> **Strategic Development Plan**: Categorized project milestones and upcoming modules.

---

## 1. Completed Modules & Capabilities (Phase 1 Base)
- [x] **Master Administration & Settings**: Company hierarchy, branches, departments, RBAC, settings, audit logs.
- [x] **Manage Stock Catalog**: Product catalog, attributes, units, taxes, categories, brands, opening stock, adjustments, batches, barcodes.
- [x] **Sales & CRM Portal**: Lead pipeline, lead-to-customer conversion, commercial quotations, GST calculation, sales orders, stock reservation.
- [x] **Organize Stock (WMS)**: Unified Pick & Pack fulfillment station, barcode verification, ₹10k exception threshold, put-away tasks, storage explorer.
- [x] **Order Supplies (Procurement)**: Suppliers, requisitions, PO creation & shipment tracking, GRN receiving, WAC updates, 3-way invoice matching.
- [x] **Transport Control Tower**: Fleet management, custody acceptance, 9-point verification checklist, manifest generation, trip dispatch, auto resource release.
- [x] **Driver Execution Terminal**: Mobile driver desk, live delivery status transitions (9 statuses), CRM & stock update on delivery.
- [x] **Customer Communication Engine (CCE)**: Document notification engine, recipient validation, status machine, communication audit.

---

## 2. In Progress / Maintenance
- [ ] **CLI Environment Integration**: Ensure system PATH contains PHP, Composer, and Node.js for local development CLI commands.
- [ ] **Domain Unit Test Expansion**: Expand `tests/Unit/` coverage across Domain Execution Engines.

---

## 3. Next Recommended Modules
- [ ] **External REST API Integration Layer (`/api/v1`)**: Expose stateless endpoints for mobile apps and third-party ERP integrations.
- [ ] **Accounts Receivable & Payable (AR/AP) Billing Module**: Full financial invoice ledger integrated with delivery closures and 3-way matches.

---

## 4. Future Modules & Enhancements
- [ ] **Real-Time GPS & Driver Location Tracking**: WebSocket/Pusher integration for live driver trip tracking on map interfaces.
- [ ] **Supplier Interactive Bidding Portal**: Portal interface allowing suppliers to respond directly to RFQs online.
