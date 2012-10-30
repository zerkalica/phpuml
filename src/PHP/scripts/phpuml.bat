@echo off
REM PHP Parser and UML/XMI generator. Reverse-engineering tool.
REM 
REM A package to scan PHP files and directories, and get an UML/XMI representation
REM of the parsed code.
REM 
REM PHP version 5
REM 
REM @category PHP
REM @package  PHP_UML
REM @author   Baptiste AUTIN <ohlesbeauxjours@yahoo.fr>
REM @author   David JEAN LOUIS <izi@php.net>
REM @license  http://www.gnu.org/licenses/lgpl.html LGPL License 3
REM @version  SVN: $Revision: 99 $
REM @link     http://pear.php.net/package/PHP_UML
REM @link     http://www.baptisteautin.com/projects/PHP_UML/
REM @since    $Date: 2009-01-08 23:49:07 +0100 (jeu., 08 janv. 2009) $

"@php_bin@" -d auto_append_file="" -d auto_prepend_file="" -d include_path="@php_dir@" "@bin_dir@\phpuml" %*
