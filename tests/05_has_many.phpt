--TEST--
Record::has_many
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

$bmw = new Car();
$bmw->mark('BMW');

$audi = new Car();
$audi->mark('Audi');

$fisker = new Car();
$fisker->mark('Fisker Karma');


$mika->cars(array($bmw, $audi, $fisker));
$mika->save();
print_r($mika->cars());


$jaak->cars();
$jaak->save();
print_r($jaak->cars());
?>
--GET--
--POST--
--EXPECT--
Array
(
    [0] => Car Object
        (
            [mark:protected] => BMW
            [person_id:protected] => 1
            [id:protected] => 1
            [data:protected] => Array
                (
                )

        )

    [1] => Car Object
        (
            [mark:protected] => Audi
            [person_id:protected] => 1
            [id:protected] => 1
            [data:protected] => Array
                (
                )

        )

    [2] => Car Object
        (
            [mark:protected] => Fisker Karma
            [person_id:protected] => 1
            [id:protected] => 1
            [data:protected] => Array
                (
                )

        )

)
