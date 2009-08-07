<?php

/*
* Record_Overload - Overloaded accessor and mutator methods
*
* Copyright (c) 2009 Mika Tuupola
*
* Licensed under the MIT license:
*   http://www.opensource.org/licenses/mit-license.php
*
* Project home:
*   http://www.appelsiini.net/
*
*/

require_once 'Record/Inflector.php';

class Record_Overload {

    public function __call($method, $arguments) {

        $var      = get_object_vars($this);
        $retval   = false;
        $property =  Record_Inflector::property($method);

        if (array_key_exists($property, $var)) {
            if (count($arguments)) {
                $this->$property = $arguments[0];
                $retval = true;
            } else {
                $retval = $this->$property;
            };
        } else {
            $class = get_class($this);
            if (in_array($method, $class::$has_one)) {
                /* Setter for has_one */
                if (count($arguments)) { 
                    $this->data[$method] = $arguments[0];
                    $retval = true;
                /* Getter for has_one */
                } else {
                    $data = $this->data();
                    $retval = $data[$method];
                }
            } else {
                trigger_error("Overloaded call to undefined method $class->$method()", E_USER_ERROR);
            }
        }
        
        return $retval;
    }
    
   public static function __callStatic($method, $arguments) {
       if (2 == count($arguments)) {
           $modifier = $arguments[0];
           $value    = $arguments[1];
       } else {
           $modifier = ':all';
           $value    = $arguments[0];
       }
       if (false !== strpos($method, 'findBy')) {
           $variable = Record_Inflector::variable(str_replace('findBy', '', $method));
           $params['where'] = sprintf('%s="%s"', $variable, $value);
       };
       
              
       $class = get_called_class();
       return $class::find($modifier, $params);
   }
   
}