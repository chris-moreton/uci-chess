<?php
namespace Netsensia\Uci;

use Aws\Sdk;
use Netsensia\Tournament\RoundRobin\Schedule;

class Server
{
    const TABLE_NAME = 'UciEngine';
    
    private $params;
    private $sdk;
    private $dynamoDbClient;
    private $sqsClient;
    private $queueUrl;
    private $engines;
    private $itemsQueued = 0;
    private $resultsProcessed = 0;
    private $tournamentsToRun; 
    
    /**
     * 
     * @param array $params 
     *      ['version' => '', 
     *       'region' => '', 
     *       'credentials' => 
     *          ['key' => '', 
     *           'secret' => ''
     *          ]
     *      ]
     */
    public function __construct($params)
    {
        $this->params = $params;
        $this->sdk = new Sdk($this->params);
        $this->dynamoDbClient = $this->sdk->createDynamoDb();
        $this->sqsClient = $this->sdk->createSqs();
        
        $this->queueUrl = $this->getQueueUrl();
        
        $this->createTable();
        $this->populateEngineList();
    }
    
    private function populateEngineList()
    {
        $iterator = $this->dynamoDbClient->getIterator('Scan', [
            'TableName'     => self::TABLE_NAME
        ]);
        
        foreach ($iterator as $item) {
            $this->engines[] = $item;
        }
    }
    
    private function createTable()
    {
        $result = $this->dynamoDbClient->listTables();
        
        foreach ($result['TableNames'] as $tableName) {
            if ($tableName == self::TABLE_NAME) {
                echo 'Table UciEngine already exists' . PHP_EOL;
                // table already exists
                return;
            }
        }

        echo 'Creating table UciEngine' . PHP_EOL;
        
        $result = $this->dynamoDbClient->createTable([
            'TableName' => self::TABLE_NAME,
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
    
        $this->dynamoDbClient->waitUntil('TableExists', [
            'TableName' => self::TABLE_NAME,
            '@waiter' => [
                'delay'       => 5,
                'maxAttempts' => 20
            ]
        ]);
    }
    
    private function getQueueUrl()
    {
        $result = $this->sqsClient->createQueue(array('QueueName' => 'uci-tournament'));
        $queueUrl = $result->get('QueueUrl');
        return $queueUrl;
    }
    
    private function queueTournament()
    {
        $schedule = new Schedule(count($this->engines));
        
        usort($this->engines, function($a, $b) {
            return rand(-1,1);
        });
        
        for ($i=0; $i<2; $i++) {
            $schedule->reset();
            $pairing = $schedule->getNextPairing();
            while ($pairing !== null) {
    
                // looks complex, but just assigns white and black depending on which iteration we are on
                $whiteIndex = $pairing[$i % 2] - 1;
                $blackIndex = $pairing[abs(($i % 2) - 1)] - 1;
    
                $messageBody = 
                    json_encode([
                        'white' => $this->engines[$whiteIndex],
                        'black' => $this->engines[$blackIndex],
                    ]);
                    
                    
                $this->sqsClient->sendMessage([
                    'QueueUrl'    => $this->queueUrl,
                    'MessageBody' => $messageBody,
                    'Attributes' => array(
                        // The client gets 30 minutes to delete the message and respond with the result
                        // otherwise the game becomes visible again
                        'VisibilityTimeout' => 30 * 60 * 60, // 30 minutes
                    ),
                ]);
                
                $this->itemsQueued ++;
    
                $pairing = $schedule->getNextPairing();
            }
        }
    }
    
    private function processResults()
    {
        do {
            
        } while (++$this->resultsProcessed < $this->itemsQueued);
    }
    
    public function run()
    {
        $this->queueTournament();
        
        // $this->processResults();
    }
}

