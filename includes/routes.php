<?php


new \Core\Route("GET", "/error/:status", function($status){
    echo 'Status '.htmlentities($status);
});