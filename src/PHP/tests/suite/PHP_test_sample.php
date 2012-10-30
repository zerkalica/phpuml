<?php
/**
 * PHP_UML (PHP/MOF program elements classes)
 *
 * This is a TEST sample, designed to by parsed by PHP_UML itself.
 * Do not use it.
 * 
 * @category   PHP
 * @package    PHP_UML::tests
 * @author     Baptiste Autin <ohlesbeauxjours@yahoo.fr> 
 * @license    http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @link       http://pear.php.net/package/PHP_UML
 *
 */

Class PHP_UML_Metamodel_NamedElement {}

/**
 * Enumerates the basic PHP types.
 * 
 */
class PHP_UML_Metamodel_Enumeration
{
    static public $datatypes = array('mixed', 'array', 'string', 'int', 'integer',
        'bool', 'boolean', 'float', 'void', 'null', 'object', 'resource');
    
    static public $filetype = array('PHP File');
}

/**
 * A file object
 *
 */
class PHP_UML_Metamodel_File extends PHP_UML_Metamodel_NamedElement
{
}


/**
 * A structure designed to store instances of PHP_UML metamodel's elements.
 * It is composed of a stack, an internal iterator ($_key), a search function
 * and somes accessors.
 * It only accepts PHP_UML_Metamodel_Metamodel_NamedElement objects.
 * 
 */
class PHP_UML_Metamodel_Sequence
{
    private $_objects = array();
    private $_key = null;    // internal iterator

    /**
     * Adds a program element to the sequence
     *
     * @param PHP_UML_Metamodel_NamedElement &$element Program element
     */
    public function add(PHP_UML_Metamodel_NamedElement &$element)
    {
        $this->_objects[] = $element;
        if (is_null($this->_key))
            $this->_key = 0;
        else
            $this->_key++;
    }

    /**
     * Returns the object stored at the $index position
     *
     * @param int $index Index position
     * 
     * @return PHP_UML_Metamodel_NamedElement
     */
    public function get($index)
    {
        return $this->_objects[$index];
    }

    /**
     * Returns all the objects stored
     *
     * @return array()
     */
    public function getAll()
    {
        return $this->_objects;
    }
    
    /**
     * Searches for an object
     *
     * @param mixed  $value    The asserted value 
     * @param string $property The property to look into
     * 
     * @return mixed Either the index position, or FALSE
     */
    public function searchElement($value, $property = 'name')
    {
        foreach ($this->_objects as $key => &$o) {
            if ($o->{$property}==$value) {
                return $key;
            }
        }
        return false;
    }

    /**
     * Returns a reference to the current element (head)
     *
     * @return PHP_UML_Metamodel_NamedElement
     */
    public function &current()
    {
        return $this->_objects[$this->_key];
    }

    /**
     * Returns the current index position
     *
     * @return int
     */
    public function key()
    {
        return $this->_key;
    }

    /**
     * Returns an iterator containing all the objects stored
     *
     * @return PHP_UML_Metamodel_SequenceIterator
     */
    public function getIterator()
    {
        return new PHP_UML_Metamodel_SequenceIterator($this->_objects);
    }
}


/**
 * An external iterator for PHP_UML_Metamodel_Sequence
 * PHP_UML_Metamodel_Sequence->getIterator() can get you one.
 *
 */
class PHP_UML_Metamodel_SequenceIterator implements Iterator, Countable
{
    private $_key = 0;
    private $_objects = array();
    
    function __construct(Array &$set)
    {
        $this->_objects = $set;
    }

    function current()
    {
        return $this->_objects[$this->_key];
    }

    function key()
    {
        return $this->_key;
    }

    function next()
    {
        $this->_key++;
    }

    function valid()
    {
        return $this->_key<count($this->_objects);
    }

    function rewind()
    {
        $this->_key = 0;
    }
    
    function count()
    {
        return count($this->_objects);
    }
}

/**
 * A superstructure to gather program elements.
 * Normally filled by PHP_UML_PHP_Parser, but you can also fill such an object
 * "by yourself", and pass it to the XMI factory for "manual" XMI generation
 *
 */
class PHP_UML_Metamodel_Superstructure
{
    public $packages;
    public $interfaces;
    public $classes;
    public $functions;    
    public $parameters;
    public $datatypes;
    public $files;
    
    public function __construct()
    {
        $this->packages   = new PHP_UML_Metamodel_Sequence;
        $this->interfaces = new PHP_UML_Metamodel_Sequence;
        $this->classes    = new PHP_UML_Metamodel_Sequence;
        $this->functions  = new PHP_UML_Metamodel_Sequence;
        $this->parameters = new PHP_UML_Metamodel_Sequence;
        $this->datatypes  = new PHP_UML_Metamodel_Sequence;
        $this->files      = new PHP_UML_Metamodel_Sequence;

        foreach (PHP_UML_Metamodel_Enumeration::$datatypes as $d) {
            $type       = new PHP_UML_Metamodel_Type;
            $type->name = $d;
            $this->datatypes->add($type);
        }
    }
}
?>
