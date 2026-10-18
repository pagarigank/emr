<!-- converted from hospital_emr_eclaims_proposal.bak.docx -->






Enterprise EMR & Integrated
E-Claims Solution Proposal
SITHEMR — Hospital Electronic Medical Records & PhilHealth e-Claims Platform

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Prepared for:  [Hospital Name]
Prepared by:  SITHEMR Solutions Inc.
Date:  July 16, 2026
Document Version:  2.0

# Table of Contents
- 1. Executive Summary
- 2. Core Clinical EMR Modules
- 3. Integrated E-Claims Engine
- 4. System Interoperability & Compliance
- 5. System Interface Preview
- 6. Professional Implementation & Pricing
- 7. Next Steps

# 1. Executive Summary
Hospital billing leakage, delayed claim reimbursements, and fragmented clinical workflows are persistent challenges across Philippine healthcare institutions. SITHEMR addresses these pain points with a unified, multi-tenant Electronic Medical Records (EMR) platform that tightly integrates clinical documentation with PhilHealth e-Claims submission and revenue cycle management.
Built on Laravel 12 + Livewire 4, the platform serves 15+ clinical modules — from patient registration and outpatient consultations to laboratory, pharmacy, radiology, and critical care. The Integrated E-Claims Engine automates eligibility checks via PhilHealth's CEWS SOAP service, generates DTD-compliant XML payloads (eClaims 3.0), uploads claims to the ECS server, polls for real-time status updates, and produces CF1–CF5 / ESOA / EECL PDFs on demand.
This proposal outlines SITHEMR's clinical capabilities, e-Claims integration architecture, interoperability standards, and a professional implementation plan along with transparent pricing.
# 2. Core Clinical EMR Modules
SITHEMR delivers a complete digital charting experience, replacing paper-based records with structured, searchable, and auditable electronic encounters.
Patient Charts:  Full demographic record, PhilHealth member linkage, medical history, document attachments, and automated certificate generation (medical, admission, discharge).
Electronic Prescriptions:  Pharmacy module with drug master file, batch tracking, inventory management, purchase orders, and dispensing — all linked to patient encounters. Medication reconciliation across OPD, ER, and inpatient stays.
Laboratory Ordering:  Test catalog with panels, specimen types, instruments, and reference ranges. Order dispensing from any encounter (OPD/ER/ward/dialysis) with results entry, validation workflow, and flagging for out-of-range values.
Radiology & Imaging:  Modalities, body parts, exam types, PACS/DICOM integration, and structured result reporting with inline image viewing.
Clinical Documentation:  SOAP-format notes, vital signs charting, nursing assessments, OB/delivery records, and MICU/ICU flow sheets with configurable assessment parameters.
Billing & Revenue Cycle:  Real-time charge capture at order entry, automated invoice generation, payment posting, fund-source tracking, and patient statement-of-account PDFs. History-column soft-delete preserves audit trail.

# 3. Integrated E-Claims Engine
The e-Claims Engine is the nerve centre connecting clinical encounters to PhilHealth's national claims processing infrastructure. It automates the full lifecycle — from eligibility verification through submission to payment reconciliation.
Real-Time Eligibility Checks:  Before a claim is created, SITHEMR queries PhilHealth's Claims Eligibility Web Service (CEWS) via SOAP 1.1. The isClaimEligible method validates the member's PIN, membership type, patient relationship, and RVS/ICD codes, returning an eligibility flag and tracking number — all within seconds.
Automated Claim Scrubbing:  Claims undergo pre-submission validation: required fields (ICD-10, RVS, case rate, patient demographics) are checked for completeness; dates are formatted to PhilHealth's m-d-Y standard; membership types are mapped (Employed->S, Self-Employed->G, OFW->I, etc.); confinement days are calculated; and DRG shadow billing amounts are computed for CF5 claims.
XML Generation (eClaims 3.0):  The XmlBuilder service constructs fully compliant XML documents using PHP's DOMDocument API, matching the eClaimsDef.dtd specification. Single-claim and batch XML payloads include eTRANSMITTAL headers (hospital code, transmittal number, process date) and CLAIM elements with MEMBER, PATIENT, ICDCODES, PROCEDURE, PACKAGE, OBSERVATION, and SYSTEM sub-elements. OECB claims receive special OPER1 package handling.
Electronic Adjudication:  XML payloads are uploaded to PhilHealth's eClaims Server (ECS) via the eClaimsUpload SOAP method. Claims progress through a lifecycle: Draft -> Submitted -> Acknowledged -> Paid | Denied. A daily cron job polls getClaimStatus for updates, with simulated fallback when the endpoint is unreachable. Every status transition triggers email notifications to the hospital billing team.
Multi-Form PDF Generation:  On-demand PDF generation via DomPDF for CF1 (normal confinement), CF2 (with referring physician), CF3 (maternity), CF4 (clinical narrative with vital signs), CF5 (DRG shadow billing), eSOA (electronic statement of account), and EECL (ER/OECB emergency care). Each form auto-populates from structured encounter data.
Cloud Archival & QR Codes:  XML payloads are archived to PhilHealth's cloud storage service (base64-encoded upload) for compliance. Each claim receives a QR code (egov://philhealth/claim URI) for quick mobile lookup via SVG, Google Charts, or data URI embedding.

# 4. System Interoperability & Compliance
SITHEMR communicates with external healthcare systems through multiple industry-standard protocols and data formats, ensuring seamless integration with PhilHealth's national infrastructure and future interoperability with other clearinghouses.

All interoperability layers are tenant-scoped via philhealth_hci_code validation, with full audit logging (claim_status_polls table) tracking every external communication. The system gracefully degrades — simulated status progression and fallback eligibility checks ensure operations continue during PhilHealth outages.

# 5. System Interface Preview
The following annotated screenshots illustrate the core EMR dashboard and the PhilHealth e-Claims management interface. Key features are highlighted with teal bounding boxes.
A. Clinical EMR Dashboard

Figure 1 — Clinical EMR Dashboard: patient overview, pending orders, and module navigation (sidebar)
The dashboard presents a consolidated view of the hospital's clinical activity: active patient census, pending laboratory/pharmacy orders, revenue summaries, and one-click access to every clinical module via the role-based collapsible sidebar.

B. PhilHealth E-Claims Management

Figure 2 — PhilHealth Claims Management: claim lifecycle, status tracking, and action buttons
The Claims Management panel displays the full claim lifecycle for every submitted encounter: claim code, patient details, ICD-10 / case rate, actual and claimed amounts, current status (Draft / Submitted / Acknowledged / Paid / Denied), and one-click actions for XML generation, PDF export (CF1–CF5 / eSOA / EECL), and manual status polling.

C. Claim Batch Submission

Figure 3 — Claim Batch Submission: grouped upload to PhilHealth ECS with tracking
Batches group multiple claims for coordinated submission. Each batch tracks the total claim count, aggregate amount, ECS upload timestamp, cloud archival status, and acknowledgment code — providing a complete audit trail from drafting through to PhilHealth adjudication.

# 6. Professional Implementation & Pricing
The following table outlines the proposed implementation packages. All figures are in Philippine Pesos (PHP) and represent a one-time professional services fee plus an annual license for ongoing support and updates.

Annual maintenance and support renewal (20% of license value) includes: software updates, PhilHealth ECS/CEWS endpoint changes, security patches, and 8x5 technical support with 4-hour response SLA.

# 7. Next Steps
- Review this proposal and share any questions or adjustments with the SITHEMR team.
- Schedule a live demo and technical walkthrough tailored to your hospital's workflows.
- Finalize scope and sign the Service Agreement to kick off implementation.
- Provide staging environment access and PhilHealth HCI credentials for integration setup.
- Begin Phase 1 — EMR deployment and claims integration within 5 business days of signing.

──────────────────────────────────────────────────

Thank you for your trust. We are committed to transforming your hospital's
clinical documentation and claims reimbursement experience with SITHEMR.

SITHEMR Solutions Inc.  |  sithemr.com  |  hello@sithemr.com
| Standard / Protocol | Usage | Details |
| --- | --- | --- |
| SOAP 1.1 (XML over HTTPS) | CEWS eligibility checks
ECS claim uploads
Cloud storage archival
Status polling | cURL-based transport; SSL disabled for dev;
SOAPAction: ''; namespace:
http://schemas.xmlsoap.org/soap/envelope/ |
| PhilHealth eClaims 3.0 DTD | XML payload generation
(eTRANSMITTAL + CLAIM elements) | DOMDocument + SimpleXMLElement;
UTF-8 encoding; date format: m-d-Y;
Inline XML in SOAP body as pXMLFile |
| REST / HTTP Multipart | Sandbox test upload
(PhilHealthApiService) | Bearer token auth; multipart/form-data
POST; text/xml file attachment;
JSON response parsing |
| Base64 Encoding | Cloud storage file upload | pFileData parameter; 120-second timeout;
status code 1 / SUCCESS = accepted |
| DRG (Diagnosis-Related Groups) | CF5 Shadow Billing
(ICD-10 -> DRG mapping) | 15 clinical DRG groups + catch-all;
60/40 HF/PF split; configurable
base rate (default ₱35,000) |
| JWT (JSON Web Tokens) | Session authentication | tymon/jwt-auth; Bearer token;
stored in server session;
multi-tenant tenant_id injection |
| DomPDF | CF1-CF5 / eSOA / EECL PDF forms | Remote-enabled; A4/letter;
Livewire view rendering;
inline + download modes |
| QR Code (egov:// URI) | Claim mobile lookup | SVG / Google Charts / data URI;
seeded PRNG for deterministic modules;
egov://philhealth/claim?... |
| Module / Service | Scope | Cost (PHP) |
| --- | --- | --- |
| Core EMR License | On-premise or cloud deployment; 15+ clinical modules; unlimited patient records; role-based access; audit logging; 1-year software updates | ₱1,200,000 |
| Claims API Integration | PhilHealth CEWS + ECS + Cloud Storage integration; XML generation (eClaims 3.0); automated claim scrubbing; status polling cron; CF1–CF5 / eSOA / EECL PDF generation; QR code support | ₱480,000 |
| Hospital Staff Training | On-site or remote training program for clinical and billing staff; train-the-trainer materials; user manuals; 5-day engagement | ₱180,000 |
| Go-Live Support | Dedicated implementation engineer for 2 weeks post-launch; data migration assistance; go-live checklist; 30-day priority support hotline | ₱240,000 |
| TOTAL PACKAGE | Full EMR + E-Claims solution incl. training & go-live support | ₱2,100,000 |