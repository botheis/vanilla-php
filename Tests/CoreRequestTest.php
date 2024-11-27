<?php
namespace Tests;

// Require request context
$_SERVER["REQUEST_METHOD"] = "GET";
$_SERVER["SERVER_PORT"] = 80;
$_SERVER["HTTP_USER_AGENT"] = "PHPUnit";
$_SERVER["REMOTE_ADDR"] = "localhost";

// Require GET context 
$_GET["uri"] = "<script>alert('xss dangerosity')</script>";
$_GET["page"] = 2;
$_GET["xss"] = "<script>alert('xss dangerosity')</script>";

// Require POST context
$_POST["form"] = "subscribe";
$_POST["name"] = "unittest";
$_POST["xss"] = "<script>alert('xss dangerosity')</script>";

class CoreRequestTest extends \PHPUnit\Framework\TestCase {

    public function testConstruct(){
        // $this->expectedException(\Exception::class);
        $this->expectExceptionMessage("Call to private Core\Request::__construct");
        new \Core\Request();
    }

    public function testGetInstance(){
        $request = \Core\Request::getInstance();

        $this->assertInstanceOf(\Core\Request::class, $request, "\Core\Request Instanciation failed");
    }

    public function testIsArray(){
        $request = \Core\Request::getInstance();
        $this->assertIsArray($request->header());
        $this->assertIsArray($request->config());
        $this->assertIsArray($request->get());
        $this->assertIsArray($request->post());
    }

    public function testIsBool(){
        $request = \Core\Request::getInstance();
        $this->assertIsBool($request->hasHeader("unitttest"));
        $this->assertIsBool($request->hasConfig("uri"));
        $this->assertIsBool($request->hasGet("page"));
        $this->assertIsBool($request->hasPost("form"));
    }

    // ----
    // Tests on header
    // ----
    public function testHasHeader(){
        $request = \Core\Request::getInstance();
        $this->assertEquals(false, $request->hasHeader("unittest"));
    }

    public function testSetHeader(){
        $request = \Core\Request::getInstance();

        $result1 = $request->header("unittest", "phpunit");
        $this->assertNull($result1);
        $result2 = $request->header("unittest", "PHPUnit");
        $this->assertIsString($result2);
        $this->assertEquals("phpunit", $result2);
    }
    
    public function testGetHeader(){
        $request = \Core\Request::getInstance();
        $this->assertIsString($request->header("unittest"));
        $this->assertEquals("PHPUnit", $request->header("unittest"));
        $this->assertEquals(["unittest"=>"PHPUnit"], $request->header());   
    }

    // ----
    // Tests on config
    // ----
    public function testHasConfig(){
        $request = \Core\Request::getInstance();
        $this->assertEquals(false, $request->hasConfig("unittest"));
    }

    public function testSetConfig(){
        $request = \Core\Request::getInstance();

        $result1 = $request->config("unittest", "phpunit");
        $this->assertNull($result1);
        $result2 = $request->config("unittest", "PHPUnit");
        $this->assertIsString($result2);
        $this->assertEquals("phpunit", $result2);
    }
    
    public function testGetConfig(){
        $request = \Core\Request::getInstance();
        $this->assertIsString($request->config("unittest"));
        $this->assertEquals("PHPUnit", $request->config("unittest"));
        $this->assertArrayHasKey("method", $request->config());
        $this->assertArrayHasKey("port", $request->config());
        $this->assertArrayHasKey("protocol", $request->config());
        $this->assertArrayHasKey("ip", $request->config());
        $this->assertArrayHasKey("client", $request->config());
        $this->assertArrayHasKey("uri", $request->config());
    }

    // ----
    // Tests on get
    // ----
    public function testHasGet(){
        $request = \Core\Request::getInstance();
        $this->assertEquals(false, $request->hasGet("unittest"));
    }

    public function testSetGet(){
        $request = \Core\Request::getInstance();

        $result1 = $request->get("unittest", "phpunit");
        $this->assertNull($result1);
        $result2 = $request->get("unittest", "PHPUnit");
        $this->assertIsString($result2);
        $this->assertEquals("phpunit", $result2);
    }
    
    public function testGetGet(){
        $request = \Core\Request::getInstance();
        $this->assertIsString($request->get("unittest"));
        $this->assertEquals("PHPUnit", $request->get("unittest"));
        $this->assertArrayNotHasKey("uri", $request->get());
    }

    // ----
    // Tests on post
    // ----
    public function testHasPost(){
        $request = \Core\Request::getInstance();
        $this->assertEquals(false, $request->hasPost("unittest"));
    }

    public function testSetPost(){
        $request = \Core\Request::getInstance();

        $result1 = $request->post("unittest", "phpunit");
        $this->assertNull($result1);
        $result2 = $request->post("unittest", "PHPUnit");
        $this->assertIsString($result2);
        $this->assertEquals("phpunit", $result2);
    }
    
    public function testGetPost(){
        $request = \Core\Request::getInstance();
        $this->assertIsString($request->post("unittest"));
        $this->assertEquals("PHPUnit", $request->post("unittest"));
        $this->assertArrayNotHasKey("uri", $request->post());
    }

    public function testXssSecurity(){
        $request = \Core\Request::getInstance();
        $this->assertStringNotContainsString('<script>', $request->config('uri'));
        $this->assertStringNotContainsString('<script>', $request->get('xss'));
        // $this->assertStringNotContainsString('<script>', $request->post('xss'));
    }
}