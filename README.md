# usd-php

PHP lib to interact with our internal issue/helpdesk tool.

# Usage

Add it to your dependencies:

```
composer require procergs/usd-php
```

Code example:

```php
<?php

require_once 'vendor/autoload.php';

use GuzzleHttp\Client;
use PROCERGS\USD\UsdService;
use PROCERGS\USD\Model\Issue;
use PROCERGS\USD\Model\IssueBuilder;
use PROCERGS\USD\Model\EndUser;

$systemCode = 999999; // Your system's user code
$org = "YOUR SYSTEM'S ORG";
$pass = "your system's user password";

$config = [
    'defaultPhoneCountry' => 'BR', // or another default country code
    'newIssueEndpoint' => 'https://endpoit.used.for.issue.creation',
    'getIssueEndpoint' => 'https://endpoit.used.for.getting.issues',
];

$http = new Client([
    'headers' => [
        'matricula' => $systemCode,
        'organizacao' => $org,
        'senha' => $pass,
    ],
]);

$usd = new UsdService($http, $config);

$endUser = new EndUser('John Doe', 'john@doe.net', 'john.doe');

$builder = (new IssueBuilder())
    ->setTitle('Server unresponsive')
    ->setDescription("I'm unable to ping the TEST server. There is no route to it.")
    ->setArea('Ops')
    ->setOrganization('My Company')
    ->setConfigurationItem('SERVER-7356')
    ->setGroup('Ops.HelpDesk')
    ->setEndUser($endUser)
    ->setSymptom('Server unresponsive');

$issue = $usd->createIssue(new Issue($builder));
// the issue is now persisted on the backend, so it'll have and ID
$issueId = $issue->getId();

// let's fetch it again to test the GET operation
$fetched = $usd->getIssue($issueId);
```
