<?php

return [
    'aet_title'    => env('DICOM_AET', 'SITHEMR'),
    'pacs_host'    => env('DICOM_PACS_HOST', ''),
    'pacs_port'    => env('DICOM_PACS_PORT', 11112),
    'pacs_aet'     => env('DICOM_PACS_AET', 'PACS'),
    'storage_driver' => env('DICOM_STORAGE', 'public'),
    'storage_path' => env('DICOM_STORAGE_PATH', 'dicom/studies'),
];
