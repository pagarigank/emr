<!-- converted from labrcu.docx -->

SITHEMR Application Overview
Login  ·  Laboratory  ·  Renal Care Modules

SITHEMR is a comprehensive Hospital Electronic Medical Records (EMR) platform. It is designed for Philippine hospitals, supporting multi-tenant operation with JWT-based authentication, real-time billing, PhilHealth eClaims (CF1-CF5, OECB), and full clinical workflows across 15+ modules. Below is a visual walkthrough of three key modules: Authentication (Login), Laboratory, and Renal Care.

# 1. Login / Authentication Module


The Login page provides the gateway into SITHEMR. It features a two-panel layout: a branded left panel with animated gradients, feature highlights (PhilHealth eClaims, in-patient/ER workflow, real-time billing),
# 2. Laboratory Module


The Laboratory module manages the full lab workflow — from test catalog definition to order dispensing to results entry and validation. The sidebar shows pages for managing Sections, Units, Specimen Types, Instruments, the Test Catalog, Panels, Reference Ranges, Dispensing (order fulfillment), and Results. Each page is driven by a dedicated Livewire CRUD component (e.g., LabResultsCrud, LabDispensingCrud, TestCrud). The database model layer includes LabRequest, LabRequestItem, LabTest, LabPanel, LabSection, LabUnit, LabInstrument, SpecimenType, and ReferenceRange — all with full migration history. The module integrates with billing via standard_charge fields on tests and panels, and supports result entry with reference range validation and flagging.
# 3. Renal Care Module


The Renal Care module supports hemodialysis patient management. It provides two main sub-pages: Dialysis Types (administering code/name definitions) and Dialysis Sessions (the primary operational interface). The DialysisSessionCrud Livewire component manages patient vitals (pre/post weight, blood pressure, heart rate), access type, medications, and session notes. It also serves as an integrated ordering hub — from within a dialysis session, clinicians can directly place Lab, Pharmacy, Radiology, Procedure, and CSR/Supply orders without switching contexts. Each order is automatically linked to the dialysis session and routed to the appropriate Renal Care department. All other dispensing components (LabDispensingCrud, PharmacyDispensingCrud, etc.) dispatch events back to DialysisSessionCrud for real-time order status updates.

SITHEMR — Hospital Electronic Medical Records
Document generated on July 15, 2026