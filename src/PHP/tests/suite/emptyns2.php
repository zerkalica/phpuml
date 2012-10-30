<?php
/**
 * Test-case (emptyns2.php) with an empty namespace
 * (it should not be ignored if appropriate switch was set in PHP_UML)
 * 
 */
 
namespace Saggitarius;

$x = 4;

function isolatedFunction2($x) {
	return 2*$x;
}


?>