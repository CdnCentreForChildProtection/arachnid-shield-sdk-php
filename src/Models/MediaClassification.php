<?php

namespace ArachnidShield\Models;

/**
 * A list of the possible categories that an image or video could be classified as.
 */
enum MediaClassification {
    /**
     * Child sexual abuse material, also known as "child pornography".
     */
    case CSAM;

    /**
     * Content considered harmful to children includes all images or videos associated with the abusive incident, nude or partially nude images or videos of children that have become publicly available and are used in a sexualized context or connected to sexual commentary.
     */
    case HarmfulToChildren;

    /**
     * Content did not match to any known media.
     */
    case NoKnownMatch;
}
