<?php

abstract class A extends Exception {
	/**
	 * Commentaire pour y
	 */
	abstract function y();
}

interface B {
	/**
	 * Commentaire pour x
	 */
	function x();
}

final class C extends A implements B {
	function x() {
	}
	function y() {
	}
	const D = 12;
	static $e;
}

namespace Test;

class Exception extends \Exception {}

?>