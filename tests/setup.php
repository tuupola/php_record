<?php

try {
    $dbh = new PDO('sqlite:/tmp/record.sqlite');
} catch(PDOException $e) {
    print $e->getMessage() .  "\n";
}

$dbh->query("DROP TABLE persons");
$dbh->query("
CREATE TABLE persons (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    first_name VARCHAR(255),
    last_name VARCHAR(255),
    age SMALLINT UNSIGNED)
");

$dbh->query("DROP TABLE computers");
$dbh->query("
CREATE TABLE computers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    person_id INTEGER UNSIGNED NOT NULL,
    mark VARCHAR(255)
)");

require_once 'Record.php';

class Person extends Record {
    
    protected $first_name;
    protected $last_name;
    protected $age;
 
    public static $has_one  = array('computer');
    public static $has_many = array('cars');
    
}

class Computer extends Record {
    
    protected $mark;
    protected $person_id;

    protected static $belongs_to = array('person');
    
}

class Car extends Record {
    
    protected $mark;
    protected $person_id;

    protected static $belongs_to = array('person');
    
}

Record::connection($dbh);