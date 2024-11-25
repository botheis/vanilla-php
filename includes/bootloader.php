<?php
/**
 * \brief this is the real starting point.
 * This page is not reachable from outside, it's called from index
 */

require_once("autoloader.php");

// Load configuration
// ----
$config = parse_ini_file("../config.ini", true);
$config["databases"]["list"] = explode(",", $config["databases"]["list"]); 


// Load incomming request object
// ----
$request = \Core\Request::getInstance();

// Load Session object
// ----
$session = \Core\Session::getInstance();
$session->start();

// Load databases connectors
// ----
$dbs = [];
foreach($config["databases"]["list"] as $name){
    if(!empty($config["db_".$name])){
        extract($config["db_".$name]);

        try{
            $db = new PDO("$dbengine:host=$dbhost;dbport=$dbport; dbname=$dbname;", $dbuser, $dbpassword);
            $dbs[$name] = $db;
        }
        catch(\Exception $e){
            exit;
        }
    }
}

// Load pre-process middlewares
// ----
require_once("preprocess.php");

// Load routes
// ----
require_once("routes.php");

// Test the incomming route
// ----
$load = \Core\Route::test($request->config("method"), $request->config("uri"));

// Default case : status 404
if($load == false){
    \Core\Route::redirect("/error/404");
}
?>