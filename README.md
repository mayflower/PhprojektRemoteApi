Remote HTTP API to PHProjekt 5.4
--------------------------------

Use PhprojektRemoteApi to talk to your PHProjekt installation from PHP code.
Calls are made via HTTP and the result of the call is taken from the resulting
HTML page. No REST or SOAP interface needed on the server side.

Usage:

```php
$api = new \PhprojektRemoteApi\PhprojektRemoteApi('http://your-phprojekt.com', 'hans.mustermann', 'god');
if ($api->login()) {
    var_dump($api->listWorkingtimeToday());
}
```

WARNING: Under development. API may change at any time!