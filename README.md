Remote HTTP API to PHProjekt 5.4
--------------------------------

Use PhprojektRemoteApi to talk to your PHProjekt installation from PHP code.
Calls are made via HTTP and the result of the call is taken from the resulting
HTML page. No REST or SOAP interface needed on the server side.

WARNING: Under development. API may change at any time!

Example Usage:

```php
$phprojekt = new \PhprojektRemoteApi\PhprojektRemoteApi(
    'http://your-phprojekt.com',
    'hans.mustermann',
    'god'
);

$timecardApi = $phprojekt->getTimecardApi();
var_dump($timecardApi->getWorkingHours(new \DateTime()));

```

Timecard API
============

The Timecard module provides methods to interact with your PHProjekt timecard.

Projects API
============

Get projects statistics from PHPRojekt.s

Ptimecontrol API
================

Not yet implemented