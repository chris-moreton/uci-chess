<?php
namespace Netsensia\Uci;

use Aws\Sdk;
use Netsensia\Tournament\RoundRobin\Schedule;

class Server
{
    const TABLE_NAME = 'UciEngine';
    
    private $params;
    
    private $sdk;
    
    private $queueUrl;
    
    private $engines;
    
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
        $this->createTable();
        $this->populateEngineList();
        $this->queueUrl = $this->getQueueUrl();
    }
    
    private function populateEngineList()
    {
        
    }
    
    private function createTable()
    {
        $client = $this->sdk->createDynamoDb();
        
        $result = $client->listTables();
        
        foreach ($result['TableNames'] as $tableName) {
            if ($tableName == self::TABLE_NAME) {
                echo 'Table UciEngine already exists' . PHP_EOL;
                // table already exists
                return;
            }
        }

        echo 'Creating table UciEngine' . PHP_EOL;
        
        $result = $client->createTable([
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
    
        $client->waitUntil('TableExists', [
            'TableName' => self::TABLE_NAME,
            '@waiter' => [
                'delay'       => 5,
                'maxAttempts' => 20
            ]
        ]);
    }
    
    private function getQueueUrl()
    {
        $client = $this->sdk->createSqs();
        
        $result = $client->createQueue(array('QueueName' => 'uci-tournament'));
        $queueUrl = $result->get('QueueUrl');
        return $queueUrl;
    }
    
    public function run()
    {
        $schedule = new Schedule(count($this->engines));
        $client = $this->sdk->createSqs();
        
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
                
                $client->sendMessage(array(
                    'QueueUrl'    => $this->queueUrl,
                    'MessageBody' => [
                        'white' => $this->engines[$whiteIndex],
                        'black' => $this->engines[$blackIndex],
                    ],
                ));
    
                $pairing = $schedule->getNextPairing();
            }
        }

        $tournament->close();
    }
}

