<?php

namespace Core{

    class Middleware{
        protected $_params;

        /**
         * Create a \Core\Middleware::class instance
         * @param mixed $params variable-length params. The number of parameters is depending on the inherited classes needs.
         *
         * @warning Do not use directly this class, needs to be inherited.
         */
        public function __construct(...$params){
            $this->_params =$params;
        }

        /**
         * On inherited classes, the public method handler must be declared. It can takes as many params as needed.
         */
        // public function handler(...){}

        /**
         * execute the inherited class::handler method
         */
        public function execute(){
            call_user_func_array([$this, "handler"], $this->_params);

        }
    }
}