<?php

namespace ArachnidShield\Models;

use ArachnidShield\ArachnidShieldException;

/**
 * A record of a batch of PDQ hashes that have been scanned by the Arachnid Shield API 
 * and any matching classifications that were found in the database.
 */
class ScannedPdqHashes {

    /**
     * An array of PDQ match results keyed by the base64 representation of the hashes.
     * @var array<string, MediaClassification>
     */
    public array $scannedHashes;

    
    public function __construct(array $scannedHashes) {
        $this->scannedHashes = $scannedHashes;
    }

    static function deserialize(array $data): self {
        $scannedHashes = [];

        foreach ($data["scanned_hashes"] as $scannedHash => $classificationObject) {
            switch ($classificationObject["classification"]) {
                case "csam": 
                {
                    $scannedHashes[$scannedHash] = MediaClassification::CSAM;
                    break;
                }
                case "harmful-to-children":
                {
                    $scannedHashes[$scannedHash] = MediaClassification::HarmfulToChildren;
                    break;
                }
                case "no-known-match":
                {
                    $scannedHashes[$scannedHash] = MediaClassification::NoKnownMatch;
                    break;
                }
                default: {
                    throw new ArachnidShieldException("Unrecognized media classification: " . $classificationObject["classification"]);
                }
            }
        }
        return new self($scannedHashes);
    }
}