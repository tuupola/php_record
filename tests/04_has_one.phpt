--TEST--
Record::has_one
--SKIPIF--
--FILE--
<?php 
require_once(dirname(__FILE__) . '/setup.php');
$params = array();
$params['first_name'] = 'Mika';
$params['last_name']  = 'Tuupola';
$params['age']        = 35;
$mika = Person::build($params);

$params = array();
$params['first_name'] = 'Jaak';
$params['last_name']  = 'Tamm';
$params['age']        = 27;
$jaak = Person::build($params);

$pc = new Computer();
$pc->mark('PC');

$mac = new Computer();
$mac->mark('Mac');

$atari = new Computer();
$atari->mark('Atari');

$mika->computer($mac);
$mika->save();
print_r($mika->computer());


$jaak->computer($atari);
$jaak->save();
print_r($jaak->computer());
?>
--GET--
--POST--
--EXPECT--
Computer Object
(
    [mark:protected] => Mac
    [person_id:protected] => 1
    [id:protected] => 1
    [data:protected] => Array
        (
        )

)
Computer Object
(
    [mark:protected] => Atari
    [person_id:protected] => 2
    [id:protected] => 2
    [data:protected] => Array
        (
        )

)
