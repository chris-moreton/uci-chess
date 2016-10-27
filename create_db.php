<?php
include 'vendor/autoload.php';

use Aws\Sdk;

const REPORT_LOCATION = '/Users/Chris/Dropbox/Dashboard/UCITournament.csv';

$params = json_decode(file_get_contents('aws.json'), true);

$sdk = new Sdk($params);

$client = $sdk->createDynamoDb();

$createTable = true;

$tableName = 'UciEngine';

if ($createTable) {
    $result = $client->createTable([
        'TableName' => $tableName,
        'AttributeDefinitions' => [
            [ 'AttributeName' => 'Id', 'AttributeType' => 'S' ]
        ],
        'KeySchema' => [
            [ 'AttributeName' => 'Id', 'KeyType' => 'HASH' ]
        ],
        'ProvisionedThroughput' => [
            'ReadCapacityUnits'    => 5,
            'WriteCapacityUnits' => 6
        ]
    ]);
    
    $client->waitUntil('TableExists', [
        'TableName' => $tableName,
        '@waiter' => [
            'delay'       => 5,
            'maxAttempts' => 20
        ]
    ]);
    
    $lines = file(REPORT_LOCATION);
    
    for ($i=1; $i<count($lines); $i++) {
        $parts = str_getcsv($lines[$i]);
        
        $nameParts = explode(' ', $parts[0]);
        $result = $client->putItem(array(
            'TableName' => $tableName,
            'Item' => array(
                'Id' => ['S' => sha1($parts[0])],
                'Name'    => array('S' => $nameParts[0]),
                'StrengthPercent' => array('N' => str_replace('%', '', $nameParts[1])),
                'Elo'    => array('N' => $parts[1]),
                'Won'    => array('N' => "0"),
                'Lost'    => array('N' => "0"),
                'Drawn'    => array('N' => "0"),
                'IllegalMoves'    => array('N' => "0")
            )
        ));
        
        
    }
}



// $credentials = parse_ini_file('.server.aws');

// $dynamo = new DynamoDbClient([
//     'key' => $credentials['key'],
//     'secret' => $credentials['secret'],
//     'region' => $credentials['region'],
//     'profile' => $credentials['profile'],
// ]);