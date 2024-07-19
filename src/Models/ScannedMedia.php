<?php

namespace ArachnidShield\Models;

use ArachnidShield\Models\MediaClassification;
use ArachnidShield\Models\MatchType;
use ArachnidShield\ArachnidShieldException;


/**
 * A record of a media (+ metadata) that has been scanned by the Arachnid Shield API 
 * and any matching classification that was found in our database.
 */
class ScannedMedia {

    /**
     * @var int The total size, in bytes, of the media that was scanned.
     */
    public int $sizeBytes;

    /**
     * @var array<array> An array of images found in the Arachnid Shield database that were visually similar to the scanned image.
     */
    public array $nearMatchDetails;

    /**
     * @var ?MatchType The matching technology that was used to match the submitted media to the media in our database;
     * This is `null` if the classification is `no-known-match`.
     */
    public ?MatchType $matchType;

    /**
     * The classification of the media in our database that matched the submitted media.
     * @var MediaClassification
     */
    public MediaClassification $classification;
    /**
     *The base-32 representation of the SHA1 cryptographic hash of the media that was scanned. 
     * @var string
     */
    public string $sha1Base32;
    /**
     * The base-16 (hexadecimal) representation of the SHA256 cryptographic hash of the media that was scanned. 
     * @var string
     */
    public string $sha256Hex;

    static function deserialize(array $data): self {
        $match_type = null;

        switch ($data["match_type"]) {
            case null: {
                break;
            }
            case "near":
            {
                $match_type = MatchType::Near;
                break;
            }
            case "exact":
            {
                $match_type = MatchType::Exact;
                break;
            }
            default: {
                throw new ArachnidShieldException("Unrecognized match type: " . $data["match_type"]);
            }
        }

        switch ($data["classification"]) {
            case "csam":
            {
                $classification = MediaClassification::CSAM;
                break;
            }
            case "harmful-to-children":
            {
                $classification = MediaClassification::HarmfulToChildren;
                break;
            }
            case "no-known-match":
            {
                $classification = MediaClassification::NoKnownMatch;
                break;
            }
            default: {
                throw new ArachnidShieldException("Unrecognized media classification: " . $data["classification"]);
            }
        }

        return new self(
            $data["size_bytes"],
            $data["near_match_details"],
            $match_type,
            $classification,
            $data["sha1_base32"],
            $data["sha256_hex"]
        );
    }

    public function __construct(
        int $sizeBytes,
        array $nearMatchDetails,
        ?MatchType $matchType,
        MediaClassification $classification,
        string $sha1Base32,
        string $sha256Hex,
    ) {
        $this->classification = $classification;
        $this->sizeBytes = $sizeBytes;
        $this->matchType = $matchType;
        $this->sha256Hex = $sha256Hex;
        $this->sha1Base32 = $sha1Base32;
        $this->nearMatchDetails = $nearMatchDetails;
    }

    /**
     * Determine if the scanned media matches any known media from the Project Arachnid database.
     * @return bool true if the scanned media is considered harmful or CSAM, otherwise false.
     */
    public function matchesKnownMedia(): bool {
        return $this->classification !== MediaClassification::NoKnownMatch;
    }
}