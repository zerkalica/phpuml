<?php
/**
 * Test-case (emptyns1.php) with an empty namespace
 * (it should not be ignored if appropriate switch was set in PHP_UML)
 * 
 */
 
namespace Saggitarius;

$r = 4;

function isolatedFunction($x) {
	return 2*$x;
}

echo isolatedFunction(8);

$r = array('un', 'deux', 'trois');
foreach($r as $key => &$elt) {
	if($elt=='deux') {
		echo 'effacement';
		unset($r[$key]);
	}
}


foreach($r as $elrt) {
	echo '<br>'.$elrt;
}


?>