--TEST--
Record::build()
--SKIPIF--
--FILE--
<?php 
require_once(dirname(__FILE__) . '/setup.php');
$params = array();
$params['first_name'] = 'Mika';
$params['last_name']  = 'Tuupola';
$params['age']        = 35;
$p = Person::build($params);
print_r($p);
?>
--GET--
--POST--
--EXPECT--
Person Object
(
    [first_name:protected] => Mika
    [last_name:protected] => Tuupola
    [age:protected] => 35
    [id:protected] => 
    [data:protected] => Array
        (
        )

)
