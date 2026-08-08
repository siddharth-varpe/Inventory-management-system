# StockManager Enterprise ERP — Immutable Development Rules

> **Strict Architectural Guardrails**: Mandatory development guidelines for all future features.

---

1. **Preserve Existing Functionality**: Existing, working modules must never be rewritten or discarded.
2. **Single Source of Truth**: Never duplicate an existing entity (e.g., Products, Customers, Orders). All portals read/write to the shared database ledger.
3. **Immutable Enterprise Order Identifiers**: Enterprise Order IDs (`SO-YYYY-XXXXX`, `PO-YYYY-XXXXX`, `MAN-YYYY-XXXXXX`) must remain immutable across cross-module workflows.
4. **Decoupled Cross-Module Communication**: Portals must interact via `EnterpriseEventBus` or explicit Domain Engines. Direct cross-database hacking is prohibited.
5. **No Production Fake Data**: Live business logic must use actual database ledgers, true GST rates, and validated stock counts.
6. **No Hardcoded Business Thresholds**: Configurable parameters (e.g., ₹10,000 WMS loss threshold, ₹5,00,000 quotation approval threshold) must come from configuration or database settings.
7. **No Destructive Database Operations**: Never execute `migrate:fresh` or `db:wipe` without explicit authorization.
8. **Mandatory Input Validation**: Every controller request must validate input data before passing it to domain engines.
9. **UI Design System Consistency**: Frontend modifications must respect existing Blade components and vanilla CSS design tokens.
10. **Explicit State Transitions**: State changes must go through `WorkflowStateMachine` or domain engine transition methods.
11. **Immutable Auditability**: All critical data mutations, custody acceptances, checklist completions, and status changes must be logged to `AuditLog`.
12. **Atomic Financial Operations**: Stock updates, financial totals, and multi-table writes must execute inside `DB::transaction()`.
