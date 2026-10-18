<!-- converted from application_development_proposal.docx -->







APPLICATION DEVELOPMENT
PROPOSAL
SITHEMR — Hospital Electronic Medical Records Platform

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Prepared for:  [Client Name]
Prepared by:  SITHEMR Development Team
Date:  July 16, 2026
Version:  1.0

# Table of Contents
- 1. Executive Summary
- 2. Technical Architecture
- 3. Core Modules
- 4. System Interface Preview
- 5. Project Quotation & Timeline
- 6. Next Steps

# 1. Executive Summary
SITHEMR is a comprehensive, multi-tenant Hospital Electronic Medical Records (EMR) platform purpose-built for Philippine hospitals. It streamlines patient care, PhilHealth compliance, billing, and clinical workflows across 15+ integrated modules.
Built on a modern Laravel 12 + Livewire 4 stack with JWT-authenticated APIs, SITHEMR delivers real-time charge capture, laboratory management, pharmacy inventory, radiology workflows, PhilHealth eClaims (CF1–CF5 / OECB / Konsulta), nursing documentation, delivery suite management, renal care (dialysis), and critical care (ICU / MICU) — all within a unified, role-based interface.
This proposal outlines the technical architecture, core modules, development estimate, and pricing for the next phase of application enhancements, refactoring, and deployment.
# 2. Technical Architecture
The platform follows a server-driven, component-based architecture leveraging Laravel's ecosystem for both backend API and frontend interactivity.
Backend Framework:  Laravel 12 with PHP 8.3+, MVC architecture, Eloquent ORM, queue support via sync/redis, and built-in scheduler for polling & batch processing.
Frontend Framework:  Livewire 4 (server-driven reactive components) + Alpine.js 3 for lightweight client interactivity. Tailwind CSS 4 provides utility-first styling, bundled via Vite 7.
Authentication:  JWT-based authentication via tymon/jwt-auth. Tokens issued on login, stored in server-side session, validated globally by a custom JwtMiddleware. Role-based permission system with middleware gates throughout all routes.
Database:  Primary: Microsoft SQL Server (sqlsrv). All billing/transactional models implement a history-column soft-delete pattern. A Qdrant vector database supports RAG/embedding features for intelligent search.
Multi-Tenancy:  Each request carries a tenant_id injected by JwtMiddleware. All core data (patients, encounters, billing) is tenant-scoped via global scopes or explicit where clauses.
API Layer:  RESTful JSON API for auth (/api/auth/*), DICOM/PACS imaging, PhilHealth eClaims submission, and OECB pipelines. XML/SOAP support via PHP SoapClient.
DevOps & Tooling:  Composer for PHP dependencies, npm/Vite for frontend assets, PHPUnit for testing, Ruff for Python linting. CI via GitHub Actions.
# 3. Core Modules

# 4. System Interface Preview

Figure 1 — SITHEMR Dashboard: system interface preview
The dashboard provides a consolidated view of key metrics: active patients, pending orders, billing summaries, and module navigation via the collapsible sidebar. Every authenticated user sees role-permission-filtered menus and data.

# 5. Project Quotation & Timeline
The following table details the proposed project phases, estimated effort, deliverables, and costs. All figures are in Philippine Pesos (PHP).

Estimated total project duration: 1–3 weeks. Timeline assumes a dedicated team of  engineers and a QA specialist. All costs are exclusive of applicable government taxes (VAT / withholding).

# 6. Next Steps
- Review and approve the proposal scope and pricing of PHP 10 per successful claims the software is free to use.
- Schedule a kick-off meeting to finalize priorities and timeline.
- Provide credentials / access to the current staging environment for audit.
- Execute the Service Agreement
- Begin Phase 1 — Discovery & Audit within 3 business days of kick-off.

──────────────────────────────────────────────────

Thank you for considering SITHEMR for your healthcare technology needs.
We look forward to partnering with you.
| Module | Description |
| --- | --- |
| Patient Registration & Records | Demographics, PhilHealth membership linkage, medical history, document attachments, and certificate generation. |
| OPD Consultations | Outpatient visit management, encounter types, referral sources, chief complaints, diagnosis, and orders. |
| Emergency Room (ER) | Triage with acuity levels, ER zones/beds, OECB (PhilHealth Outpatient Emergency Care Benefit) workflow, and printable EECL forms. |
| Admission & Wards | Inpatient admission, ward/room management, bed transfers, and discharge planning across ICU, MICU, and general wards. |
| Laboratory | Full lab workflow: test catalog, panels, specimen types, instruments, reference ranges, order dispensing, and results entry with validation. |
| Radiology / Imaging | Modalities, body parts, exam types, order dispensing, PACS/DICOM integration, and result reporting. |
| Pharmacy & Supply Chain | Drug master file, batch tracking, inventory management, purchase orders, stock adjustments, and dispensing (pharmacy, CSR/supply). |
| Billing & Revenue | Real-time charge capture, patient ledger, invoice generation, payment posting, fund-source tracking, and statement-of-account PDFs. |
| PhilHealth eClaims | CF1–CF5 form generation, ESOA, EECL summaries, batch submission, XML construction, cloud upload, and status polling. |
| Nursing | Nursing charting and clinical documentation. |
| Obstetrics (OB) | OB consultations, fetal presentations, contraceptive counseling, and delivery management. |
| Delivery Suite | DR scheduling, delivery room management, types, complications, and neonatal outcome tracking. |
| Renal Care | Dialysis type definitions and dialysis session management with integrated multi-department ordering (lab, pharmacy, radiology, etc.). |
| Operating Room (OR) | OR scheduling and room management. |
| Critical Care (ICU / MICU) | ICU ward/bed management and MICU patient tracking with assessment parameters. |
| YAKAP / Konsulta | PhilHealth Konsulta package: empanelment, FPE records, screenings, capitation, and first-patient-encounter workflow. |
| Doctors Portal | Physician dashboard with patient charts and encounter histories. |
| Phase | Est. Hours | Deliverables | Cost (PHP) |
| --- | --- | --- | --- |
| Phase 1 — Discovery & Audit | 40 | Codebase audit, architecture review, documentation gap analysis |  |
| Phase 2 — Core Refactoring | 120 | History-column soft-delete hardening, query optimization, billing engine cleanup |  |
| Phase 3 — UI / UX Enhancements | 80 | Livewire component modernization, responsive layout, accessibility pass |  |
| Phase 4 — Lab Module Deepening | 60 | Reference range flagging, result validation workflow, print templates |  |
| Phase 5 — PhilHealth eClaims v2 | 100 | OECB pipeline hardening, CF1–CF5 auto-population, batch reliability |  |
| Phase 6 — QA & Testing | 60 | Unit / feature / E2E tests, load testing, security audit |  |
| Phase 7 — Deployment & Go-Live | 40 | Environment setup, CI/CD pipeline, data migration, training, a 1 time payment | ₱250,000 |
| TOTAL | 500 |  | ₱250,000 |