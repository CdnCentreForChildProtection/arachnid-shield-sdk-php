<?php

namespace ArachnidShield;

use ArachnidShield\Models\ScannedMedia;
use ArachnidShield\Models\ScannedPdqHashes;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Utils;
use Psr\Http\Message\ResponseInterface;
use Exception;
use Throwable;

/**
 * Thrown upon a failed or erroneous interaction with the Arachnid Shield API.
 */
class ArachnidShieldException extends Exception {
    public function __construct($message, $code = 0, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }

    public function __toString(): string {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}

interface ArachnidShieldV1 {

    /**
     * Scan a given media for CSAM (image or video) based on its contents (i.e. raw bytes).
     * @param resource $content The resource containing the raw contents of the media.
     * @param string|null $mimeType If known, the mime type for the media.
     * @throws ArachnidShieldException
     * @return ScannedMedia
     */
    public function scanMediaFromBytes($content, string $mimeType = null): ScannedMedia;

    /**
     * Scan a given media for CSAM (image or video) based on the contents of the file.
     * @param string $filePath
     * @throws ArachnidShieldException
     */
    public function scanMediaFromFile(string $filePath): ScannedMedia;
    /**
     * Scan a given media for CSAM (image or video) based on the URL that it is available at.
     * @param string $url The url to the media that needs to be scanned.
     * @return ScannedMedia
     * @throws ArachnidShieldException
     */
    public function scanMediaFromUrl(string $url): ScannedMedia;

    /**
     * Scan medias for CSAM based on their PDQ hashes.
     * @param array<string> $hashes A list of base64-encoded PDQ hashes to scan.
     * @return ScannedPdqHashes A record of a batch of PDQ hashes that have been scanned by the Arachnid Shield API 
     * and any matching classifications that were found in the database.
     */
    public function scanPdqHashes(array $hashes): ScannedPdqHashes;

}

class ArachnidShield implements ArachnidShieldV1
{
    private Client $client;
    private string $username;
    private string $baseUri;
    private string $authHeader;
    private float $timeout = 30.0;

    public function __construct(string $username, string $password)
    {
        $this->username = $username;
//        $this->baseUri = "https://shield.projectarachnid.com/v1/";
        $this->baseUri = "http://fastapi.public-api-aalekh.staging.c3p/v1/";

        $this->authHeader = "Basic " . base64_encode("$username:$password");

        $this->client = new Client([
            'base_uri' => $this->baseUri,
            'timeout' => $this->timeout,
            'headers' => [
                'Authorization' => $this->authHeader
            ],
            'http_errors' => false
        ]);
    }

    static function handleScannedMediaResponse(ResponseInterface $response) {
        $body = $response->getBody()->getContents();
        $data = json_decode($body, true);
        $status = $response->getStatusCode();

        if ($status >= 400) {
            throw new ArachnidShieldException($data["detail"], $status);
        } else {
            return ScannedMedia::deserialize($data);
        }
    }

    static function handleScannedPdqHashesResponse(ResponseInterface $response) {
        $body = $response->getBody()->getContents();
        $data = json_decode($body, true);
        $status = $response->getStatusCode();

        if ($status >= 400) {
            throw new ArachnidShieldException($data["detail"], $status);
        } else {
            return ScannedPdqHashes::deserialize($data);
        }
    }

    public function scanMediaFromBytes(
        $content,
        string $mimeType = null
    ): ScannedMedia
    {
        return $this->client->requestAsync(
            "POST",
            "media/",
            [
                "headers" => [
                    "Content-Type" => $mimeType,
                ],
                "body" => $content
            ]
        ) -> then(
            function (ResponseInterface $response) {
                return ArachnidShield::handleScannedMediaResponse($response);
            }
        ) -> wait();
    }

    public function scanMediaFromFile(string $filePath): ScannedMedia
    {
        $contents = Utils::tryFopen($filePath, "r");
        $mimeType = mime_content_type($filePath);

        if (!$mimeType) {
            $mimeType = "application/octet-stream";
        }
        return $this->scanMediaFromBytes($contents, $mimeType);
    }

    public function scanMediaFromUrl(string $url): ScannedMedia
    {
        return $this->client->requestAsync(
            "POST",
            "url/",
            [
                "headers" => [
                    "Content-Type" => "application/json",
                ],
                "json" => [
                    "url" => $url
                ]
            ]
        )->then(
            function (ResponseInterface $response) {
                return ArachnidShield::handleScannedMediaResponse($response);
            }
        )->wait();
    }

    public function scanPdqHashes(array $hashes): ScannedPdqHashes
    {
        return $this->client->requestAsync(
            "POST",
            "pdq/",
            [
                "headers" => [
                    "Content-Type" => "application/json",
                ],
                "json" => [
                    "hashes" => $hashes
                ]
            ]
        )->then(
            function (ResponseInterface $response) {
                return ArachnidShield::handleScannedPdqHashesResponse($response);
            }
        )->wait();
    }
}