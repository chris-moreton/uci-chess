<?php

use Netsensia\Uci\Server;

/**
 * A test script that sets up a round robin tournament with three engines configured at
 * multiple perecent strengths. Runs the tournament an infinite number of times, saving
 * the current ELO of each engine in a file after each match.
 */

include 'vendor/autoload.php';
const REPORT_LOCATION = '/Users/Chris/Dropbox/Dashboard/UCITournament.csv';

$server = new Server(json_decode(file_get_contents('aws.json'), true));

$server->run();


