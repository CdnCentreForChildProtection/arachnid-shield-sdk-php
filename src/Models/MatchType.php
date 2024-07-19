<?php

namespace ArachnidShield\Models;


/**
 * The technology that was used to verify a match between two media.
 * This indicates whether the submitted media matched media in our database
 * exactly (by cryptographic hash) or visually (by visual hash).
 */
enum MatchType {
    /**
     * An exact cryptographic hash match using SHA1.
     */
    case Exact;

    /**
     * A visual near-match using PhotoDNA.
     */
    case Near;
}