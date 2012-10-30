<?php
/**
 * PHP_UML (MOF Program elements classes)
 *
 * This is a TEST sample, designed to by parsed by PHP_UML itself.
 * Do not use it.
 * 
 * @category   PHP
 * @package    PHP_UML::tests::Metamodel
 * @author     Baptiste Autin <ohlesbeauxjours@yahoo.fr> 
 * @license    http://www.gnu.org/licenses/lgpl.html LGPL License 3s
 * @link       http://pear.php.net/package/PHP_UML
 *
 */

class PHP_UML_Metamodel_NamedElement
{
    public $name;
}

class PHP_UML_Metamodel_Type extends PHP_UML_Metamodel_NamedElement
{
}

class PHP_UML_Metamodel_TypedElement extends PHP_UML_Metamodel_NamedElement
{
    public $type;
}

class PHP_UML_Metamodel_Interface extends PHP_UML_Metamodel_Type
{
    public $superClass = array();
    public $ownedOperation = array();
    public $file;
    public $package;
}

class PHP_UML_Metamodel_Class extends PHP_UML_Metamodel_Interface
{
    public $ownedAttribute = array();
    public $isAbstract;
    public $isInstantiable;
    public $implements = array();
}

class PHP_UML_Metamodel_Operation extends PHP_UML_Metamodel_NamedElement
{
    public $isAbstract;
    public $isInstantiable;
    public $ownedParameter = array();
    public $class;
    public $visibility;
}

class PHP_UML_Metamodel_Property extends PHP_UML_Metamodel_TypedElement
{
    public $isReadOnly;
    public $isInstantiable;
    public $visibility;
    public $default;
    public $class;
}

class PHP_UML_Metamodel_Parameter extends PHP_UML_Metamodel_TypedElement
{
    public $default;
    public $operation;
    public $direction;
}

class PHP_UML_Metamodel_Package extends PHP_UML_Metamodel_NamedElement
{
    public $nestingPackage;
    public $nestedPackage = array();
    public $ownedType = array();
}

?>
