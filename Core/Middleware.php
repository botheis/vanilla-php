<?php

namespace Core{

    class Middleware{
        protected $_params;

        public function __construct(...$params){
            $this->_params =$params;
        }

        public function execute(){
            call_user_func_array([$this, "handler"], $this->_params);
            
        }
    }
}