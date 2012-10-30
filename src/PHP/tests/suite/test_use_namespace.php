<?php
include "test_use_namespace_lib1.php";
include "test_use_namespace_lib2.php";
include "test_use_namespace_lib3.php";

$x1 = new Vegetable\SweetPepper\Peel();
$x2 = new Vegetable\Cucumber\Peel();
$y = new Fruit\SmallOrange();

echo $x1->test($y);
echo $x2->test($y);

?>