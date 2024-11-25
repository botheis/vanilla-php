<?php
/**
 * \brief : Load all the classes called through the php files
 */
spl_autoload_register(function($name){
    $name = trim($name, '\\');
    $name = str_replace("\\", "//", $name);

    $filename = "../".$name.".php";
    if(is_file($filename)){
        require_once($filename);
    }
});