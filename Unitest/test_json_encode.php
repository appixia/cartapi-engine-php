<?php

require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'Engine.php');

$encoder = CartAPI_Engine::getEncoder('JSON');

// regular work (explicit arrays)
print "\n*********************************\nTEST\n*********************************\n\n";

$root = $encoder->createRoot();
$encoder->addString($root, 'Name', 'John Smith');
$encoder->addNumber($root, 'Age', 30);
$encoder->addBoolean($root, 'IsOld', true);

$wife = &$encoder->addContainer($root, 'Wife');
$encoder->addString($wife, 'Name', 'Betty Smith');
$encoder->addNumber($wife, 'Age', 28);
$encoder->addBoolean($wife, 'IsOld', false);

$wife_pet = &$encoder->addContainer($wife, 'Pet');
$encoder->addString($wife_pet, 'Name', 'Rex');
$encoder->addNumber($wife_pet, 'Age', 3);
$encoder->addBoolean($wife_pet, 'IsOld', false);

$colors = &$encoder->addArray($root, 'Color');
$encoder->addStringToArray($colors, 'Red');
$encoder->addStringToArray($colors, 'Green');
$encoder->addStringToArray($colors, 'Blue');

$friends = &$encoder->addArray($root, 'Friend');

$friend = &$encoder->addContainerToArray($friends);
$encoder->addString($friend, 'Name', 'Billy James');
$encoder->addNumber($friend, 'Age', 31);
$encoder->addBoolean($friend, 'IsOld', true);

$friend = &$encoder->addContainerToArray($friends);
$encoder->addString($friend, 'Name', 'Jeff Frost');
$encoder->addNumber($friend, 'Age', 29.5);
$encoder->addBoolean($friend, 'IsOld', false);

var_dump($root);

$encoder->render($root);

// regular work (implicit arrays)
print "\n*********************************\nTEST\n*********************************\n\n";

$root = $encoder->createRoot();
$encoder->addString($root, 'Name', 'John Smith');
$encoder->addNumber($root, 'Age', 30);
$encoder->addBoolean($root, 'IsOld', true);

$wife = &$encoder->addContainer($root, 'Wife');
$encoder->addString($wife, 'Name', 'Betty Smith');
$encoder->addNumber($wife, 'Age', 28);
$encoder->addBoolean($wife, 'IsOld', false);

$wife_pet = &$encoder->addContainer($wife, 'Pet');
$encoder->addString($wife_pet, 'Name', 'Rex');
$encoder->addNumber($wife_pet, 'Age', 3);
$encoder->addBoolean($wife_pet, 'IsOld', false);

$encoder->addString($root, 'Color', 'Red');
$encoder->addString($root, 'Color', 'Green');
$encoder->addString($root, 'Color', 'Blue');

$friend = &$encoder->addContainer($root, 'Friend');
$encoder->addString($friend, 'Name', 'Billy James');
$encoder->addNumber($friend, 'Age', 31);
$encoder->addBoolean($friend, 'IsOld', true);

$friend = &$encoder->addContainer($root, 'Friend');
$encoder->addString($friend, 'Name', 'Jeff Frost');
$encoder->addNumber($friend, 'Age', 29.5);
$encoder->addBoolean($friend, 'IsOld', false);

var_dump($root);

$encoder->render($root);

// working directly with php natives
print "\n*********************************\nTEST\n*********************************\n\n";

$data = array(
	'Name' => 'John Smith',
	'Age' => 30,
	'IsOld' => true,
	'Color' => array('Red','Green','Blue'),
	'Pet' => array(
		'Name' => 'Rex',
		'Age' => 3,
		'IsOld' => false,
	),
);

$encoder->render($data);

?>