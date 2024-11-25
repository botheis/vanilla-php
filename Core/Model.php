<?php

namespace Core{

    class Model{
        static protected $_db;
        static protected $_instance;

        /**
         * Try to select the specified ressources with the clauses on the database
         *
         * @param string $element table name
         * @param array $where causes where to add on the request.
         *
         * The $where clause is an array of arrays i.e.:
         * [
         *  ["id", "=", 5],
         *  ["AND", "id_role", "=", 1]
         * ]
         *
         * In this example we can see AND and OR clauses can be added indifferently
         *
         * @param int $offset add offset clause to the request
         * @param int $limit add limit clause to the request
         * @param array $order add the order clause to the request.
         *
         * The $order clause is declared in an array : [ "column_name", "asc" ]
         */
        public function select(string $element, array $where = [], int $offset=0, int $limit=-1, array $order=[]){
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

        /**
         * Count the elements based on the $where clause
         *
         * @param string $element table name
         * @param array $where causes where to add on the request.
         *
         * The $where clause is an array of arrays i.e.:
         * [
         *  ["id", "=", 5],
         *  ["AND", "id_role", "=", 1]
         * ]
         *
         * In this example we can see AND and OR clauses can be added indifferently
         *
         * @return int the final count
         */
        public function count(string $element, $where = []){
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

        /**
         * Update the specified table with the values contained in $fields, based on $where clause
         *
         * @param string $element table to update
         * @param array $fields associative array containing the new values
         * @param array $where causes where to add on the request.
         *
         * The $where clause is an array of arrays i.e.:
         * [
         *  ["id", "=", 5],
         *  ["AND", "id_role", "=", 1]
         * ]
         *
         * In this example we can see AND and OR clauses can be added indifferently
         *
         */
        public function update(string $element, array $fields, $where=[]){
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

        /**
         * Insert datas into table specified by $element
         *
         * @param string $element the selected table
         * @param array $fields associative array containing the new values
         * @return mixed last inserted id on success, else false
         */
        public function insert(string $element, array $fields){
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


        /**
         * Delete rows from table specified by $element
         *
         * @param string $element the selected table
         * @param array $where causes where to add on the request.
         *
         * The $where clause is an array of arrays i.e.:
         * [
         *  ["id", "=", 5],
         *  ["AND", "id_role", "=", 1]
         * ]
         *
         * In this example we can see AND and OR clauses can be added indifferently
         * @return boolean true if success else false
         */
        public function delete(string $element, array $where = []){
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

        /**
         * Execute bulk query
         *
         * @param string $statment the sql request
         * @param array $params the params needed to the prepare statment.
         *
         * @return mixed depend on the type of request (select, insert, update|delete), try to determine an adequat result.
         */
        public function exec(string $statment, array $params=[]){
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