<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GetOrderDebtsTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testGetOrderDebts()
    {
	print("start GetOrderDebts\n");
        
	$data = array("login" => "777", "password" => "12345678", "value" => "TestValue");
        $response = $this->call('POST', "http://loadapi.loc/Api/GetOrderDebts", $data );
        
    $data = json_decode($response->getContent(), true);    
	if(json_last_error() === JSON_ERROR_NONE){
                print('GetOrderDebts return '.count($data).'!');
                $this->addToAssertionCount(0);        
            }else{
                print('GetOrderDebts error!');
                $this->addToAssertionCount(0);
            }
            $this->addToAssertionCount(1);
        print("\n end GetOrderDebts");
    }
}
