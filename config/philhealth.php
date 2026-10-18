<?php

return [
    'hospital' => [
        'code' => env('PHILHEALTH_HOSPITAL_CODE', 'P0000000'),
        'name' => env('PHILHEALTH_HOSPITAL_NAME', 'Hospital Name'),
        'tin' => env('PHILHEALTH_HOSPITAL_TIN', '000-000-000-000'),
        'contact_no' => env('PHILHEALTH_HOSPITAL_CONTACT', ''),
        'email' => env('PHILHEALTH_HOSPITAL_EMAIL', ''),
        'fax_no' => env('PHILHEALTH_HOSPITAL_FAX', ''),
    ],

    'system' => [
        'name' => 'SITHEMR',
        'version' => '1.0',
    ],

    'cews' => [
        'endpoint' => env('PHILHEALTH_CEWS_ENDPOINT', 'https://cews.philhealth.gov.ph/cews/services/ClaimsEligibility'),
        'username' => env('PHILHEALTH_CEWS_USERNAME', ''),
        'password' => env('PHILHEALTH_CEWS_PASSWORD', ''),
        'timeout' => env('PHILHEALTH_CEWS_TIMEOUT', 30),
    ],

    'eclaims' => [
        'endpoint' => env('PHILHEALTH_ECLAIMS_ENDPOINT', 'https://eclaims.philhealth.gov.ph/eclaims/services/eClaimsUpload'),
        'username' => env('PHILHEALTH_ECLAIMS_USERNAME', ''),
        'password' => env('PHILHEALTH_ECLAIMS_PASSWORD', ''),
        'timeout' => env('PHILHEALTH_ECLAIMS_TIMEOUT', 60),
    ],

    'cloud_storage' => [
        'endpoint' => env('PHILHEALTH_CLOUD_ENDPOINT', 'https://ecstest.philhealth.gov.ph:7220/eden/ws/storage/upload'),
        'username' => env('PHILHEALTH_CLOUD_USERNAME', ''),
        'password' => env('PHILHEALTH_CLOUD_PASSWORD', ''),
    ],

    'dtd_url' => env('PHILHEALTH_DTD_URL', 'http://cdp.philhealth.gov.ph/dtd/eClaimsDef.dtd'),

    'drg' => [
        'base_rate' => env('PHILHEALTH_DRG_BASE_RATE', 35000),
    ],
];
