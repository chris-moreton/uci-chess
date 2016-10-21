<?php
include 'vendor/autoload.php';

use Aws\Sdk;

$params = json_decode(file_get_contents('aws.json'), true);

$sdk = new Sdk($params);

$client = $sdk->createDynamoDb();

$result = $client->createTable([
    'TableName' => 'UciEngine',
    'AttributeDefinitions' => [
        [ 'AttributeName' => 'EngineId', 'AttributeType' => 'S' ]
    ],
    'KeySchema' => [
        [ 'AttributeName' => 'EngineId', 'KeyType' => 'HASH' ]
    ],
    'ProvisionedThroughput' => [
        'ReadCapacityUnits'    => 5,
        'WriteCapacityUnits' => 6
    ]
]);

$client->waitUntil('TableExists', [
    'TableName' => 'UciEngine',
    '@waiter' => [
        'delay'       => 5,
        'maxAttempts' => 20
    ]
]);

$engineName = 'Adsasdasd';
$result = $client->putItem(array(
    'TableName' => 'UciEngine',
    'Item' => array(
        'EngineId' => ['S' => sha1($engineName)],
        'Elo'    => array('N' => "123"),
    )
));

// $credentials = parse_ini_file('.server.aws');

// $dynamo = new DynamoDbClient([
//     'key' => $credentials['key'],
//     'secret' => $credentials['secret'],
//     'region' => $credentials['region'],
//     'profile' => $credentials['profile'],
// ]);