<?php
namespace Netsensia\Uci;

class Server
{
    private $awsKey;
    private $awsSecret;
    
    public function __construct()
    {
        if (!file_exists('.server.aws')) {
            throw new \Exception('AWS server credentials file ".server.aws." not found');
        }
        
        $credentials = file('.server.aws');
        
        if (count($credentials) < 2) {
            throw new \Exception('Invalid content in ".server.aws"');
        }
        
        $this->awsKey = $credentials[0];
        $this->awsSecret = $credentials[1];
    }
    
    public function run()
    {
        
    }
}

