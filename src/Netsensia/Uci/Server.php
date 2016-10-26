<?php
namespace Netsensia\Uci;

use Aws\Sdk;

class Server
{
    const TABLE_NAME = 'UciEngine';
    
    private $params;
    
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
        $this->createTable();
    }
    
    private function createTable()
    {
        $sdk = new Sdk($this->params);
        
        $client = $sdk->createDynamoDb();
        
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
    
    public function run()
    {
    }
}

