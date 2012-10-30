<?php
/**
 * API test unit
 * 
 * Checks the various ways to play with the objects around PHP_UML (but
 * does not check the correctness of the parsing itself).
 * 
 * USAGE:
 * - chdir to the current directory
 * - run on the command line : phpunit UmlXmiTest
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
 * Checks various calls to the API
 *
 * @category PHP
 * @package  PHP_UML::tests
 * @author   Baptiste Autin <ohlesbeauxjours@yahoo.fr>
 */
class UmlXmiTest extends PHPUnit_Framework_TestCase
{
    const SUITE_DIR = './suite/';
    const TEMP_DIR  = './temp/';
    
    /**
     * Tests the various calls to the API made from scripts/phpuml
     * 
     */
    public function testPhpUmlScript()
    {
        $output     = self::TEMP_DIR.'new_phpumlscript.xmi';
        $version    = 1;
        $modelName  = 'Foo';
        $encoding   = 'iso-8859-1';
        $errorLevel = 1;
    
        $uml = new PHP_UML();
       
        $uml->setInput(self::SUITE_DIR . 'test1.php');
    
        $uml->deploymentView = true;
        $uml->componentView  = true;
        $uml->dollar         = true;
        $uml->docblocks      = true;
        $uml->onlyApi        = false;
        $uml->showInternal   = true;
        $uml->pureObject     = false;
       
        $uml->setMatchPatterns('*.php');
       
        $uml->setIgnorePatterns('.svn');
        
        PHP_UML_Warning::clear();
        
        $uml->parse('test');
        
        $e = PHP_UML_Output_Exporter::getInstance('xmi');
        $uml->setExporter($e);
        
        if ($e instanceof PHP_UML_Output_Xmi_Exporter) {
            $e->setEncoding($encoding);
            $e->setXmiVersion($version);
        }
        
        if ($e instanceof PHP_UML_Output_Xmi_Exporter) {
            $e->generateXmi();
            echo $e->getXmiDocument()->dump();
        }
        
        $e->export($output);

        foreach (PHP_UML_Warning::$stack as $msg) {
            echo $msg."\n";
        }
        
        $this->assertTrue(file_exists($output));
    }
    
    public function testAllFormatsCall()
    {
        $output     = self::TEMP_DIR.'new_variousapi.xmi';
        $version    = 2.1;
        $modelName  = 'Foo';
        $encoding   = 'iso-8859-1';
        $errorLevel = 1;
    
        $uml = new PHP_UML();
                     
        $uml->parseFile(self::SUITE_DIR.'test1.php', $modelName);
        
        chdir(dirname(__FILE__));
        echo $uml->export('xmi', $output);

        $this->assertTrue(file_exists($output));
                
        // now, we test the other output formats:
        
        $uml->export('html', self::TEMP_DIR);
        
        $uml->export('php', self::TEMP_DIR);

        $uml->export('HtmlNew', self::TEMP_DIR);
        
        $uml->export('Eclipse', self::TEMP_DIR);
    }
    
    public function testDirectXmiImportExport()
    {
        $importer = new PHP_UML_Input_XMI_FileScanner();
        $importer->setFiles(array(self::SUITE_DIR.'xmiParsingTest.xmi'));
        $importer->import();
        
        $exporter = new PHP_UML_Output_Html_Exporter();
        $exporter->setModel($importer->getModel());
        $exporter->export(self::TEMP_DIR);
    }
}
?>