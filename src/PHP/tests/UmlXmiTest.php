<?php
/**
 * Main test unit
 * 
 * USAGE:
 * - chdir to the current directory
 * - run on the command line : phpunit UmlXmiTest
 * 
 * (you might need to redirect the output to a file, if some errors occur in long
 * XMI files...)
 * 
 * UmlXmiTest compares globally (all cases at once) the XMI that PHP_UML
 * (along with PHP_UML_Output_Xmi_Builder) has generated, against the "correct" original
 * XMI files (in XMI version 1, and in XMI version 2). 
 * If new features are added to the XMI builder, those two "correct" versions may
 * no longer be correct, and they then should be updated. This is the aim of
 * rebuildExpectedObjects(). Only a maintainer of the package should run that
 * method though.
 * 
 * Tip: the "current" xmi files are created on the disc (as "new_globalX.xmi")
 * so that you can compare them with the original ones (data-providers/globalX.xmi)
 * if they turned out to be different.
 * 
 * PHP version 5.3
 * 
 * @category PHP
 * @package  PHP_UML::tests
 * @author   Baptiste Autin <ohlesbeauxjours@yahoo.fr>
 * @license  http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version  SVN: $Rev: 179 $
 * @link     http://www.phpunit.de/
 * 
 */

error_reporting(E_ALL);

require_once 'PHPUnit/Framework.php';
require_once 'PHP/UML.php';

/**
 * Test unit class for the XMI generation
 *
 * @category PHP
 * @package  PHP_UML::tests
 * @author   Baptiste Autin <ohlesbeauxjours@yahoo.fr>
 */
class UmlXmiTest extends PHPUnit_Framework_TestCase
{
    const SUITE_DIR     = './suite/';
    const PROVIDERS_DIR = 'data-providers/';
    
    static public $IGNORED_DIR = array('.svn');
   
    /**
     * Provides the data for the "all at once" model check
     *
     * @return array
     */
    static public function providerXMIGlobal()
    {
        $data = array();
        
        $uml = self::getPhpUmlObject();
        $uml->parseDirectory(self::SUITE_DIR);
        
        $e = new PHP_UML_Output_Xmi_Exporter();
        $e->setModel($uml->getModel());
        
        $e->setDeploymentView(true);
        $e->setComponentView(false);
        $e->setStereotypes(true);
        
        $e->setXmiVersion(1);
        // We are saving what was generated (for later manual check, if needed)
        $e->export('temp/new_global1.xmi');

        // Then let's compare the content of global1.xmi, with the
        // XMI code we have just generated
        $data[] = array(
            file_get_contents(
                self::SUITE_DIR.self::PROVIDERS_DIR.'global1.xmi'
            ),
            $e->getXmiDocument()->dump(), 'XMI version 1'
        );

        // Same with XMI version 2:
        $e->setXmiVersion(2);
        //$e->generateXmi();
        $e->export('temp/new_global2.xmi');
        $data[] = array(
            file_get_contents(self::SUITE_DIR.self::PROVIDERS_DIR.'global2.xmi'),
            $e->getXmiDocument()->dump(), 'XMI version 2'
        );

        return $data;
    }
    
    /**
     * Checks the XMI files globally (all bug cases at once)
     * 
     * @param mixed  $expected Expected element
     * @param mixed  $actual   Current element
     * @param string $msg      Message
     * 
     * @dataProvider providerXMIGlobal
     */
    public function testXMIGlobal($expected, $actual, $msg)
    {
        $this->assertXmlStringEqualsXmlString($expected, $actual, 'Difference in '.$msg);
    }
    
    /**
     * Rebuilds the set of original objects (stored in data-providers).
     * You should not need to run it. If you do so, run it with a
     * trusted version of UML.
     */
    static public function rebuildExpectedParsePhp()
    {
        foreach (new DirectoryIterator(self::SUITE_DIR) as $file) {
            if (!$file->isDot() && !$file->isDir()) {
                $filename = $file->getFilename();
                if (substr($filename, -4) == '.php') {
                    $uml      = self::getPhpUmlObject();
                    $uml->parseFile(self::SUITE_DIR.$filename);
                    $str = serialize($uml->getModel());
                    $ptr = fopen(
                        self::SUITE_DIR.self::PROVIDERS_DIR.$filename.'.obj', 'wb'
                    );
                    fwrite($ptr, $str);
                    fclose($ptr);
                }
            }
        }
 
        // Global check (the two XMI files)
        $uml = self::getPhpUmlObject();
        $uml->parseDirectory(self::SUITE_DIR);
        
        $e = new PHP_UML_Output_Xmi_Exporter();
        
        $e->setDeploymentView(true);
        $e->setComponentView(false);
        $e->setStereotypes(true);
        
        $e->setXmiVersion(1);
        $e->export(self::SUITE_DIR.self::PROVIDERS_DIR.'global1.xmi');
        
        $e->setXmiVersion(2);
        $e->export(self::SUITE_DIR.self::PROVIDERS_DIR.'global2.xmi');
        
        // used by UmlParserTest::providerModelGlobal():
        $str = serialize($uml->getModel());
        $ptr = fopen(self::SUITE_DIR.self::PROVIDERS_DIR.'global.obj', 'wb');
        fwrite($ptr, $str);
        fclose($ptr);
    }
    
    /**
    * Rebuilds the set of original objects (stored in data-providers).
    * You should not need to run it. If you do so, run it with a
    * trusted version of UML.
    */
    static public function rebuildExpectedParseXmi()
    {
        foreach (new DirectoryIterator(self::SUITE_DIR) as $file) {
            if (!$file->isDot() && !$file->isDir()) {
                $filename = $file->getFilename();

                if (substr($filename, -4) == '.xmi') {
                    $uml = self::getPhpUmlObject();
                    $uml->setImporter(new PHP_UML_Input_XMI_FileScanner());
                    $uml->parseFile(self::SUITE_DIR.$filename);
                    $str = serialize($uml->getModel());
                    $ptr = fopen(
                        self::SUITE_DIR.self::PROVIDERS_DIR.$filename.'.obj', 'wb'
                    );
                    fwrite($ptr, $str);
                    fclose($ptr);
                }
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
        $uml = new PHP_UML();
        $uml->setIgnorePatterns(self::$IGNORED_DIR);
        $uml->docblocks      = true;
        $uml->dollar         = false;
        $uml->componentView  = false;
        $uml->deploymentView = true;
        $uml->pureObject     = false;
        return $uml;
    }
}
//UmlXmiTest::rebuildExpectedParseXmi();
//UmlXmiTest::rebuildExpectedParsePhp();
?>