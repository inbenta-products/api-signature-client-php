# Introduction
This client helps you signing requests following Inbenta's API Signature Protocol, which is an extra security step that some Inbenta APIs include.

The protocol details are explained in Inbenta's developers site (https://developers.inbenta.io/general/authorization/signing-api-requests), but in summary what it does is adding some additional header to API requests.

In detail, any request that is signed using Inbenta's Signature Protocol must provide the following headers:
* `x-inbenta-key`: Like other Inbenta API, this API requires authorization.
* `authorization`: Like other Inbenta API, this API requires authorization.
* `x-inbenta-signature`: This is the header containing the signature this client will help you build.
* `x-inbenta-signature-version`: This header identifies the protocol to follow when signing requests (defaults to `"v1"`).
* `x-inbenta-timestamp`: This header carries a unix timestamp of the time of the request. This is a security measure to prevent replay attacks (defaults to `time()`).


# Installation
You should load this library using [composer](https://getcomposer.org).
First of all, edit your `composer.json` file to include the api-signature-client repository:
```json
"repositories": [
    {
        "type": "vcs",
        "url": "<the url you see when clicking in the 'Clone' button>"
    }
]
```
Then, from the root of your project you can require the library:
```bash
$ composer require inbenta/api-signature-client
```

# Usage
The SignatureClient can be used in 2 different ways, which are detailed in the following sections. You will find two examples:

* `Using PSR-7 Requests and Responses`: if you are using any framework that uses PSR-7 objects for representing requests and responses, use this option in order to simplify the signing process. Be aware that part of the simplification implies that your objects will be altered (by adding new headers mostly).
* `Using string values`: if you don't have proper objects for requests and responses in your application or you want to add any customization to the authorization process. This option works with the values needed for each step instead of wrapping them within a PSR-7 object.

## Before running the examples
See that we are using Guzzle for representing requests and responses in the examples that follow.

Also, see that some constants are defined in the following examples, which are:
* `AUTH_API_URL`: The Auth URL against you authenticate for all Inbenta API's (usually `https://api.inbenta.io/v1/auth`).

Then, from Backstage > Administration > Reporting API section you will be able to obtain the rest of constants:
* `API_KEY`: The reporting key to authenticate against Auth.
* `API_SECRET`: The reporting secret to authenticate against Auth.
* `REPORTING_API_BASE_URL`: The endpoint to target to obtain your reporting data.
* `SIGNATURE_KEY`: The token to correctly sign your requests.


## Examples

### Using PSR-7 Requests and Responses

```php
<?php

require 'path/to/your/vendor/autoload.php';

use Inbenta\ApiSignature\SignatureClient;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Client;

define('AUTH_API_URL', '');
define('REPORTING_API_BASE_URL', '');
define('API_KEY', '');
define('API_SECRET', '');
define('SIGNATURE_KEY', '');

// 1. Inbenta Auth
$auth = new Client();
$headers = [
    'x-inbenta-key' => API_KEY
];
$body = json_encode([
    'secret' => API_SECRET
]);
$response = $auth->request('POST', AUTH_API_URL, ['headers' => $headers, 'body' => $body]);
$responseBody = json_decode($response->getBody(true)->getContents(), true);
$accessToken = $responseBody['accessToken'];

// 2. Create request
$path = 'v1/events/user_questions';
$request = new Request(
    $method = 'GET',
    $url = REPORTING_API_BASE_URL.$path,
    [
        'x-inbenta-key' => API_KEY,
        'Authorization' => 'Bearer '.$accessToken
    ]
);

// 3. Sign request
$signatureClient = new SignatureClient(REPORTING_API_BASE_URL, SIGNATURE_KEY);
$request = $signatureClient->signRequest($request);

// 4. Send request to API
$client = new Client();
$response = $client->send($request);

// 5. Validate response
$signatureValid = $signatureClient->validateResponse($response);
```

### Using string values

```php
<?php

require 'path/to/your/vendor/autoload.php';

use Inbenta\ApiSignature\SignatureClient;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Client;

define('AUTH_API_URL', '');
define('REPORTING_API_BASE_URL', '');
define('API_KEY', '');
define('API_SECRET', '');
define('SIGNATURE_KEY', '');

// 1. Inbenta Auth
$auth = new Client();
$headers = [
    'x-inbenta-key' => API_KEY
];
$body = json_encode([
    'secret' => API_SECRET
]);
$response = $auth->request('POST', AUTH_API_URL, ['headers' => $headers, 'body' => $body]);
$responseBody = json_decode($response->getBody(true)->getContents(), true);
$accessToken = $responseBody['accessToken'];

// 2. Create request
$path = 'v1/events/user_questions';
$body = '';
$request = new Request(
    $method = 'GET',
    $url = REPORTING_API_BASE_URL.$path,
    [
        'x-inbenta-key' => API_KEY,
        'Authorization' => 'Bearer '.$accessToken
    ]
);

// 3. Sign request
$signatureClient = new SignatureClient(REPORTING_API_BASE_URL, SIGNATURE_KEY);
$signature = $signatureClient->generateRequestSignature($url, $body, $method);

$request = $request->withHeader('x-inbenta-signature-version', $signatureClient->getSignatureVersion());  // v1 by default
$request = $request->withHeader('x-inbenta-timestamp', $signatureClient->getTimestamp());  // time() by default
$request = $request->withHeader('x-inbenta-signature', $signature);

// 4. Send request to API
$client = new Client();
$response = $client->send($request);

// 5. Validate response
$responseSignature = $response->getHeaderLine('x-inbenta-signature');
$responseBody = $response->getBody()->getContents();
$signatureValid = $signatureClient->validateResponseSignature($responseSignature, $responseBody);
```

# Dependencies
This repository has only two dev dependencies (for testing purposes only), which are `guzzlehttp/psr7@^1.5` and `phpunit/phpunit@^4.8`.
