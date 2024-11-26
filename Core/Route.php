<?php

namespace Core{
    class Route{
        static protected $_list;

        private $_uri;
        private $_callback;
        private $_middlewares;

        /**
         * Create a new \Core\Route::class instance.
         * @param mixed $methods can be a string or an array of methods. The methods list can be found in \Core\Request::methods
         * @param string $uri correspond to the uri used to access to the page.
         *  - It's possible to declare mandatory parameter in uris using :, i.e.: /mypage/:id
         *  - It's possible to declare optionnal parameter in uris using ?, i.e.: /mypage/?id
         *  - Mandatory and optionnal parameters can be mixed on single uris, i.e.: /mypage/:id/sub/?value
         * @param callable $callback refers to the function/method called when this uri matches with the route.
         * @param mixed $middlewares can be a \Core\Middleware or array of \Core\Middleware objects.
         * These middlewares are interceptors used to do some verifications or modifications on the workflow.
         * @see includes/routes.php to see the routes declarations
         */
        public function __construct(mixed $methods, string $uri, mixed $callback, mixed $middlewares=[]){
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

        /**
         * Test the uri called by the client. If exists, load the page.
         * @param string $method must be one among the \Core\Request::methods list AND one of the declared during the route creation.
         * @param string $uri called by the client through the request
         * @return boolean true if an existing route matches with specified uri
         */
        static public function test(string $method, string $uri){
            if(empty(\Core\Route::$_list[$method])){
                return false;
            }

            $uri = trim($uri, '/');
            foreach(\Core\Route::$_list[$method] as $route){
                $matches = [];

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

        /**
         * Proceed a unconditionnal redirection to the specified uri.
         * @param string $uri new uri
         * @param array $params additionnal GET parameters
         */
        static public function redirect(string $uri){
            global $request;
            $uri = trim($uri, '/');

            // TODO : add extra params to the uri from $params
            if($uri != $request->config("uri")){
                header("location: /".$uri);
            }
        }
    }
}