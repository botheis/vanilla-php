<?php
namespace Core{

    class Session{
        static protected $_instance;

        static public function getInstance(){
            return static::$_instance = (static::$_instance == NULL) ? new Session() : static::$_instance;
        }

        private function __construct(){}

        public function start($options = []){
            if(session_status() != PHP_SESSION_ACTIVE){
                session_start($options);
            }
        }

        public function stop(){
            session_destroy();
        }

        public function restart(){
            $this->stop();
            $this->start();
        }

        public function has($key){
            return (isset($_SESSION[$key])) ? true : false;
        }

        public function get($key=NULL){
            if($key == NULL){
                return $_SESSION;
            }
            if($this->has($key)){
                return $_SESSION[$key];
            }

            return NULL;
        }

        public function set($key, $value){
            $old = $this->get($key);
            $_SESSION[$key] = $value;
            return $old;
        }
    }
}