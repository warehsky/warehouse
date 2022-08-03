<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GetWarehousesTest extends TestCase
{
    private $show=0;
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testLogin(){
        $tm = time();
        $p = hash("sha256", "777666".$tm);
        $data = array("login" => json_encode(array("login" => "777", "password" => $p, "timemark" => $tm)));
        $response = $this->call('POST', "Api/Login", $data );
        if($this->isJSON($response->getContent())){
            $dat = json_decode($response->getContent(), true);
            print('Login '.($dat['data']['fio']).'!');
            //print_r($dat);
            $this->assertTrue(true);
            return $dat['data'];
            $this->addToAssertionCount(0);        
        }else{
            print('Login error!');
            $this->assertTrue(false);
            return 0;
            $this->addToAssertionCount(0);
        }
    }
    /**
     * @depends testLogin
     */
    public function testGetWarehouses($login)
    {
        $show = $this->show;
        if( $show ) print("\nstart GetWarehouses\n");
        //$this->addToAssertionCount(1);
        $data = array("login" => json_encode(array("session" => $login['session'])));
        $response = $this->call('POST', "Api/GetWarehouses", $data );
        
        if($this->isJSON($response->getContent())){
            $dat = json_decode($response->getContent(), true);
            print("\nGetWarehouses return count=".count($dat['data'])."!");
            $this->assertSame('warehouse1', ($dat['data'][count($dat['data'])-1])["warehouse"]);
            $this->assertTrue(1 == count($dat['data']));
            //$this->assertNotEmpty($dat['data']);
            if( $show )
            foreach($dat['data'] as $r){
                print("\n");
                print_r($r);
            }
            $this->addToAssertionCount(0);        
        }else{
            print('Warehouses error!');
            $this->addToAssertionCount(0);
        }
        if( $show ) print("\n GetWarehouses end \n ");
    }
    /**
     * @depends testLogin
     */
    public function testGetClients($login)
    {
        $show = $this->show;
        //$show = 1;
        if( $show ) print("\nstart GetClients\n");
            
        $data = array("login" => json_encode(array("session" => $login['session'])));
        $response = $this->call('POST', "Api/GetClients", $data );
        
        if($this->isJSON($response->getContent())){
            $dat = json_decode($response->getContent(), true);
            print("\nGetClients return count=" . count($dat['data']));
            $this->assertSame('Квакин', ($dat['data'][0])["client"]);
            $this->assertTrue(2 == count($dat['data']));
            if( $show )
            foreach($dat['data'] as $r){
                print("\n");
                print_r($r);
            }
            $this->addToAssertionCount(0);        
        }else{
            print('Clients error!');
            $this->addToAssertionCount(0);
        }
        if( $show ) print("\n GetClients end \n ");
    }
}