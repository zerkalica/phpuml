<?php
/**
 * Another test file for PEAR PHP_UML
 */
 
require "test1.php"; 

/**
 * A class that does not belong to any namespace :
 * (except the "global namespace" of PHP)
 */
abstract // oooh !!
class Universe
{
    public $age = 13000000000;
	abstract function expands()
	{
		
	}
}


/**
 * A test class to play with package PHP_UML
 * 
 * @package Orion
 * @author Baptiste Autin
 *
 */

class TestClass2 extends TestClass1 {

	static private $foo2;
   
   /**
    * @param string $name Name
    * @param int $age Age (that one is by reference !)
    * @return array
    */
   function dumbMethod(&$age, TestClass1 $object, $name = 'Some characters')
   {
       $age++;
       $name += $object->fooPublic;
       $a = array($name, $age);
       return array($name, $age);
   }
}