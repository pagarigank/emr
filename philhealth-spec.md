# Specification Blueprint: PhilHealth eClaims 3.0 XML Generator & API Client (Laravel + MS SQL)

## Role & Goal
You are an expert software engineer specializing in healthcare integrations, Laravel (PHP), and Microsoft SQL Server. Your task is to implement an automated electronic Statement of Account (eSOA) XML generator and a multi-part REST API client compliant with the mandatory PhilHealth eClaims 3.0 technical framework.

The system must support a multi-tenant structure (multiple hospitals/HCIs using one database instance) and ensure all generated payloads conform strictly to PhilHealth XML schemas, specifically eliminating scanned PDFs and integrating Diagnosis-Related Group (DRG) Claim Form 5 (CF5) data models.

---

## 1. System Requirements & Architecture
* **Framework:** Laravel 10.x or 11.x
* **Database Driver:** `sqlsrv` (Microsoft SQL Server)
* **Data Payload Format:** Strictly XML (`SimpleXMLElement` or native MS SQL `FOR XML`)
* **API Protocol:** HTTP POST via `multipart/form-data` stream
* **Testing Sandbox Endpoint:** `https://philhealth.gov.ph`

---

## 2. Environment Configuration (`.env`)
Ensure the application reads the following structural configuration variables:

```env
PHILHEALTH_ENV=sandbox
PHILHEALTH_TEST_URL=https://philhealth.gov.ph
PHILHEALTH_USERNAME=your_global_csp_username
PHILHEALTH_PASSWORD=your_global_csp_password
PHILHEALTH_DEV_TOKEN=your_global_token_from_itmd
```

---

## 3. Step-by-Step Implementation Instructions

### Step 3.1: Create the PhilHealth XML Service
Generate a service class at `app/Services/PhilHealthXmlService.php`. This service must map multi-tenant data extracted from the MS SQL database using structural mapping logic.

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use SimpleXMLElement;
use Exception;

class PhilHealthXmlService
{
    /**
     * Generates a PhilHealth eClaims 3.0 compliant eSOA XML string.
     *
     * @param int \$claimId
     * @param string \$hciCode
     * @return string
     * @throws Exception
     */
    public function generateEsoaXml(int \(claimId, string\)hciCode): string
    {
        // 1. Fetch record from MS SQL with isolation boundary check (Multi-Tenant)
        \$claim = DB::connection('sqlsrv')->table('claims')
            ->where('id', \$claimId)
            ->where('hci_code', \$hciCode)
            ->first();

        if (!\$claim) {
            throw new Exception("Claim record not found or access denied for HCI: {\$hciCode}");
        }

        // 2. Initialize the XML document complying with the official DTD root node
        \$xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><CLAIM></CLAIM>');
        
        // 3. Map Header Data
        \(xml->addChild('HCI_CODE', htmlspecialchars(\)claim->hci_code));
        \(xml->addChild('ENCOUNTER_ID', htmlspecialchars(\)claim->encounter_number));
        
        // 4. Map Patient Demographic Structural Block
        \(patient =\)xml->addChild('PATIENT');
        \(patient->addChild('FIRST_NAME', htmlspecialchars(\)claim->patient_first_name));
        \(patient->addChild('LAST_NAME', htmlspecialchars(\)claim->patient_last_name));
        \(patient->addChild('EGOV_STATUS', htmlspecialchars(\)claim->egov_qr_status ?? 'VERIFIED'));

        // 5. Map DRG / Claim Form 5 (CF5) Shadow Billing elements
        \(cf5Billing =\)xml->addChild('CF5_BILLING');
        \(cf5Billing->addChild('TOTAL_CHARGES', number_format(\)claim->total_amount, 2, '.', ''));
        \(cf5Billing->addChild('DRG_CODE', htmlspecialchars(\)claim->drg_code ?? ''));
        
        // Return raw formatted XML payload string
        return \$xml->asXML();
    }
}
```

### Step 3.2: Create the API Client Service
Generate a transmission handler class at `app/Services/PhilHealthApiService.php` using Laravel’s HTTP Client (Guzzle wrapper) to stream data as a multipart transmission.

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class PhilHealthApiService
{
    /**
     * Streams the generated XML file payload to PhilHealth Sandbox.
     *
     * @param string \$xmlContent
     * @param string \$filename
     * @return array
     */
    public function uploadTestClaim(string \(xmlContent, string\)filename = 'test_claim.xml'): array
    {
        \(url = config('services.philhealth.test_url', env('PHILHEALTH_TEST_URL'));\)username = env('PHILHEALTH_USERNAME');
        \(password = env('PHILHEALTH_PASSWORD');\)token = env('PHILHEALTH_DEV_TOKEN');

        try {
            // Execute HTTP POST payload mimicking multipart form collection boundary
            \$response = Http::withHeaders([
                'Accept'        => 'application/json',
                'Authorization' => 'Bearer ' . \$token,
            ])
            // If facing local development certificate errors, safely uncomment line below:
            // ->withoutVerifying() 
            ->attach('uploadFile', \(xmlContent,\)filename, ['Content-Type' => 'text/xml'])
            ->post(\$url, [
                'name'        => \$username,
                'password'    => \$password,
                'description' => 'ECLAIMS 3.0 GLOBAL CSP TESTING STREAM',
            ]);

            if (\$response->successful()) {
                return [
                    'status'        => 'success',
                    'http_code'     => \$response->status(),
                    'philhealth_rx' => \$response->json(),
                ];
            }

            return [
                'status'    => 'failed',
                'http_code' => \$response->status(),
                'error'     => \$response->body(),
            ];

        } catch (Exception \$e) {
            return [
                'status' => 'exception',
                'error'  => \$e->getMessage(),
            ];
        }
    }
}
```

### Step 3.3: Wire Up the Diagnostic Testing Controller
Generate a web route/controller layer to facilitate continuous integration loops and testing diagnostics. Create `app/Http/Controllers/PhilHealthTestController.php`.

```php
<?php

namespace App\Http\Controllers;

use App\Services\PhilHealthXmlService;
use App\Services\PhilHealthApiService;
use Illuminate\Http\JsonResponse;

class PhilHealthTestController extends Controller
{
    protected \$xmlService;
    protected \$apiService;

    public function __construct(PhilHealthXmlService \(xmlService, PhilHealthApiService\)apiService)
    {
        \(this->xmlService =\)xmlService;
        \(this->apiService =\)apiService;
    }

    /**
     * Executes a complete end-to-end sandbox pipeline submission test.
     */
    public function __invoke(): JsonResponse
    {
        // Define testing constants based on PhilHealth Sandbox Dummy HCI Specs
        \$testClaimId = 1; 
        \$testHciCode = 'H99999999'; 

        try {
            // 1. Structural extraction and XML transformation step
            \$xmlString = \(this->xmlService->generateEsoaXml(\)testClaimId, \(testHciCode);                          // 2. Transmit generated payload to the cloud sandbox endpoint\)filename = "HCI_{\(testHciCode}_Claim_{\)testClaimId}.xml";
            \(apiResult =\)this->apiService->uploadTestClaim(\(xmlString,\)filename);

            return response()->json([
                'diagnostic_run' => 'complete',
                'generated_file' => \$filename,
                'api_response'   => \$apiResult
            ], \$apiResult['http_code'] ?? 200);

        } catch (\Exception \$e) {
            return response()->json([
                'diagnostic_run' => 'failed',
                'error'          => \$e->getMessage()
            ], 500);
        }
    }
}
```

### Step 3.4: Define the Testing Routing Hook
Append this line inside your `routes/web.php` or `routes/api.php` file to expose the entry trigger for testing executions:

```php
use App\Http\Controllers\PhilHealthTestController;

Route::get('/api/v3/philhealth/test-pipeline', PhilHealthTestController::class);
```

---

## 4. Verification Checkpoints for the AI Assistant
When executing the development cycles, verify the output meets these rules:
1. **No Scanned Formats:** The `uploadFile` payload key *must* pass string-validated raw XML content. Hardcoded paths pointing to PDFs must trigger an immediate coding error validation failure.
2. **Multi-Tenant Guarding:** The `generateEsoaXml` query string *must* structurally append an explicit conditional `where('hci_code', ...)` clause to enforce system architecture partitioning.
3. **Data Type Preservation:** Monetary parameters like `TOTAL_CHARGES` must string-format values with exactly two fixed decimal places (`0.00`) to match strict parsing validation routines enforced by PhilHealth cloud schemas.
