<?php

namespace Core{

    class Model{
        static protected $_db;
        static protected $_instance;

        public function select($element, $where = [], $offset=0, $limit=-1, $order=[]){
            $sql = "SELECT * FROM $element";
            $params = [];
            $i = 0;
            foreach($where as $clause){
                $str = "";
                if($i == 0){
                    $str .= " WHERE ";
                }
                else if (preg_match("#or#i", $clause[0])){
                    $str .= " OR ";
                    array_shift($clause);
                }
                else{
                    $str .= " AND ";
                }
                $params[] = $clause[2];
                $clause[2] = ' ? ';
                $str .= implode(" ", $clause);

                $sql .= $str;

                $i++;
            }

            if($order != [] && preg_match("#asc|desc#i", $order[1])){
                $sql .= " ORDER BY ".implode(' ', $order);
            }

            if($limit == -1){
                $sql .= " LIMIT ".$offset.',999999999999999999';
            }
            else{
                $sql .= " LIMIT ".$offset.','.$limit;
            }

            


            $query = static::$_db->prepare($sql);
            $query->execute($params);
            
            $datas = $query->fetchAll(\PDO::FETCH_ASSOC);
            
            return $datas;
        }

        public function count($element, $where = []){
            $sql = "SELECT count(*) as nb FROM $element";
            $params = [];
            $i = 0;
            foreach($where as $clause){
                $str = "";
                if($i == 0){
                    $str .= " WHERE ";
                }
                else if (preg_match("#or#i", $clause[0])){
                    $str .= " OR ";
                    array_shift($clause);
                }
                else{
                    $str .= " AND ";
                }
                $params[] = $clause[2];
                $clause[2] = ' ? ';
                $str .= implode(" ", $clause);

                $sql .= $str;

                $i++;
            }

            $query = static::$_db->prepare($sql);
            $query->execute($params);
            
            $datas = $query->fetch(\PDO::FETCH_ASSOC);
            
            return $datas["nb"];
        }

        public function update($element, $fields, $where){
            $sql = "UPDATE $element SET ";

            $elements = [];
            $params = [];
            foreach($fields as $field=>$value){
                $params[] = $value;
                $elements[] = $field.'= ?';
            }
            $sql .= implode(', ', $elements);
            $i = 0;
            foreach($where as $clause){
                $str = "";
                if($i == 0){
                    $str .= " WHERE ";
                }
                else if (preg_match("#or#i", $clause[0])){
                    $str .= " OR ";
                    array_shift($clause);
                }
                else{
                    $str .= " AND ";
                }
                $params[] = $clause[2];
                $clause[2] = ' ? ';
                $str .= implode(" ", $clause);

                $sql .= $str;

                $i++;
            }

            static::$_db->beginTransaction();
            try{
                $query = static::$_db->prepare($sql);
                $query->execute($params);
            }
            catch(\PDOException $e){
                static::$_db->rollback();
                return false;
            }
            static::$_db->commit();

            return true;
        }

        public function insert($element, $fields){
            $sql = "INSERT INTO $element";

            $elements = [];
            $params = [];
            $values = [];
            foreach($fields as $field=>$value){
                $params[] = $value;
                $elements[] = $field;
                $values[] = '?';
            }
            $sql .= '('.implode(', ', $elements).') VALUES('.implode(',', $values).')';

            static::$_db->beginTransaction();
            try{
                $query = static::$_db->prepare($sql);
                $query->execute($params);
            }
            catch(\PDOException $e){
                static::$_db->rollback();
                return false;
            }
            static::$_db->commit();



            $queryId = static::$_db->prepare("SELECT MAX(id) as lastId from $element");
                $resultId = NULL;
                $lastId = 0;
                try{
                    $queryId->execute([]);
                }
                catch(\PDOException $e){
                    return $lastId;
                }

                $lastId = $queryId->fetch(\PDO::FETCH_ASSOC)["lastId"];
                return $lastId;

            return $datasId;
        }


        public function delete($element, $where){
            $sql = "DELETE FROM $element";

            $i = 0;
            foreach($where as $clause){
                $str = "";
                if($i == 0){
                    $str .= " WHERE ";
                }
                else if (preg_match("#or#i", $clause[0])){
                    $str .= " OR ";
                    array_shift($clause);
                }
                else{
                    $str .= " AND ";
                }
                $params[] = $clause[2];
                $clause[2] = ' ? ';
                $str .= implode(" ", $clause);

                $sql .= $str;

                $i++;
            }

            static::$_db->beginTransaction();
            try{
                $query = static::$_db->prepare($sql);
                $query->execute($params);
            }
            catch(\PDOException $e){
                static::$_db->rollback();
                return false;
            }
            static::$_db->commit();

            return true;
        }

        public function exec($statment, $params=[]){
            static::$_db->beginTransaction();
            $query = static::$_db->prepare($statment);
            $result = NULL;
            try{
                $query->execute($params);
            }
            catch(\PDOException $e){
                static::$_db->rollback();
                return false;
            }

            static::$_db->commit();

            if(preg_match("#^select#i", $statment)){
                $result = $query->fetchAll(\PDO::FETCH_ASSOC);
                return $result;
            }
            else if(preg_match("#^insert#i", $statment)){
                $match = [];
                $table = "";
                preg_match("#into\ (.*) \(#i", $statment);
                if($match != []){
                    array_shift($match);
                    $table = $match[0];
                }
                $queryId = static::$_db->prepare("SELECT MAX(id) as lastId from $table");
                $resultId = NULL;
                $lastId = 0;
                try{
                    $queryId->execute([]);
                }
                catch(\PDOException $e){
                    return $lastId;
                }

                $lastId = $queryId->fetch(\PDO::FETCH_ASSOC)["lastId"];
                return $lastId;
            }
            return $query;
        }

    };
}