<?php

namespace Core{

    class Request{
        static protected $_instance;
        static public $methods = ["GET", "POST", "PUT", "PATCH", "DELETE"];
        private $_config;
        private $_headers;
        private $_get;
        private $_post;

        static public function getInstance(){
            return static::$_instance = (static::$_instance == NULL) ? new Request() : static::$_instance;
        }

        private function __construct(){
            $this->_headers = getallheaders();
            $this->_config = [];
            $this->_config["method"] = $this->hasHeader("X-HTTP-Method-Override") ? htmlentities($this->header("X-HTTP-Method-Override")) : htmlentities($_SERVER["REQUEST_METHOD"]);
            $this->_config["port"] = htmlentities($_SERVER["SERVER_PORT"]);
            $this->_config["protocol"] = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https" : "http";
            $this->_config["ip"] = htmlentities($_SERVER["REMOTE_ADDR"]);
            $this->_config["client"] = htmlentities($_SERVER["HTTP_USER_AGENT"]);
            $this->_config["uri"] = htmlentities($_GET["uri"]);
            unset($_GET['uri']);

            $this->_get = $_GET;
            $this->_post = $_POST;
        }

        public function hasHeader($key){
            return (!empty($this->_headers[$key])) ? true : false;
        }

        public function header($key=NULL, $value=NULL){
            if($key == NULL){
                return $this->_headers;
            }

            if($value == NULL){
                return ($this->hasHeader($key)) ? $this->_headers[$key] : NULL;
            }
            $old = ($this->hasHeader($key)) ? htmlentities($this->_headers[$key]) : NULL;
            $this->_headers[$key] = $value;

            return $old;
        }

        public function hasConfig($key){
            return (!empty($this->_config[$key])) ? true : false;
        }

        public function config($key=NULL, $value=NULL){
            if($key == NULL){
                return $this->_config;
            }

            if($value == NULL){
                return ($this->hasConfig($key)) ? $this->_config[$key] : NULL;
            }
            $old = ($this->hasConfig($key)) ? htmlentities($this->_config[$key]) : NULL;
            $this->_config[$key] = $value;

            return $old;
        }

        public function hasGet($key){
            return (!empty($this->_get[$key])) ? true : false;
        }

        public function get($key=NULL, $value=NULL){
            if($key == NULL){
                return $this->_get;
            }

            if($value == NULL){
                return ($this->hasGet($key)) ? $this->_get[$key] : NULL;
            }
            $old = ($this->hasGet($key)) ? htmlentities($this->_get[$key]) : NULL;
            $this->_get[$key] = $value;

            return $old;
        }

        public function hasPost($key){
            return (!empty($this->_post[$key])) ? true : false;
        }

        public function post($key=NULL, $value=NULL){
            if($key == NULL){
                return $this->_post;
            }

            if($value == NULL){
                return ($this->hasPost($key)) ? $this->_post[$key] : NULL;
            }
            $old = ($this->hasPost($key)) ? htmlentities($this->_post[$key]) : NULL;
            $this->_post[$key] = $value;

            return $old;
        }

    };
}