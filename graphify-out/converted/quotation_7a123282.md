<!-- converted from quotation.docx -->





SITHEMR
System for Integrated Tertiary Hospital
Electronic Medical Records



BUSINESS PROPOSAL & QUOTATION

Enterprise Hospital Information System
PhilHealth eClaims 3.0 Compliant






TABLE OF CONTENTS

1. Executive Summary
2. Project Scope & Objectives
3. Technical Architecture
4. Module Breakdown & Pricing
5. Summary
6. Terms & Conditions
7. Acceptance

1. Executive Summary

SITHEMR (System for Integrated Tertiary Hospital Electronic Medical Records) is a comprehensive, enterprise-grade Hospital Information System (HIS) designed specifically for Philippine tertiary hospitals. The system provides end-to-end coverage of the entire hospital workflow — from patient registration and clinical encounters through ancillary services, billing, and PhilHealth eClaims submission.
Built on Laravel 12 with Livewire, Tailwind CSS, and Microsoft SQL Server, SITHEMR delivers a modern, reactive single-page application experience with multi-tenant architecture, granular role-based access control (65 permissions), and full PhilHealth eClaims 3.0 compliance including CF1-CF5 form generation, XML submission, and API integration.


2. Project Scope & Objectives

•  Digitize and streamline all hospital operations from admission through discharge
•  Achieve full PhilHealth eClaims 3.0 compliance for seamless claims submission
•  Implement multi-tenant architecture supporting multiple hospital facilities
•  Provide granular role-based access control with full audit trail
•  Deliver real-time inventory management across Pharmacy and Central Supply
•  Enable DICOM/PACS integration for radiology imaging
•  Generate all required PhilHealth forms (CF1-CF5, EECL, eSOA) as PDF
•  Support mobile-responsive access for clinicians and nursing staff

3. Technical Architecture


4. Module Breakdown & Pricing

Rate per hour: ₱2,500.00

1. Core Infrastructure
2. Patient Management
3. Emergency Room (ER)
4. In-Patient Services
5. Ancillary Services
6. Financial / Billing
7. PhilHealth Integration
8. Doctors Portal
9. Administration
10. Frontend & UI/UX

5. Summary



6. Terms & Conditions

Payment Terms. 30% upon project commencement, 40% at midpoint milestone, 30% upon final acceptance and deployment.
Project Timeline. Estimated 27 months from project kickoff, subject to timely client feedback and approvals.
Scope Changes. Any changes to the agreed scope will be evaluated and quoted separately via a Change Request process.
Warranty. 12-month warranty period covering bug fixes and defects from the date of final acceptance.
Support & Maintenance. Optional annual support and maintenance agreement available post-warranty at preferential rates.
Intellectual Property. Full source code ownership transfers to the client upon final payment. All third-party licenses remain with their respective owners.
Confidentiality. Both parties agree to maintain strict confidentiality of all project-related information.
Force Majeure. Neither party shall be liable for delays caused by circumstances beyond reasonable control.

7. Acceptance

By signing below, the Client agrees to the scope, terms, and pricing outlined in this proposal.




Thank you for your consideration.
This proposal is valid for 30 days from the date of issue.
| Proposal Date: | July 15, 2026 |
| --- | --- |
| Valid Until: | August 15, 2026 |
| Document Ref: | SITHEMR-QTN-20260715-001 |
| Version: | 1.0 |
| 25+ | 150+ | 95+ | 65 | 145+ | 5 |
| --- | --- | --- | --- | --- | --- |
| Functional Modules | Database Tables | Livewire Components | RBAC Permissions | EECL Items | PhilHealth Claim Forms |
| Component | Technology |
| --- | --- |
| Backend Framework | Laravel 12 (PHP 8.2+) |
| Frontend Stack | Livewire 4.3 + Alpine.js + Tailwind CSS 4.3 |
| Build Tool | Vite 7.0 |
| Database | Microsoft SQL Server (sqlsrv) |
| Authentication | JWT (tymon/jwt-auth 2.3) |
| PDF Generation | DomPDF 3.1 |
| Spreadsheet Export | PhpSpreadsheet 5.8 |
| Vector Database | Qdrant (RAG/Embeddings) |
| External Integration | PhilHealth CEWS, ECS, Cloud Storage, DICOM/PACS |
| # | Description | Hours | Cost |
| --- | --- | --- | --- |
| 1 | Multi-Tenant Architecture
Tenant isolation, global scopes, composite keys | 120 | ₱300,000.00 |
| 2 | Authentication & JWT Integration
JWT auth, session hybrid, persistent claims | 80 | ₱200,000.00 |
| 3 | Role-Based Access Control (RBAC)
65 permissions, 4 roles, overrides, audit trail | 200 | ₱500,000.00 |
| 4 | Master Data Management
15+ CRUD modules (countries, regions, barangays, etc.) | 140 | ₱350,000.00 |
| Subtotal — Core Infrastructure | Subtotal — Core Infrastructure | 540 | ₱1,350,000.00 |
| # | Description | Hours | Cost |
| --- | --- | --- | --- |
| 1 | Patient Registration
Full demographics, PhilHealth member linking, auto-codes | 100 | ₱250,000.00 |
| 2 | Medical Records & Certificates
Patient chart, 5 certificate types, PDF generation | 80 | ₱200,000.00 |
| Subtotal — Patient Management | Subtotal — Patient Management | 180 | ₱450,000.00 |
| # | Description | Hours | Cost |
| --- | --- | --- | --- |
| 1 | ER Triage
Patient search, quick registration, acuity levels, vitals | 100 | ₱250,000.00 |
| 2 | ER Area & Bed Management
Zone/bed management, admission workflow | 80 | ₱200,000.00 |
| 3 | ER Treatment
3-tab clinical form, orders, disposition workflow | 120 | ₱300,000.00 |
| 4 | OECB / EECL
Essential care list, PhilHealth Annex D PDF | 60 | ₱150,000.00 |
| Subtotal — Emergency Room (ER) | Subtotal — Emergency Room (ER) | 360 | ₱900,000.00 |
| # | Description | Hours | Cost |
| --- | --- | --- | --- |
| 1 | OPD Consultation
Visit types, referral sources, YAKAP integration | 80 | ₱200,000.00 |
| 2 | Admission & Ward Management
Admit workflow, room assignment, discharge, calendar-day billing | 140 | ₱350,000.00 |
| 3 | Nursing Module
Orders (5 types), room transfer, discharge confirmation | 100 | ₱250,000.00 |
| 4 | OB/GYN Consultation
Fetal presentations, delivery types, OB-specific fields | 80 | ₱200,000.00 |
| 5 | Delivery Room
Schedule management, treatment, integration with OB | 70 | ₱175,000.00 |
| 6 | Operating Room (OR)
OR rooms, schedule management, surgical procedures | 70 | ₱175,000.00 |
| 7 | Renal Care / Dialysis
Dialysis types, session management | 50 | ₱125,000.00 |
| 8 | MICU
Patient management, assessment parameters, value tracking | 50 | ₱125,000.00 |
| Subtotal — In-Patient Services | Subtotal — In-Patient Services | 640 | ₱1,600,000.00 |
| # | Description | Hours | Cost |
| --- | --- | --- | --- |
| 1 | Laboratory
Tests, panels, reference ranges, dispensing, results, verification | 180 | ₱450,000.00 |
| 2 | Radiology
Modalities, exam types, DICOM/PACS integration, C-STORE | 160 | ₱400,000.00 |
| 3 | Pharmacy
Drug management, batches, inventory, PO, dispensing, PMR | 200 | ₱500,000.00 |
| 4 | Central Supply Room (CSR)
Supply items, categories, suppliers, batch tracking, dispensing | 140 | ₱350,000.00 |
| 5 | Procedures
Categories, procedure master, dispensing workflow | 60 | ₱150,000.00 |
| Subtotal — Ancillary Services | Subtotal — Ancillary Services | 740 | ₱1,850,000.00 |
| # | Description | Hours | Cost |
| --- | --- | --- | --- |
| 1 | Billing Module
4-tab UI, invoices, payments, manual charges, ledger | 250 | ₱625,000.00 |
| 2 | Fund Source Management
Budget tracking, fund source allocation | 60 | ₱150,000.00 |
| 3 | Patient Ledger & Statement
Audit trail, cancel/revoke, PDF statement generation | 80 | ₱200,000.00 |
| 4 | Auto-Invoice & Room Billing
Scheduled billing, calendar-day noon cutoff | 70 | ₱175,000.00 |
| Subtotal — Financial / Billing | Subtotal — Financial / Billing | 460 | ₱1,150,000.00 |
| # | Description | Hours | Cost |
| --- | --- | --- | --- |
| 1 | eClaims 3.0 Module
CF1-CF4 data entry, dynamic line items, 5 components | 300 | ₱750,000.00 |
| 2 | XML Generator & API Client
PhilHealth schema compliance, CEWS/ECS/Cloud services | 200 | ₱500,000.00 |
| 3 | PDF Form Generation
CF1-CF5, EECL, eSOA via DomPDF | 120 | ₱300,000.00 |
| 4 | OECB System
Multi-entry OECB, 145 fixed-fee EECL items | 80 | ₱200,000.00 |
| 5 | CF5 DRG Shadow Billing
DRG code, weight, base rate, variance analysis | 60 | ₱150,000.00 |
| 6 | YAKAP (Primary Care)
Dashboard, empanelment, FPE, screenings, capitation | 100 | ₱250,000.00 |
| 7 | Konsulta
Enrollment, consultations, prescriptions, lab referrals | 80 | ₱200,000.00 |
| Subtotal — PhilHealth Integration | Subtotal — PhilHealth Integration | 940 | ₱2,350,000.00 |
| # | Description | Hours | Cost |
| --- | --- | --- | --- |
| 1 | Doctors Dashboard
OPD/ER/Ward views, patient chart, SOAP notes, history timeline | 120 | ₱300,000.00 |
| Subtotal — Doctors Portal | Subtotal — Doctors Portal | 120 | ₱300,000.00 |
| # | Description | Hours | Cost |
| --- | --- | --- | --- |
| 1 | Role & Permission Management
CRUD, user overrides, full audit trail with JSON diff | 80 | ₱200,000.00 |
| 2 | Audit Trail System
Change logging, diff display, export | 50 | ₱125,000.00 |
| 3 | Hospital Transfer
Inter-facility patient transfer management | 40 | ₱100,000.00 |
| Subtotal — Administration | Subtotal — Administration | 170 | ₱425,000.00 |
| # | Description | Hours | Cost |
| --- | --- | --- | --- |
| 1 | Tailwind CSS Premium UI
Glassmorphism, gradients, section-accent navigation | 100 | ₱250,000.00 |
| 2 | Livewire SPA Navigation
95+ components, event-driven, reactive UI | 80 | ₱200,000.00 |
| 3 | Responsive Design
Mobile/tablet/desktop layouts | 60 | ₱150,000.00 |
| Subtotal — Frontend & UI/UX | Subtotal — Frontend & UI/UX | 240 | ₱600,000.00 |
| Total Estimated Hours | 4390 |
| --- | --- |
| Hourly Rate | ₱2,500.00 |
| Total Project Cost | ₱10,975,000.00 |
| Estimated Duration | 27 months (at 160 hrs/month) |
| Number of Modules | 25+ across 10 categories |
| AUTHORIZED SIGNATORIES | AUTHORIZED SIGNATORIES |
| --- | --- |
| Name: | Name: |
| Title: | Title: |
| Date: | Date: |