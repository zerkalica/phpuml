<?php
/**
 * Ce commentaire concerne l'espace de nom Truc
 */
namespace Truc;

/**
 * Ce commentaire concerne la classe bidule1
 */
class bidule1 {
	const T = 0;
	function fonctionDeBidule1($x) {
	}
}

define("Message", "Hello world.");

/**
 * Ce commentaire concerne la fonction A
 */
function A($a) {
	// Cette fonction isolée est sans commentaire !
	$a++;
	return $a;
}

function B($a) {
	// Cette fonction isolée est sans commentaire !
	$a++;
	return $a;
}

/**
 * Ce commentaire concerne une fonction isolée C à l'intérieur d'un package
 * @package Baratin
 * 
 */
function C() {
}

class bidule3 {
}

/**
 * Ce commentaire concerne la classe bidule2
 */
class bidule2 {
	const T = 0;
	function fonctionDeBidule2($x) {
	}
}

/**
 * Comment for the constant BB
 */ 
const BB = 4;

/**
 * Comment for Contract
 */
interface Contract {
	function trop();
}

const DD = 0;
const /*with comment*/ EE					=				'far';

const F = 'really';

/**
 * Commentaire pour la constante G
 * @package Baratin
 */
const G = 4.7;

echo A(BB);

?>