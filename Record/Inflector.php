<?php

/*
* Record_Inflector - Worlds simplest inflector
*
* Copyright (c) 2007 -2009 Mika Tuupola
*
* Licensed under the MIT license:
*   http://www.opensource.org/licenses/mit-license.php
*
* Project home:
*   http://www.appelsiini.net/
*
*/

class Record_Inflector {

    public function __construct() {
    }
    
    static function pluralize($word) {
        $pluralized = $word . "s";
        return $pluralized;
    }
    
    static function singularize($word) {
        $singularized = substr($word, 0, -1); 
        return $singularized;
    }

    static function camelize($word) {
        $camelized = str_replace(" ", "", ucwords(str_replace("_", " ", $word)));
        return $camelized;
    }
    
    static function underscore($word) {
        $underscored = strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $word));
        $underscored = strtolower(preg_replace('/([0-9])([a-z])/', '\\1_\\2', $underscored));        
        $underscored = strtolower(preg_replace('/([a-z])([0-9])/', '\\1_\\2', $underscored));        
        $underscored = preg_replace('/__/', '_', $underscored);        
        return $underscored;
    }
    
    static function humanize($word) {
        $humanized = ucwords(str_replace("_", " ", $word));
        return $humanized;
    }
    
    static function classify($table) {
        $classified = Record_Inflector::camelize(Record_Inflector::singularize($table));
        return $classified;
    }
    
    static function tableize($class) {
        $tableized = Record_Inflector::pluralize(Record_Inflector::underscore($class));
        return $tableized;
    }
    
    static function variable($string) {
        $variable = Record_Inflector::underscore($string);
        return $variable;
    }

    static function property($string) {
        $property = Record_Inflector::underscore($string);
        return $property;
    }
    
    static function method($string) {
        $method = Record_Inflector::camelize($string);
        return lcfirst($method);
    }

}
