<?php
/**
 * \brief this is the real starting point.
 * This page is not reachable from outside, it's called from index
 */

require_once("autoload.php");

// Load configuration
// ----
$config = parse_ini_file("../config.ini", true);


// Load databases connectors
// ----
if(!empty($config["databases"])){
    if($config["databases"]["list"] != "")
        $config["databases"]["list"] = explode(",", $config["databases"]["list"]);
    else
    $config["databases"]["list"] = [];

    $dbs = [];
    foreach($config["databases"]["list"] as $name){
        if(!empty($config["db_".$name])){
            $arr = &$config["db_".$name];
            $dbengine = (!empty($arr["dbengine"])) ? htmlspecialchars($arr["dbengine"]) : "";
            $dbhost = (!empty($arr["dbhost"])) ? htmlspecialchars($arr["dbhost"]) : "";
            $dbport = (!empty($arr["dbport"])) ? htmlspecialchars($arr["dbport"]) : "";
            $dbuser = (!empty($arr["dbuser"])) ? htmlspecialchars($arr["dbuser"]) : "";
            $dbpassword = (!empty($arr["dbpassword"])) ? htmlspecialchars($arr["dbpassword"]) : "";
            $dbname = (!empty($arr["dbname"])) ? htmlspecialchars($arr["dbname"]) : "";
            // extract($config["db_".$name]);
            try{
                $db = new PDO("$dbengine:host=$dbhost;dbport=$dbport; dbname=$dbname;", $dbuser, $dbpassword);
                $dbs[$name] = $db;
            }
            catch(\Exception $e){
            }
        }
    }
}

// Load incomming request object
// ----
$request = \Core\Request::getInstance();

// Load Session object
// ----
$session = \Core\Session::getInstance();
$session->start();



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