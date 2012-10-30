<?php
/**
* @package PHP_UML::tests
*/
/**
* Base Class
*
* @package	PHP_UML::tests
* @subpackage	_test1
*/
class bug_556894_base
{
/**
* I'm a test var
*/
var $test;

/**
* I'm a test method
*/
function test()
{
}
}

/**
* Subclass in same subpackage
*
* @package	PHP_UML::tests
* @subpackage	_test1
*/
class bug_556894_sub1 extends bug_556894_base
{
}

/**
* Subclass in different subpackage
*
* @package	PHP_UML::tests
* @subpackage	_test2
*/
class bug_556894_sub2 extends bug_556894_base
{
}
?>
