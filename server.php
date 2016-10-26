<?php

use Netsensia\Uci\Server;
use Aws\Sdk;

/**
 * A test script that sets up a round robin tournament with three engines configured at
 * multiple perecent strengths. Runs the tournament an infinite number of times, saving
 * the current ELO of each engine in a file after each match.
 */

include 'vendor/autoload.php';
const REPORT_LOCATION = '/Users/Chris/Dropbox/Dashboard/UCITournament.csv';

$server = new Server(json_decode(file_get_contents('aws.json'), true));

$sdk = new Sdk(json_decode(file_get_contents('aws.json'), true));

$client = $sdk->createDynamoDb();

$lines = file(REPORT_LOCATION);

for ($i=1; $i<count($lines); $i++) {
    $parts = str_getcsv($lines[$i]);

    $result = $client->putItem(array(
        'TableName' => 'UciEngine',
        'Item' => array(
            'Id' => ['S' => sha1($parts[0])],
            'Name'    => array('S' => $parts[0]),
            'Elo'    => array('N' => $parts[1]),
            'Won'    => array('N' => "0"),
            'Lost'    => array('N' => "0"),
            'Drawn'    => array('N' => "0"),
            'IllegalMoves'    => array('N' => "0")
        )
    ));
}

