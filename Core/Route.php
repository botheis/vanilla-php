<?php

namespace Core{
    class Route{
        static protected $_list;

        private $_uri;
        private $_callback;
        private $_middlewares;

        public function __construct($methods, $uri, $callback, $middlewares=[]){
            if(!is_string($uri)){
                return;
            }
            $this->_uri = trim($uri, "/");

            $this->_callback = $callback;
            $this->_middlewares = [];

            $middlewares = is_array($middlewares) ? $middlewares : [$middlewares];
            foreach($middlewares as $middleware){
                $this->_middlewares[] = $middleware;
            }

            $methods = is_array($methods) ? $methods : [$methods];

            foreach($methods as $method){
                if(in_array($method, \Core\Request::$methods)){
                    if(empty(static::$_list[$method])){
                        static::$_list[$method] = [];
                    }

                    static::$_list[$method][] = $this;
                }
            }
        }

        static public function test($method, $uri){

            if(empty(\Core\Route::$_list[$method])){
                return false;
            }

            foreach(\Core\Route::$_list[$method] as $route){
                $matches = [];
                $uri = trim($uri, '/');

                $regex = preg_replace("#:([\w]+)#", "([\w]+)", $route->_uri);
                $regex = preg_replace("#\/\?([\w]+)?#", "\/?([\w]+)?", $regex);

                if(preg_match("#^".$regex."$#", $uri, $matches)){

                    array_shift($matches);
                    foreach($route->_middlewares as $middleware){
                        $middleware->execute();
                    }
                    call_user_func_array($route->_callback, $matches);
                    return true;
                }
            }
        }

        static public function redirect($uri){
            global $request;
            $uri = trim($uri, '/');

            if($uri != $request->config("uri")){
                header("location: /".$uri);
            }
        }
    }
}