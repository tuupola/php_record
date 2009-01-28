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

    public function __call($method, $params) {

        $var      = get_object_vars($this);
        $retval   = false;
        $property =  Record_Inflector::property($method);

        if (array_key_exists($property, $var)) {
            if (count($params)) {
                $this->$property = $params[0];
                $retval = true;
            } else {
                $retval = $this->$property;
            };
        }
        
        return($retval);  
    }
}