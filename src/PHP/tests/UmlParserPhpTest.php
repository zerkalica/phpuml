<?php
/**
 * Main test unit
 * 
 * USAGE:
 * - chdir to the current directory
 * - run on the command line : phpunit UmlParserPhpTest
 * 
 * UmlParserPhpTest compares the objects that PHP_UML_Input_PHP_Parser has
 * generated against the serialized "correct" versions (stored in /providers).
 * It does it, first for each bug case (independantly), and then globally
 * (all cases at once), to discern errors implying the type resolution
 * (which works globally).
 * 
 * Also, note that if an error occurs in the one-by-one test, it is likely you'll
 * see it in the global test too.
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
 * Test unit class for the parser
 *
 * @category PHP
 * @package  PHP_UML::tests
 * @author   Baptiste Autin <ohlesbeauxjours@yahoo.fr>
 */
class UmlParserPhpTest extends PHPUnit_Framework_TestCase
{
    const SUITE_DIR     = './suite/';
    const PROVIDERS_DIR = 'data-providers/';
    
    static public $IGNORED_DIR = array('.svn');

    /**
     * Provides the data for the "all at once" model check
     *
     * @return array
     */
    static public function providerModelGlobal()
    {
        $uml = self::getPhpUmlObject();

        $data = array();
        $uml->parseDirectory(self::SUITE_DIR);
        $model = unserialize(
            file_get_contents(
                self::SUITE_DIR.self::PROVIDERS_DIR.'global.obj'
            )
        );
        $data[] = array($model, $uml->getModel(), 'Global parsing');

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
    
    /**
     * Lists the file that differs with previous version
     */
    static function listFiles()
    {
        $dir = self::SUITE_DIR;
        if (is_dir($dir)) {
            if ($dh = opendir($dir)) {
                while (($file = readdir($dh)) !== false) {
                    if (substr($file, -4)=='.php') {
                        $t = self::getPhpUmlObject();
                        $t->parseFile($dir.$file);
                        $str   = serialize($t->getModel());
                        $model = file_get_contents(self::SUITE_DIR.self::PROVIDERS_DIR.$file.'.obj');
                        if(md5($str) != md5($model))
                            echo $dir.$file.' : '.strlen($str).' '.strlen($model).'<br>';
                        file_put_contents('./temp/'.$file.'.obj', $str);
                    }        			
                }
                closedir($dh);
            }
        }
    }

}

UmlParserPhpTest::listFiles();
?>