<?php

namespace App\Traits;

use DOMDocument;

trait SoapClient
{
    protected function buildSoapEnvelope(string $method, array $params): string
    {
        $xml = new DOMDocument('1.0', 'UTF-8');
        $envelope = $xml->createElementNS('http://schemas.xmlsoap.org/soap/envelope/', 'soap:Envelope');
        $envelope->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
        $envelope->setAttributeNS('http://www.w3.org/2000/xmlns/', 'xmlns:xsd', 'http://www.w3.org/2001/XMLSchema');

        $body = $xml->createElement('soap:Body');
        $methodEl = $xml->createElement($method);

        foreach ($params as $key => $value) {
            $el = $xml->createElement($key);
            $el->appendChild($xml->createTextNode($value ?? ''));
            $methodEl->appendChild($el);
        }

        $body->appendChild($methodEl);
        $envelope->appendChild($body);
        $xml->appendChild($envelope);

        return $xml->saveXML();
    }

    protected function callSoapEndpoint(string $endpoint, string $envelope, int $timeout = 60): array
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $endpoint,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $envelope,
            CURLOPT_HTTPHEADER => [
                'Content-Type: text/xml; charset=utf-8',
                'SOAPAction: ""',
                'Content-Length: ' . strlen($envelope),
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => $timeout,
            CURLOPT_SSL_VERIFYPEER => (bool) config('soap.soap_ssl_verify', true),
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        return [
            'response' => $response,
            'http_code' => $httpCode,
            'curl_error' => $curlError,
        ];
    }

    protected function parseSoapBody(string $response): ?object
    {
        $xml = @simplexml_load_string($response);
        if (!$xml) return null;

        $body = $xml->children('http://schemas.xmlsoap.org/soap/envelope/')->Body
             ?? $xml->children('')->Body;

        if (!$body) return null;

        foreach ($body->children() as $child) {
            return $child;
        }

        return null;
    }
}
