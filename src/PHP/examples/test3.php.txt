<?php
/**
 * Third test file for PEAR PHP_UML
 * 
 * @package Andromedia
 *   -> note that this is a new package !
 *
 * ... and also note that this is the FILE docblock, so the package "Andromedia"
 * applies to all the code below (interfaces and classes)
 *
 */

interface iTest3
{
	public function add($a=0, $b=0);
}

interface iTest5
{
	public function substract($a, $b);
}

class TestClass3 implements iTest3, iTest5 {
  
   /**
    * @return int
    */
   function add($a=0, $b=0)
   {
      return ($a+$b);
   }
   
   function substract($a, $b)
   {
      return $a-$b;
   }
   
}

class TestClass4 extends TestClass3
{

}

/**
 * Cassiopeia should now override "Andromedia".
 * Note that PHP instruction "namespace" is implemented only in PHP>= 5.3
 * and its specification is still subject to change.
*/

namespace Cassiopeia;

/**
 * "Universe" is not present in the current namespace (Cassiopeia)
 * but it should be found in the global namespace.
 */
class Constellation extends Universe
{
	public $distance = 'far away';
}

/**
 * "Constellation" should be found in the current namespace (Cassiopeia)
 * but TestClass2 should be found nowhere (neither in Cassiopeia nor in the global namespace)
 * since TestClass2 belongs to Orion. A warning should be reported to PHP_UML_Warning.
 *
 */
class Galaxy extends Constellation implements TestClass2
{
    private $age = 2.500001;
}

/*
 * Galaxy should be found in the current namespace (Cassiopeia)
 */
class SolarSystem extends Galaxy
{
    public $elements = array('planets', 'comets');
}



/**
 * Let's back to the package Andromedia
 */
 
namespace Andromedia;

interface iTest4 extends iTest3
{
    public function divide($x=0, $y=1);
    public function multiply($x, $y);
}

class Test4 implements iTest4 {
	 
    function add($a=0, $b=0)
    {
        return ($a+$b);
    }
   
    public function divide($x=0, $y=1) {
        return $x/$y;
    }

    /**
     * The multiply operation
     *
     * @param float $x
     * @param float $y
     * @return float
     */
    public function multiply($x, $y) {
        return $x*$y;
    }
	
}
?>