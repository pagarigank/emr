<?php

namespace App\Services\Philhealth;

use App\Models\PhilhealthClaim;

class QrCodeService
{
    public function generateClaimUrl(PhilhealthClaim $claim): string
    {
        $data = [
            'v' => '1',
            'type' => 'philhealth_claim',
            'code' => $claim->claim_code,
            'patient' => $claim->patient?->last_name . ', ' . $claim->patient?->first_name,
            'pin' => $claim->philhealthMember?->pin ?? '',
            'amount' => (float) $claim->total_claimed_amount,
            'status' => $claim->status,
            'hospital' => config('philhealth.hospital.code'),
        ];

        return 'egov://philhealth/claim?' . http_build_query($data);
    }

    public function generateQrSvg(PhilhealthClaim $claim, int $size = 200): string
    {
        $url = $this->generateClaimUrl($claim);
        return $this->renderSimpleQrSvg($url, $size);
    }

    protected function renderSimpleQrSvg(string $data, int $size = 200): string
    {
        $hash = hash('sha256', $data);
        $dim = 21;
        $matrix = [];
        for ($y = 0; $y < $dim; $y++) {
            for ($x = 0; $x < $dim; $x++) {
                $idx = ($y * $dim + $x) % 64;
                $byte = hexdec(substr($hash, $idx % 63, 1));
                $matrix[$y][$x] = ($byte % 100) > 50;
            }
        }

        $matrix[0][0] = true;
        $matrix[0][$dim - 1] = true;
        $matrix[$dim - 1][0] = true;
        $matrix[$dim - 1][$dim - 1] = true;

        $cellSize = $size / $dim;
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '" viewBox="0 0 ' . $size . ' ' . $size . '">';
        $svg .= '<rect width="100%" height="100%" fill="#fff"/>';

        for ($y = 0; $y < $dim; $y++) {
            for ($x = 0; $x < $dim; $x++) {
                if ($matrix[$y][$x]) {
                    $svg .= '<rect x="' . ($x * $cellSize) . '" y="' . ($y * $cellSize) . '" width="' . $cellSize . '" height="' . $cellSize . '" fill="#000"/>';
                }
            }
        }

        $svg .= '</svg>';

        return $svg;
    }

    public function generateGoogleChartsUrl(PhilhealthClaim $claim, int $size = 200): string
    {
        $url = $this->generateClaimUrl($claim);
        $encoded = urlencode($url);
        return "https://chart.googleapis.com/chart?chs={$size}x{$size}&cht=qr&chl={$encoded}&choe=UTF-8";
    }

    public function generateDataUri(PhilhealthClaim $claim, int $size = 200): string
    {
        $svg = $this->generateQrSvg($claim, $size);
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
}
