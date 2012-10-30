<?php
/**
 * Main test unit
 * 
 * USAGE:
 * - chdir to the current directory
 * - run on the command line : phpunit UmlParserXmiTest
 * 
 * UmlParserXmiTest compares the object that PHP_UML_Input_XMI_Parser has
 * generated against the serialized expected version (stored in /providers).
 * 
 * PHP version 5.3
 * 
 * @category PHP
 * @package  PHP_UML::tests
 * @author   Baptiste Autin <ohlesbeauxjours@yahoo.fr>
 * @license  http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version  SVN: $Revision: 179 $
 * @link     http://www.phpunit.de/
 * 
 */

error_reporting(E_ALL);

require_once 'PHPUnit/Framework.php';
require_once 'PHP/UML.php';

/**
 * Test unit class for the XMI parser
 *
 * @category PHP
 * @package  PHP_UML::tests
 * @author   Baptiste Autin <ohlesbeauxjours@yahoo.fr>
 */
class UmlParserXmiTest extends PHPUnit_Framework_TestCase
{
    const SUITE_DIR     = './suite/';
    const PROVIDERS_DIR = 'data-providers/';
    
    static public $IGNORED_DIR = array('.svn');

    /**
     *
     * @return array
     */
    static public function providerModelGlobal()
    {
        $data = array();
        
        $uml = self::getPhpUmlObject();
        $uml->setImporter(new PHP_UML_Input_XMI_FileScanner());
        $uml->parseFile(self::SUITE_DIR.'xmiParsingTest.xmi');
        
        $expected = unserialize(
            file_get_contents(
                self::SUITE_DIR.self::PROVIDERS_DIR.'xmiParsingTest.xmi.obj'
            )
        );
        $data[] = array($expected, $uml->getModel(), 'XMI parser');

        return $data;
    }
 
    /**
     * Checks the model globally (all bug cases at once)
     * 
     * @param mixed  $expected Expected element
     * @param mixed  $actual   Current element
     * @param string $filename Filename
     * 
     * @dataProvider providerModelGlobal
     */
    public function testModelGlobal($expected, $actual, $filename)
    {
        $this->_assertModel($expected->packages, $actual->packages, 'package', $filename);
    }

    private function _assertModel(PHP_UML_Metamodel_Package $actual, PHP_UML_Metamodel_Package $expected, $type, $filename)
    {
        $this->_assertEMOFEqual($actual, $expected, $type, $filename);
    }
    
    private function _assertEMOFEqual(PHP_UML_Metamodel_NamedElement $actual, PHP_UML_Metamodel_NamedElement $expected, $type, $filename)
    {
        $this->assertEquals(
            get_class($actual),
            get_class($expected),
            get_class($actual).' and '.get_class($expected).': different classes'
        );
        $a = (array) $actual;
        $b = (array) $expected;
        $this->assertEquals(count($a), count($b), $type.' : different numbers');
        foreach ($a as $key => $value) {
            if (is_array($value)) {
                $bb = $b[$key];
                foreach ($value as $k => $v) {
                    $this->assertEquals($v, $bb[$k], 'In: '.$filename.'/'.$type.'/'.$key.'/'.$k, 0, 2);
                }
            } else {
                $this->assertEquals($b[$key], $value, 'In: '.$filename.'/'.$type.'/'.$key, 0, 1);
            }
        }
    }
    
    /**
     * Return a PHP_UML object to test
     *
     * @return PHP_UML
     */
    static function getPhpUmlObject()
    {
        PHP_UML_SimpleUID::$deterministic = true;
        PHP_UML_SimpleUID::reset();
        $t = new PHP_UML();
        $t->setIgnorePatterns(self::$IGNORED_DIR);
        $t->docblocks      = true;
        $t->dollar         = false;
        $t->componentView  = false;
        $t->deploymentView = true;
        $t->pureObject     = false;
        return $t;
    }
}
?>