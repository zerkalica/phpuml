<?php

class A_090827 {
  public $foo = 42;

  public function getFooString() {
    $s = "This is a string {$this->foo}.";
    return $s;
  }
}
 
class B_090827 {
  public function getFooString1() {
		$t1 = "}";
    return $t1;
  }
  
  public function getFooString2() {
		$t2 = "{";
    return $t2;
  }
  
  private $foo = '{';
  
  public function getFooString3() {
  	$t3 = 0;
  }
  
}
?>
