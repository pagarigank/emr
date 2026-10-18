<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class PhilHealthApiService
{
    /**
     * Streams the generated XML file payload to PhilHealth Sandbox.
     *
     * @param string $xmlContent
     * @param string $filename
     * @return array
     */
    public function uploadTestClaim(string $xmlContent, string $filename = 'test_claim.xml'): array
    {
        $url      = config('services.philhealth.test_url');
        $username = config('services.philhealth.username');
        $password = config('services.philhealth.password');
        $token    = config('services.philhealth.dev_token');

        try {
            // Execute HTTP POST payload mimicking multipart form collection boundary
            $response = Http::withHeaders([
                'Accept'        => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ])
            // If facing local development certificate errors, safely uncomment line below:
            // ->withoutVerifying()
            ->attach('uploadFile', $xmlContent, $filename, ['Content-Type' => 'text/xml'])
            ->post($url, [
                'name'        => $username,
                'password'    => $password,
                'description' => 'ECLAIMS 3.0 GLOBAL CSP TESTING STREAM',
            ]);

            if ($response->successful()) {
                return [
                    'status'        => 'success',
                    'http_code'     => $response->status(),
                    'philhealth_rx' => $response->json(),
                ];
            }

            return [
                'status'    => 'failed',
                'http_code' => $response->status(),
                'error'     => $response->body(),
            ];

        } catch (Exception $e) {
            return [
                'status' => 'exception',
                'error'  => $e->getMessage(),
            ];
        }
    }
}
