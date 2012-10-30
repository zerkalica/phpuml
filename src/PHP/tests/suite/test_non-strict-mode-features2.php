<?php

/**
 * Cette variable est globale
 */
define("GlobalMessage", "Hey");

/**
 * Cette variable est aussi globale
 */
define("GlobalNumber", -10.54);

define("Complement"+"", GlobalMessage);

define(GlobalMessage, "Hey hey");

echo Hey;

/**
 * Ce commentaire concerne la fonction GlobalA
 */
function GlobalA($x, $y=0) {
	// Cette fonction isolée est sans commentaire !
	$a++;
	return $a;
}

function GlobalB($a) {
	// Cette fonction isolée est sans commentaire !
	$a++;
	return $a;
}

const GlobalX = 40;

/**
 * That one (GlobalY)  has a comment
 */
const GlobalY = 0;

?>