# Arachnid Shield PHP Client

This library is a PHP Client for the Arachnid Shield API.

The Arachnid Shield API is a free HTTP API that implements the scanning
of media (images or videos) for proactive detection of known CSAM and harmful/abusive images of 
children. Maintained by the Canadian Centre for Child Protection, it offers API endpoints to scan 
media (images or videos) against an existing database of known CSAM images.

# Installation

You may install the client in your PHP project using composer:
```
composer require arachnid/shield
```

## Usage

An example of scanning a media file on disk for CSAM:

```php
use ArachnidShield\ArachnidShield;

include __DIR__ . "/../vendor/autoload.php";

$shield = new ArachnidShield("username", "password");
$scannedMedia = $shield->scanMediaFromFile("path/to/image.jpeg");
if ($scannedMedia->matchesKnownMedia()) {
    echo "Scanned media has matches to known material";
    echo $scannedMedia->visualMatchDetails;
}
```

An example of scanning a media file hosted at a website you own:

```php
use ArachnidShield\ArachnidShield;

include __DIR__ . "/../vendor/autoload.php";

$shield = new ArachnidShield("username", "password");
$scannedMedia = $shield->scanMediaFromUrl("https://example.com/path/to/image.jpeg");
if ($scannedMedia->matchesKnownMedia()) {
    echo "Scanned media has matches to known material";
    echo $scannedMedia->visualMatchDetails;
}
```
