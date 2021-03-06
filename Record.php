<?php

/*
* Record - Simple Active Record implementation in PHP
*
* Copyright (c) 2002-2011 Philippe Archambault, Mika Tuupola
*
* Licensed under the MIT license:
*   http://www.opensource.org/licenses/mit-license.php
*
* Project home:
*   http://www.appelsiini.net/
*
* Code is combination Model class from Green Framework by
* Philippe Archambault and DB_DataContainer + MDB2_DataContainer2 by
* Mika Tuupola (thats me):
* 
*   http://code.google.com/p/green-framework/
*   http://svn.appelsiini.net/viewvc/php/trunk/DB_DataContainer/
*   http://svn.appelsiini.net/viewvc/php/trunk/MDB2_DataContainer2/
*
*/

require_once 'Record/Overload.php';

class Record extends Record_Overload {
    
    public static $dbh = false;
    
    protected $id;
    protected static $has_one    = array();
    protected static $has_many   = array();
    protected static $belongs_to = array();
    protected static $habtm      = array();
    protected $data = array();
        
    /**
     * Set or get the database connection.
     */
     
    final public static function connection() {
        if (func_num_args() > 0) {
            self::$dbh = func_get_arg(0);
        } else {
            return self::$dbh;            
        }
    }
    
    public function columns() {
        $columns = array_keys(get_object_vars($this));
        $key = array_search('data', $columns);
        unset($columns[$key]);
        return $columns;
    }
    
    public function beforeSave() { return true; }
    public function beforeInsert() { return true; }
    public function beforeUpdate() { return true; }
    public function beforeDelete() { return true; }
    public function afterSave() { return true; }
    public function afterInsert() { return true; }
    public function afterUpdate() { return true; }
    public function afterDelete() { return true; }
    
    /**
     * Construct new object using given attributes.
     *
     * @return object
     */
    public static function build($attribute_array=array()) {
        $class  = get_called_class();
        $object = new $class;
        foreach ($attribute_array as $key => $attribute) {
            $method = Record_Inflector::method($key);
            $object->$method($attribute);
        }
        return $object;
    }

    /**
     * Construct new object using given attributes and save it to database.
     *
     * @return object
     */
    public static function create($attribute_array=array()) {
        $class  = get_called_class();
        $object = $class::build($attribute_array);
        $object->save();
        return $object;
    }
    
    function properties($params) {
        if (is_array($params)) {
            foreach ($params as $key => $value) {
                $method = Record_Inflector::method($key);
                $this->$method($value);
            }
        }
    }
    
    /**
     * Generates a insert or update string from the supplied data and execute it
     *
     * @return boolean
     */
    public function save() {
        if (!$this->beforeSave()) return false;
        
        $value_of = array();
        if (empty($this->id)) {
            
            if (!$this->beforeInsert()) return false;
                        
            /* Escape values. */
            foreach ($this->columns() as $column) {
                if (isset($this->$column)) {
                    $value_of[$column] = self::$dbh->quote($this->$column);
                }
            }
            
            $columns = implode(', ', array_keys($value_of));
            $values  = implode(', ', array_values($value_of));
            $sql     = sprintf('INSERT INTO %s (%s) VALUES (%s)', $this->table(), $columns, $values);

            /* Force retval to be boolean. */
            $return = self::$dbh->exec($sql) !== false;
            /* TODO: This wont always work. */
            $this->id = self::lastInsertId();
            
            $class = get_class($this);
            $key   = $class . '_id';

            /* Save all has_one's */
            foreach ($class::$has_one as $one) {
                /* Save only if we have one. */
                if ($this->$one()) {
                    $setter = Record_Inflector::method($key);
                    $this->$one()->$setter($this->id());
                    $this->$one()->save();                    
                }
            };
            
            /* Save all has_many's */
            foreach ($class::$has_many as $many) {
                /* Save only if we have one. */
                if ($this->$many()) {
                    foreach ($this->$many() as $item) {
                        $setter = Record_Inflector::method($key);
                        $item->$setter($this->id());
                        $item->save();                                            
                    }
                }
            };
             
            if (! $this->afterInsert()) return false;
        
        } else {
            
            if (! $this->beforeUpdate()) return false;
                        
            /* Escape values. */
            foreach ($this->columns() as $column) {
                if (isset($this->$column)) {
                    $value_of[$column] = $column . '=' . self::$dbh->quote($this->$column);
                }
            }
            
            unset($value_of['id']);

            $items   = implode(', ', $value_of);
            $sql     = sprintf('UPDATE %s SET %s WHERE id=%d', $this->table(), $items, $this->id());
            
            /* Force retval to be boolean. */
            $retval = self::$dbh->exec($sql) !== false;
            
            if (! $this->afterUpdate()) return false;
        }

        if (! $this->afterSave()) return false;
        
        return true;
    }
    
    public function delete() {
        if (!$this->beforeDelete()) return false;
        
        $sql = sprintf('DELETE FROM %s WHERE id=%d LIMIT 1', $this->table(), $this->id());

        $retval = self::$dbh->exec($sql) !== false;

        if (!$this->afterDelete()) {
            $this->save();
            $retval = false;
        }
                
        return $retval;
    }
    
    public function lastInsertId() {
        return self::$dbh->lastInsertId(); 
    }
    
    public function table() {
        /* Constant TABLE defined in model overrides the default table name. */
        if (defined(get_class($this) . '::TABLE')) {
            return constant(get_class($this) . '::TABLE');                
        } else {
            return Record_Inflector::tableize(get_class($this));            
        }
    }
    
    public static function count($params=null) {
        $class = get_called_class();
        $params['select'] = 'COUNT(*)';
        $sql = Record::buildSql($params, $class);
        return self::$dbh->query($sql, PDO::FETCH_COLUMN, 0)->fetch();
    }

    public static function findById($id) {
        $class = get_called_class();
        $params['where'] = sprintf('id=%d', $id);
        $sql = Record::buildSql($params, $class);
        return self::$dbh->query($sql, PDO::FETCH_CLASS, $class)->fetch();
    }

    public static function find() {
        $args     = func_get_args();
        $class    = get_called_class();
        $modifier = ':all';
        $params   = array();

        if (isset($args[0])) {
            /* Record::find(array()) */
            if (is_array($args[0])) {
                $params = $args[0];
            } else {
                if (intval($args[0]) !='') {
                    /* Record::find(6) */
                    $id = intval($args[0]);
                    $params['where'] = sprintf('id=%d', $id);
                    $modifier = ':one';
                } else {
                    $modifier = $args[0];
                    if (is_array($args[1])) {
                        /* Record::find(':all', array()) */
                        $params = $args[1];                        
                    } else if (intval($args[1]) != '') {
                        /* Record::find(':one', 6) */
                        $id = intval($args[1]);
                        /* Integer is primary key ... */
                        $params['where'] = sprintf('id=%d', $id);
                    }
                }
            }            
        }

        $sql    = Record::buildSql($params, $class);    

        $retval = array();
        
        foreach (self::$dbh->query($sql, PDO::FETCH_CLASS, $class) as $object) {
            $key   = $class . '_id';

            /* Load all has_one's */
            foreach ($class::$has_one as $one) {
                $one_class = Record_Inflector::classify(Record_Inflector::pluralize($one));
                $finder = Record_Inflector::finder($key);
                $one_object = $one_class::$finder(':one', $object->id());
                $object->data[$one] = $one_object;                
            };

            /* Load all has_manys's */
            foreach ($class::$has_many as $many) {
                $many_class = Record_Inflector::classify($many);
                $finder = Record_Inflector::finder($key);
                $all_objects = $many_class::$finder(':all', $object->id());
                $object->data[$many] = $all_objects;                
            };

            $retval[] = $object;
        }
        if (':one' == $modifier) {
            return $retval[0];
        } else {
            return $retval;            
        }
    }
    
    private static function buildSql($params, $class) {
        $dummy  = new $class;        
        $table  = $dummy->table();
        
        $select = isset($params['select']) ? $params['select'] :  implode(',', $dummy->columns());
        
        $sql = "SELECT $select FROM $table ";
        
        if (isset($params['where'])) {
            $sql .= " WHERE " . $params['where'];
        }
        
        if (isset($params['order_by'])) {
            $sql .= " ORDER BY " . $params['order_by'];
        } elseif (isset($params['order'])) {
            $sql .= " ORDER BY " . $params['order'];
        }
        
        if (isset($params['limit'])) {
            if (is_array($params['limit'])) {
                $from  = $params['limit'][0];
                $count = $params['limit'][1];
            } else {
                /* split by whitespace and/or comma */
                $temp = preg_split ('/[\s,]+/', $params['limit'], 2);
                if (count($temp) == 2) {
                  $from  = $temp[0];
                  $count = $temp[1];
                } else {
                  $from  = 0;
                  $count = $temp[0];
                }
            }
            $sql .= "LIMIT $from, $count ";
        }
        return $sql;
    }
        
}