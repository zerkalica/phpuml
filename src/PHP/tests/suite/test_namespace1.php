<?php
/**
 * Comment for namespace A\B
 */
namespace A\B {

	class C implements E {
	}
	
	interface E {
	}
}

/**
 * Comment for namespace C\D
 */
namespace C\D {

	interface E {
	}
	
	class F implements E, \A\B\E {
	}
}

/**
 * Comment for namespace C\D\G
 */
namespace C\D\G {
	
	class H extends \C\D\F {
		static function cdf(\K\L $object) {
			echo 'hello !';
		}
	}
	
}

namespace I {
	use C\D\G as foo;

	$r = new \K\L();

	foo\H::cdf($r);

	class J extends foo\H implements \O {
	}

	J::cdf($r);
	
	use C\D as cd, A\B;
	
	class Boo extends \K\L implements B\E {
	}
	
}

namespace K {
	
	use C\D\G as B;
	use \I as M;
	use C\D\G\H as test;
	
	class Boo extends B\H {
	}

	class L {
		function boo(M\J &$j, array $k=array('a'=>'b')) {
		}
	}
	
	/**
   * Comment for class N
   */
	class N extends test implements P\Q {
	}
	
}

namespace K\P {
	
	interface Q {
	}
}

namespace {
	/**
   * Comment for interface O
   */
	interface O {
	}
}

?>