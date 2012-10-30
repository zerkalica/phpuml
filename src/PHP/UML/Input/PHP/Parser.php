<?php
/**
 * PHP_UML
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHP_UML
 * @author   Baptiste Autin <ohlesbeauxjours@yahoo.fr> 
 * @license  http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @version  SVN: $Revision: 180 $
 * @link     http://pear.php.net/package/PHP_UML
 * @since    $Date: 2012-05-12 19:33:59 +0200 (sam., 12 mai 2012) $
 */

/**
 * The PHP parser.
 * It stores all the program elements of a PHP file in
 * a PHP_UML_Metamodel_Superstructure object.
 * 
 * @category   PHP
 * @package    PHP_UML
 * @subpackage Input
 * @subpackage PHP
 * @author     Baptiste Autin <ohlesbeauxjours@yahoo.fr> 
 * @license    http://www.gnu.org/licenses/lgpl.html LGPL License 3
 */
abstract class PHP_UML_Input_PHP_Parser
{
    const T_NS_SEPARATOR  = '\\';
    const T_NS_SEPARATOR2 = '::';    // for backward compat
        
    /**
     * Constructor
     *
     * @param PHP_UML_Metamodel_Superstructure &$struct An empty instance of metamodel (superstructure)
     * @param PHP_UML_Input_PHP_ParserOptions  $options List of parsing options
     */
    abstract public function __construct(PHP_UML_Metamodel_Superstructure &$struct, PHP_UML_Input_PHP_ParserOptions $options=null);


    /**
     * Parse a PHP file
     * 
     * @param string $fileBase Full path, or base directory
     * @param string $filePath Pathfile (relative to $fileBase)
     */
    abstract public function parse($fileBase, $filePath = null);

}
?>
