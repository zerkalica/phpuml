<?php
/**
 *
 * This is a TEST sample, designed to by parsed by PHP_UML itself.
 * 
 * Do not use it.
 * It is probably an old version, already out of date. 
 *
 * PHP version 5
 *
 * @category PHP
 * @package  PHP_UML::tests
 * @author   Baptiste Autin <ohlesbeauxjours@yahoo.fr> 
 * @license  http://www.gnu.org/licenses/lgpl.html LGPL License 3
 * @link     http://pear.php.net/package/PHP_UML
 */


/*
 * PHP File scanner
 */
abstract class PHP_UML_Scanner
{
    /**
     * Directories to ignore during scan
     * @var array
     */
    public $ignoredDirectories = array();
 
    /**
     * Recursive search
     * @var bool
     */
    public $subDirectories = true;
 
    /**
     * Traverse recursively the directories for files to parse
     *
     * @param string $dir   Path folder to look into
     * @param int    $level Level of recursion
     *
     * @return void
     */
    protected function traverseDirectory($dir, $level = 1)
    {
        if (is_dir($dir)) {
            $this->atFolderIn($level, $dir);
            if ($dh = opendir($dir)) { 
                while (($file = readdir($dh)) !== false) {
                    if (array_search($file, $this->ignoredDirectories)===false) {
                        if (filetype($dir.$file) == 'dir') { 
                            if ($this->subDirectories) {
                                $this->traverseDirectory(
                                    $dir.$file.DIRECTORY_SEPARATOR, $level+1
                                );
                            }
                        }
                        else {
                            $this->atFile($level, $dir.$file);
                        }
                    }
                }
                closedir($dh);
            }
            $this->atFolderOut($level, $dir);
        }
    }
}


/**
 * The main class to instantiate
 * 
 */
class PHP_UML extends PHP_UML_Scanner
{
    const FILE      = 1;
    const DIR_OPEN  = 2;
    const DIR_CLOSE = 3;

    /**
     * Extensions of files to scan
     * @var Array
     */
    public $acceptedExtensions = array('php');

    /**
     * Filenames are added to classes and interfaces
     * @var bool
     */
    public $tagFilename = true;

    /**
     * Each file generates an UML:Artifact (in the logicial view)
     * @var bool
     */
    public $pageAsArtifact = true;

    /**
     * A component view is created at root model, with the whole scanned file system
     * inside (as components)
     * @var bool
     */
    public $componentView = true;

    /**
     * Docblocks are read (package, param and return). This includes class,
     * function and file comments.
     * @var bool
     */
    public $docblocks = true;

    /**
     * Keep the PHP variable prefix $
     * @var bool
     */
    public $dollar = true;
    
    /**
     * A reference to a PHP_UML_Metamodel_Superstructure object
     * Either parseFile() or parserDirectory() set it, once their job is done.
     * Or you can set it yourself with a predefined instance of superstructure.
     * @var PHP_UML_Metamodel_Superstructure
     */
    public $model;

    /**
     * XML Encoding (see the constructor)
     * @var string
     */
    private $_xmlEncoding;

    /**
     * The concatened XMI string
     * @var string
     */
    private $_xmi = '';

    /**
     * A reference to a PHP_UML_PHP_Parser object
     * @var PHP_UML_PHP_Parser
     */
    private $_parser;
    
    /**
     * A reference to a PHP_UML_XMI_Factory object
     * @var PHP_UML_XMI_Factory
     */
    private $_factory;

    /**
     * Original directory path
     * @var string
     */
    private $_originalDir = '';

    /**
     * Stack of parsed files and folders. Used for building the filesystem tree.
     * @var array
     */
    private $_visited = array();

    /**
     * Start position of the scanned filepath
     * @var int
     */
    private $_basePathStart = 0;


    public function __construct()
    {
    }
       
    /**
     * Parse a PHP file, and builds the resulting XMI.
     *
     * @param mixed  $filename File(s) to parse. Can be a single file,
     *                         or an array of files.
     * @param string $model    Name of the model placed at root (enclosing pkg)
     *
     * It is the "PHP global" namespace.
     */
    public function parseFile($filename, $model = 'default')
    {
        $this->_parser = new PHP_UML_PHP_Parser($model, $this->docblocks, $this->dollar);
        
        $this->_visited = array();
        
        if (!is_array($filename)) {
            $filename = array($filename);
        }

        foreach ($filename as $filename_item) {
            if (file_exists($filename_item)) {
                $filename_item        = realpath($filename_item);
                $name                 = basename($filename_item);
                $this->_originalDir   = dirname($filename_item);
                $this->_basePathStart = 1+strlen($this->_originalDir);
                $path                 = $this->_originalDir.DIRECTORY_SEPARATOR;
                $this->atFile(1, $path.$name);
            }
            else
                throw new PHP_UML_Exception($filename_item.' : file not found.');
        }
        $this->_parser->finalize();
        $this->model = &$this->_parser->model;
    }
 
    /**
     * Parse a PHP file, and builds the resulting XMI.
     *
     * @param mixed  $path  Path(s) of the directories. Can be a single path,
     *                      or an array of pathes.
     * @param string $model Name of the model placed at root (enclosing pkg)
     */
    public function parseDirectory($path, $model = 'default')
    {
        $this->_parser = new PHP_UML_PHP_Parser($model, $this->docblocks, $this->dollar);
        
        $this->_visited = array();
        
        array_push($this->ignoredDirectories, '.');
        array_push($this->ignoredDirectories, '..'); 
        
        if (!is_array($path)) {
            $path = array($path);
        }

        foreach ($path as $path_item) {
            $this->_originalDir   = realpath($path_item);
            $this->_basePathStart = 1+strlen($this->_originalDir);
            if ($this->_originalDir != '') {
                $this->traverseDirectory($this->_originalDir.DIRECTORY_SEPARATOR);
            }
            else
                throw new PHP_UML_Exception($path_item.' : unknown path.');
        }
        $this->_parser->finalize();
        $this->model = &$this->_parser->model;
    }

    /**
     * XMI Generator
     * Generates XMI corresponding to the PHP model stored in $this->model.
     * 
     * If you need to use this XMI Generator without any previous PHP parsing,
     * simply set $this->model with a proper PHP_UML_Metamodel_Superstructure object
     *  
     * @param float  $version     XMI Version For XMI 1.x, any value below 2.
     *                                        For XMI 2.1, any value above or equal to 2.
     * @param string $xmlEncoding XML Encoding
     */
    public function generateXMI($version = 2.1, $xmlEncoding = 'iso-8859-1')
    {
        $this->_xmlEncoding = $xmlEncoding;
        $this->_xmi         = '<?xml version="1.0" encoding="'.$this->_xmlEncoding.'"?>';
        $this->_factory     = PHP_UML_XMI_Factory::factory($version);

        if(empty($this->model)) {
            throw new PHP_UML_Exception('No model given');
        }

        $_root       = &$this->model->packages->get(0);
        $this->_xmi .= $this->_factory->getModelOpen($_root);

        foreach ($this->model->datatypes->getIterator() as $type)
            $this->_xmi .= $this->_factory->getDatatype($type);

        $this->addPackage($_root, true);
        
        if ($this->componentView) {
            $this->_xmi .= $this->_factory->getComponentView($this->_visited);
        }
        $this->_xmi .= $this->_factory->getModelClose();
    }
    
    /**
     * Save the previously generated XMI to a file.
     *
     * @param string $output_file Filename
     */
    public function saveXMI($output_file)
    {
        if ($ptr = fopen($output_file, 'w+')) {
            fwrite($ptr, $this->XMI);
            fclose($ptr);
        }
        else
            throw new PHP_UML_Exception(
                'File '.$output_file.' could not be created.'
            );
    }
 
    /**
     * Save a UML Profile XMI-suited with PHP_UML.
     *
     * THIS IS EXPERIMENTAL.
     * Only XMI and UML >= 2.x
     *
     * @param string $output_file Filename
     */
    private function _saveXMIProfile($output_file)
    {
        if ($ptr = fopen($output_file, 'w+')) {
            fwrite($ptr, '<?xml version="1.0" encoding="'.$this->_xmlEncoding.'"?>'.
                $this->_factory->getProfile()
            );
            fclose($ptr);
        }
        else
            throw new PHP_UML_Exception(
                'File '.$output_file.' could not be created.'
            );
    }
 
    /**
     * Accessor to the XMI.
     *
     * @param string $name Must be "XMI" or "parsed"
     *
     * @return string The XMI code, or a PHP_UML_Parser_Result object
     */
    public function __get($name)
    {
        switch($name) {
        case 'XMI':
            if (strtolower($this->_xmlEncoding)=='utf-8')
                return utf8_encode($this->_xmi);
            else
                return $this->_xmi;
            break;
        case 'XMIProfile':
            return $this->_factory->getProfile();
            break;
        default:
            return null;
        }
    }
    
    /**
     * Function executed each time a new file is traversed
     *
     * @param int    $level    Level of recursion in the sub-directories
     * @param string $pathfile Current file path
     */
    protected function atFile($level, $pathfile)
    {
        $path_parts = pathinfo($pathfile);
        if (isset($path_parts['extension'])) {
            $extension = $path_parts['extension'];
        }
        else {
            $extension = '';
        }
        if (in_array($extension, $this->acceptedExtensions)) {
            $this->_parser->parse($pathfile);
        }
        $this->_visited[] = array(
            self::FILE => substr($pathfile, $this->_basePathStart)
        );
    }

    /**
     * Enters a new folder
     *
     * @param int    $level Level of recursion
     * @param string $dir   Name of folder
     */
    protected function atFolderIn($level, $dir)
    {
        $this->_visited[] = array(self::DIR_OPEN => $dir);
    }

    /**
     * Exits a folder
     *
     * @param int    $level Level of recursion
     * @param string $dir   Name of folder
     */
    protected function atFolderOut($level, $dir)
    {
        $this->_visited[] = array(self::DIR_CLOSE => $dir);
    }

    /**
     * Traverses all packages, and adds recursively the elements found
     * to the "xmi" string property.
     * 
     * @param PHP_UML_Metamodel_Package $package  New package to traverse
     * @param bool                      $stripTag Omit package XMI tag
     */
    protected function addPackage(PHP_UML_Metamodel_Package $package, $stripTag = false)
    {
        if (!$stripTag) {
            $this->_xmi .= $this->_factory->getPackageOpen($package);
        }

        foreach ($package->ownedType as &$elt) {
            if (get_class($elt)=='PHP_UML_Metamodel_Interface')
                $this->_xmi .= $this->_factory->getInterface($elt);
            else
                $this->_xmi .= $this->_factory->getClass($elt);
        }

        foreach ($package->nestedPackage as $idx)
            $this->addPackage($this->model->packages->get($idx));

        /*if($this->pageAsArtifact) {
            $files_list = self::getFilesInPackage($obj);
            $this->_xmi .= $this->_factory->getArtifacts(
                $files_list, $package
            );
        }*/
        if (!$stripTag) {
            $this->_xmi .= $this->_factory->getPackageClose();
        }
    }
    
    /**
     * Filename part of a given path
     *
     * @param string $x Filename
     *
     * @return string
     */
    private static function _getFilename($x)
    {
        $pathinfo = pathinfo($x);
        return $pathinfo['filename'];
    }
 
    /**
     * Basename part of a given path
     *
     * @param string $x Filename
     *
     * @return string
     */
    private static function _getBasename($x)
    {
        $pathparts = pathinfo(realpath($x));
        return $pathparts['basename'];
    }
}


/**
 * Subclass for PHP_UML_Exception
 *
 */
class PHP_UML_Exception extends PEAR_Exception
{
}

/**
 * Maintains of stack of warning messages. Worth to being checked, especially
 * if multiple classes in your PHP files have the same name...
 */
class PHP_UML_Warning
{
    /**
     * The $stack to read.
     * @var array
     */
    static public $stack;
    
    /**
     * Adds a message to the pile
     *
     * @param string $message The warning message to add
     */
    static public function add($message)
    {
        self::$stack[] = $message;
    }
    
    /**
     * Clears the pile
     */
    static public function clear()
    {
        self::$stack = array();
    }
}


/**
 * Abstract class to build UML elements through XMI code.
 * Only basic UML concepts are available.
 * To deal with the two different versions of XMI (1.4 and 2.1), you must use one of
 * the two specialized versions : PHP_UML_XMI_Factory1, or PHP_UML_XMI_Factory2
 *
 */
abstract class PHP_UML_XMI_Factory
{
    const EXPORTER_NAME = 'PEAR::PHP_UML';
    const PHP_FILE      = 'PHP File';

    static public $stereotypes = array('File', self::PHP_FILE);
    static public $extensions  = array(''=>'File', 'php'=>self::PHP_FILE);

    /**
     * Retrieves the ID of a stereotype, given a filename
     *
     * @param string $filename The file name
     *
     * @return string The PHP_UML ID of the matching extension 
     */
    static protected function guessStereotype($filename = '')
    {
        $path_parts = pathinfo($filename);
        $extension  = isset($path_parts['extension']) ? $path_parts['extension'] : '';
        if (isset(self::$extensions[$extension]))
            return self::generateID('stereotype', self::$extensions[$extension]);
        else
            return self::generateID('stereotype', self::$extensions['']);
    }

    static protected function generateID($type, $element)
    {
        return md5(self::EXPORTER_NAME.'#'.$type.'#'.$element);
    }

    protected static function getFilesInPackage(Array &$package)
    {
        $files_list = array();
        if (!empty($package)) {
            foreach ($package as $c => &$value) {
                if (!in_array($value['file'], $files_list))
                    $files_list[] = $value['file'];
            }
        }
        return $files_list;
    }

    /**
     * Insert a component view of the scanned file system.
     * Files are treated as components (all files are inserted)
     * Folders are treated as subsystems in UML1, and as nested components in UML2.
     *
     * @param array &$obj Visited files
     *
     * @return string XMI
     */
    public function getComponentView(Array &$obj)
    {
        $str = $this->getSubsystemOpen('Component View');
        foreach ($obj as $value) {
            $keys    = array_keys($value);
            $type    = $keys[0];
            $element = $value[$type];
            switch($type) {
            case PHP_UML::FILE :
                $str .= $this->getComponent(
                    basename($element), $element, self::guessStereotype($element)
                );
                break;
            case PHP_UML::DIR_OPEN :
                $str .= $this->getSubsystemOpen(basename($element), $element);
                break;
            case PHP_UML::DIR_CLOSE :
                $str .= $this->getSubsystemClose();
                break;
            }
        }
        $str .= $this->getSubsystemClose();
        return $str;
    }

    /**
     * Factory method
     *
     * @param int $version XMI version
     * 
     * @return PHP_UML_XMI_Factory
     */
    static function factory($version)
    {
        if ($version < 2)
            return new PHP_UML_XMI_Factory1();
        else
            return new PHP_UML_XMI_Factory2();
    }
}

/**
 * Implementation class to create XMI in version 1
 *
 */
class PHP_UML_XMI_Factory1 extends PHP_UML_XMI_Factory
{
    const XMI_VERSION = 1.2;
    const UML_VERSION = 1.4;

    const DEFAULT_CLASSIFIER_ATT = ' visibility="public" isAbstract="false" 
        isSpecification="false" isRoot="false" isLeaf="false" ';
    
    /**
     * Formates the XMI header
     *
     * @param string &$model Name of the model (root package)
     */
    public function getModelOpen(PHP_UML_Metamodel_Package &$model)
    {
        $str = '<XMI xmi.version="'.self::XMI_VERSION.'"
            xmlns:UML="http://www.omg.org/spec/UML/1.4">
            <XMI.header>
                <XMI.documentation>
                    <XMI.exporter>'.self::EXPORTER_NAME.'</XMI.exporter>
                </XMI.documentation>
                <XMI.metamodel XMI.name="UML" XMI.version="'.self::XMI_VERSION.'" />
            </XMI.header>
            <XMI.content>
            <UML:Model name="'.$model->name.'"
                xmi.id="'.parent::generateID('model', $model->name).'" '.
                self::DEFAULT_CLASSIFIER_ATT.'>
                <UML:Namespace.ownedElement>';

        foreach (self::$stereotypes as $item)
            $str .= '<UML:Stereotype xmi.id="'.parent::generateID('stereotype', $item).'"
                name="'.$item.'" '.self::DEFAULT_CLASSIFIER_ATT.' />';
        
        $str .= '<UML:Stereotype xmi.id="'.parent::generateId('stereotype', 'realize').'"
            name="realize" '.self::DEFAULT_CLASSIFIER_ATT.'>
            <UML:Stereotype.baseClass>Abstraction</UML:Stereotype.baseClass>
             </UML:Stereotype>';
        return $str;             
    }

    /**
     * Formates the opening tag for a package
     * 
     * @param PHP_UML_Package &$package Package
     */
    public function getPackageOpen(PHP_UML_Metamodel_Package &$package)
    {
        return '<UML:Package xmi.id="'.parent::generateID('package', $package->name).
            '" name="'.$package->name.'"><UML:Namespace.ownedElement>';
    }

    /**
     * Formates the closing tag of a package
     *
     */
    public function getPackageClose()
    {
        return '</UML:Namespace.ownedElement></UML:Package>';
    }
 
    /**
     * Formates the XMI declaration of the main PHP types (official and unofficial ones)
     *
     * @param PHP_UML_Type &$type Datatype
     */
    public function getDatatype(PHP_UML_Metamodel_Type &$type)
    {
        return '<UML:DataType xmi.id="'.self::generateID('datatype', $type->name).
            '" name="'.$type->name.'" visibility="public" isRoot="false" '.
            ' isLeaf="false" isAbstract="false"/>';
    }
        /*$str .= '<UML:TagDefinition xmi.id="'.parent::generateID('tag','src_path').'" 
            name="src_path" isSpecification="false" tagType="String">
            <UML:TagDefinition.multiplicity>
                <UML:Multiplicity xmi.id="'.parent::generateID('tag','src_path_multi').'">
                <UML:Multiplicity.range>
                <UML:MultiplicityRange xmi.id="'.
                    parent::generateID('tag','src_path_multi_range').
                '" lower="0" upper="1" />
                </UML:Multiplicity.range>
                </UML:Multiplicity>
            </UML:TagDefinition.multiplicity>
            </UML:TagDefinition>';*/


    /**
     * Formates the closing tag of an XMI:Model
     */
    public function getModelClose()
    {
        return '</UML:Namespace.ownedElement></UML:Model></XMI.content></XMI>';
    }


    public function getClass(PHP_UML_Metamodel_Class &$class)
    {
        $strRealization = '';
        $strGeneral     = '';

        $cn = $class->name;
        $nn = $class->package->name;
 
        $str = '<UML:Class name="'.$cn.'" xmi.id="'.
            parent::generateID('class', $nn.'#'.$cn).'" visibility="package"
            isAbstract="'.($class->isAbstract?'true':'false').'">';

        $str            .= self::_getGeneralizations($class, $strGeneral);
        $strRealization .= self::_getRealizations($class);
 
        $str .= '<UML:Classifier.feature>';

        foreach ($class->ownedAttribute as &$property) {
            $str .= self::getProperty($property);
        }

        foreach ($class->ownedOperation as &$operation)
            $str .= self::getOperation($operation);

        $str .= '</UML:Classifier.feature>';
        $str .= '</UML:Class>';

        return $str.$strGeneral.$strRealization;
    }

    public function getInterface(PHP_UML_Metamodel_Interface &$interface)
    {
        $in = $interface->name; 
        $nn = $interface->package->name;

        $strGeneral = '';

        $str = '<UML:Interface name="'.$in.'"'.
            ' xmi.id="'.parent::generateID('class', $nn.'#'.$in).'"'.
            ' visibility="package" isAbstract="true">';

        $str .= '<UML:Classifier.feature>';
        foreach ($interface->ownedOperation as &$operation)
            $str .= self::getOperation($operation, $nn, $in);

        $str .= '</UML:Classifier.feature>';
        $str .= self::_getGeneralizations($interface, $strGeneral);

        $str .= '</UML:Interface>';
        return $str.$strGeneral;
    }

    static private function _getGeneralizations(PHP_UML_Metamodel_Type &$client, &$general)
    {
        $str = '';
        $set = $client->superClass;
        $cn  = $client->name;
        $nn  = $client->package->name;

        foreach ($set as &$gclass) {
            if (!empty($gclass)) {
                $gcn = $gclass->name;
                $gnn = $gclass->package->name;
                $id  = parent::generateID(
                    'generalization', $nn.'#'.$cn.'-'.$gnn.'#'.$gcn
                );

                $str .= '<UML:GeneralizableElement.generalization>
                    <UML:Generalization xmi.idref="'.$id.'"/>
                    </UML:GeneralizableElement.generalization>';

                $general .= '<UML:Generalization xmi.id="'.$id.'">
                    <UML:Generalization.child><UML:Class xmi.idref="'.
                    parent::generateID('class', $nn.'#'.$cn).
                    '" /></UML:Generalization.child>
                    <UML:Generalization.parent><UML:Class xmi.idref="'.
                    parent::generateID('class', $gnn.'#'.$gcn).'"/>
                    </UML:Generalization.parent></UML:Generalization>';
            }
        }
        return $str;
    }

    static private function _getRealizations(PHP_UML_Metamodel_Class &$client)
    {
        $str = '';
        $set = $client->implements;
        $cn  = $client->name;
        $nn  = $client->package->name;

        foreach ($set as &$rclass) {
            if (!empty($rclass)) {
                $rcn  = $rclass->name;
                $rnn  = $rclass->package->name;
                $str .= '<UML:Abstraction '.
                    'xmi.id="'.parent::generateID(
                        'realize', $nn.'#'.$cn.'-'.$rnn.'#'.$rcn
                    ).'" isSpecification="false">'.
                    '<UML:ModelElement.stereotype><UML:Stereotype xmi.idref="'.
                    parent::generateID('stereotype', 'realize').'"/>'.
                    '</UML:ModelElement.stereotype>'.
                    '<UML:Dependency.client><UML:Class xmi.idref="'.
                    parent::generateID('class', $nn.'#'.$cn).
                    '"/></UML:Dependency.client>'.
                    '<UML:Dependency.supplier><UML:Interface xmi.idref="'.
                    parent::generateID('class', $rnn.'#'.$rcn).'"/>'.
                    '</UML:Dependency.supplier></UML:Abstraction>';
            }
        }
        return $str;
    }

    static public function getProperty(PHP_UML_Metamodel_Property &$property)
    {
        $pn = $property->name;
        $cn = $property->class->name;
        $nn = $property->class->package->name;
        $id = parent::generateID('property', $nn.'#'.$cn.'#'.$pn);
 
        $str = '<UML:Attribute name="'.$pn.'"'.
            ' xmi.id="'.$id.'"'.
            ' visibility="'.$property->visibility.'" ';
        if (!$property->isInstantiable)
            $str .= ' isStatic="true" ownerScope="classifier" ';
        else
            $str .= 'ownerScope="instance" ';
        if ($property->isReadOnly)
            $str .= 'changeability="frozen" isReadOnly="true" ';

        $str .= '>';
        $str .= self::_getTypeAndDefProp($property,
            parent::generateID('literal', $nn.'#'.$cn.'#'.$pn.'##dv')
        );

        $str .= '</UML:Attribute>';
        return $str;
    }
    
    /**
     * Special version of getTypeAndDefault for XMI 1.x
     * Splits a parameter into its type, name and default value
     *
     * @param PHP_UML_TypedElement &$parameter Parameter to split
     * @param int                  $id         Id of tag Expression
     */
    static private function _getTypeAndDefProp(PHP_UML_Metamodel_TypedElement &$parameter, $id)
    {
        $str = '';
        if (get_class($parameter->type)=='PHP_UML_Class') {
            $cn   = $parameter->type->name;
            $nn   = $parameter->type->package->name;
            $str .= '<UML:StructuralFeature.type>'.
                '<UML:DataType xmi.idref="'.self::generateID(
                    'class', $nn.'#'.$cn
                ).'"/>'.
                '</UML:StructuralFeature.type>';
        }
        elseif (get_class($parameter->type)=='PHP_UML_Type') {
            $cn   = $parameter->type->name;
            $str .= '<UML:StructuralFeature.type>'.
                '<UML:DataType xmi.idref="'.self::generateID(
                    'datatype', $cn
                ).'"/>'.
                '</UML:StructuralFeature.type>';
        }
        if ($parameter->default!='')
            $str .= '<UML:Attribute.initialValue>'.
                '<UML:Expression xmi.id="'.$id.'"'.
                ' body="'.htmlentities($parameter->default, ENT_QUOTES, "UTF-8").'" />'.
                '</UML:Attribute.initialValue>';
        return $str;
    }

    /*
    private function _getMethods(Array &$obj, $package, $c)
    {
        $str = '';
        foreach ($obj as $m => &$v) {
            $str .= '
                <UML:Operation name="'.$m.'" xmi.id="'.
                parent::generateID('method', $package.'#'.$c.'#'.$m).
                '" visibility="'.$v['visibility'].'" ';
            if ($v['static'])
                $str .= ' isStatic="true"';
            if ($v['abstract'])
                $str .= ' isAbstract="true"';
            $str .= ' isQuery="false" concurrency="sequential">'.
                '<UML:BehavioralFeature.parameter>';

            $str .= $this->prepareParameters($v, $package, $c, $m);
 
            $str .= '</UML:BehavioralFeature.parameter></UML:Operation>';
        }
        return $str;
    }*/
 
    static public function getOperation(PHP_UML_Metamodel_Operation &$operation)
    {
        $on = $operation->name;
        $cn = $operation->class->name;
        $nn = $operation->class->package->name;

        $str = '<UML:Operation name="'.$on.'" xmi.id="'.
            parent::generateID('method', $nn.'#'.$cn.'#'.$on).'" 
            visibility="'.$operation->visibility.'" ';
        if (!$operation->isInstantiable)
            $str .= ' isStatic="true"';
        if ($operation->isAbstract)
            $str .= ' isAbstract="true"';

        $str .= ' isQuery="false" concurrency="sequential">'.
            '<UML:BehavioralFeature.parameter>';

        $i = 0;
        foreach ($operation->ownedParameter as &$parameter) {
            $str .= self::getParameter($parameter, $i++);
        }

        $str .= '</UML:BehavioralFeature.parameter></UML:Operation>';

        return $str;
    }
        
    /*
    protected function getParameter($p, $c, $m, $name, $type, $kind, $default = '')
    {
        $temp = $p.'#'.$c.'#'.$m.'#'.$name;
        $id = parent::generateID('parameter', $temp);
        $str = '<UML:Parameter name="'.$name.'" xmi.id="'.$id.'" kind="'.$kind.'">'.
            '<UML:Parameter.type>'.
                '<UML:DataType xmi.idref="'.$type.'" />'.
            '</UML:Parameter.type>';
        if ($default != '') {
            $id = parent::generateID('expression', $temp.'#'.$default);
            $str .= '<UML:Parameter.defaultValue>'.
                '<UML:Expression xmi.id="'.$id.'" body="'.$default.'"/>'.
                '</UML:Parameter.defaultValue>';
        }     
        $str .= '</UML:Parameter>';
        return $str;
    }*/

    static public function getParameter(PHP_UML_Metamodel_Parameter &$parameter, $order = 0)
    {   
        $pn = $parameter->name;
        $on = $parameter->operation->name;
        $cn = $parameter->operation->class->name;
        $nn = $parameter->operation->class->package->name;
        $id = parent::generateID('parameter', $nn.'#'.$cn.'#'.$on.'#'.$order);
 
        $str  = '<UML:Parameter name="'.$pn.'" xmi.id="'.$id.'" '.
            'kind="'.$parameter->direction.'">';
        $str .= self::_getTypeAndDefault($parameter,
                parent::generateID('literal', $nn.'#'.$cn.'#'.$on.'#'.$pn.'##dv')
        );

        $str .= '</UML:Parameter>';

        return $str;
    }
    
    static private function _getTypeAndDefault(PHP_UML_Metamodel_TypedElement &$parameter, $id)
    {
        // Exception to MOF : a PHP class can have the name of a datatype
        $str = '';
        if (get_class($parameter->type)=='PHP_UML_Metamodel_Class') {
            $cn   = $parameter->type->name;
            $nn   = $parameter->type->package->name;
            $str .= '<UML:Parameter.type>'.
                '<UML:DataType xmi.idref="'.self::generateID(
                    'class', $nn.'#'.$cn
                ).'"/>'.
                '</UML:Parameter.type>';
        }
        elseif (get_class($parameter->type)=='PHP_UML_Metamodel_Type') {
            $cn   = $parameter->type->name;
            $str .= '<UML:Parameter.type>'.
                '<UML:DataType xmi.idref="'.self::generateID(
                    'datatype', $cn
                ).'"/>'.
                '</UML:Parameter.type>';
        }
        if ($parameter->default!='')
            $str .= '<UML:Parameter.defaultValue>'.
                '<UML:Expression xmi.id="'.$id.'"'.
                ' body="'.htmlentities($parameter->default, ENT_QUOTES, "UTF-8").'" />'.
                '</UML:Parameter.defaultValue>';
        return $str;
    }
    
    /*
    static private function _getTaggedValue($id, $value, $id_type)
    {
        return '<UML:ModelElement.taggedValue><UML:TaggedValue xmi.id="'.$id.'">'.
            '<UML:TaggedValue.dataValue>'.$value.'</UML:TaggedValue.dataValue>'.
            '<UML:TaggedValue.type><UML:TagDefinition xmi.idref="'.$id_type.'" />'.
            '</UML:TaggedValue.type>'.
            '</UML:TaggedValue></UML:ModelElement.taggedValue>';
    }
    */

    /**
     * Gets the XMI code of the artifacts in a given package
     *
     * @param array  $files_list List of files to map to artifacts
     * @param string $package    Package to retrieve (for ID generation)
     *
     * @return string XMI Code
     */
    public function getArtifacts(Array $files_list, $package)
    {
        $str = '';
        foreach ($files_list as $name)
            $str .= '<UML:Artifact xmi.id="'.
                parent::generateID('artifact', $package.'#'.$name).'" name="'.$name.'">
                <UML:ModelElement.stereotype>
                <UML:Stereotype xmi.idref="'.parent::generateID('stereotype', self::PHP_FILE).'"/>
                </UML:ModelElement.stereotype>
            </UML:Artifact>';
    
        return $str;
    }
    
    /**
     * Formates the XMI code for a subsystem
     *
     * @param string $name Name of the subsystem
     * @param string $id   Identifier (optional)
     *
     * @return string XMI Code
     */
    public function getSubsystemOpen($name, $id = null)
    {
        $str = '<UML:Subsystem name="'.$name.'" xmi.id="'.
            (is_null($id) ? parent::generateID('subsystem', $name) : $id).
            '" isInstantiable="false"><UML:Namespace.ownedElement>';
        return $str;
    }

    /*
     * Formates the closing tag of a subsystem
     */
    public function getSubsystemClose()
    {
        return '</UML:Namespace.ownedElement></UML:Subsystem>';
    }

    /**
     * Formates the XMI for a component
     *
     * @param string $name       Name of the component.
     * @param string $id         Identifier (optional)
     * @param string $stereotype Stereotype
     *
     * @return string XMI code
     */
    public function getComponent($name, $id = null, $stereotype = '')
    {
        return '<UML:Component xmi.id="'.
            (is_null($id) ? parent::generateID('component', $name) : $id).
            '" name="'.$name.'" '.
            self::DEFAULT_CLASSIFIER_ATT.' stereotype="'.$stereotype.'">'.
            '</UML:Component>';
    }
    
    public function getProfile()
    {
    }
}


/**
 * Implementation class to create XMI in version 2. See version 1 for explanations.
 *
 *
 */
class PHP_UML_XMI_Factory2 extends PHP_UML_XMI_Factory
{
    const XMI_VERSION = '2.1';
    const UML_VERSION = '2.1.2';

    const DEFAULT_CLASSIFIER_ATT = ' visibility="public" isAbstract="false" ';
    
    /**
     * PHP_UML UML Profile (TODO) 
     * @var string
     */
    public $profile = '';

    public function getModelOpen(PHP_UML_Metamodel_Package &$model)
    {
        return '<xmi:XMI xmi:version="'.self::XMI_VERSION.'" 
            xmlns:uml="http://schema.omg.org/spec/UML/'.self::UML_VERSION.'"
              xmlns:xmi="http://schema.omg.org/spec/XMI/'.self::XMI_VERSION.'">
                <xmi:Documentation exporter="'.self::EXPORTER_NAME.'"
                    exporterVersion="0.2" /> 
                <uml:Model xmi:type="uml:Model" name="'.$model->name.'"
                xmi:id="'.parent::generateID('model', $model->name).'" '.
                self::DEFAULT_CLASSIFIER_ATT.'>';
    }
    
    public function getPackageOpen(PHP_UML_Metamodel_Package &$package)
    {
        return '<packagedElement xmi:type="uml:Package" xmi:id="'.
            parent::generateID('package', $package->name).
            '" name="'.$package->name.'">';
    }
    
    public function getPackageClose()
    {
        return '</packagedElement>';
    }
    
    public function getDatatype(PHP_UML_Metamodel_Type &$type)
    {
        return '<packagedElement xmi:type="uml:DataType"'.
            ' xmi:id="'.self::generateID('datatype', $type->name).'"'.
            ' name="'.$type->name.'" />';
    }
    
    public function getModelClose()
    {
        return '</uml:Model></xmi:XMI>';
    }
    
    public function getClass(PHP_UML_Metamodel_Class &$class)
    {
        $strRealization = '';

        $cn  = $class->name;
        $nn  = $class->package->name;
        $str = '<packagedElement xmi:type="uml:Class" name="'.$cn.'" xmi:id="'.
            parent::generateID('class', $nn.'#'.$cn).'" visibility="package"
            isAbstract="'.($class->isAbstract?'true':'false').'">';

        $str .= self::_getGeneralizations($class);

        $strRealization .= self::_getRealizations($class);

        foreach ($class->ownedAttribute as &$property)
            $str .= self::getProperty($property);

        foreach ($class->ownedOperation as &$operation)
            $str .= self::getOperation($operation);

        /*
        if ($tagFilename)
            $str .= $this->getComment(
                parent::generateID('comment', $nn.'#'.$cn), 'src_path', $class->file->name
        );*/

        $str .= '</packagedElement>';

        return $str.$strRealization;
    }
 
    public function getInterface(PHP_UML_Metamodel_Interface &$interface)
    {
        $in  = $interface->name; 
        $nn  = $interface->package->name;
        $str = '<packagedElement xmi:type="uml:Interface" name="'.$in.'"'.
            ' xmi:id="'.parent::generateID('class', $nn.'#'.$in).'"'.
            ' visibility="package" isAbstract="true">';

        foreach ($interface->ownedOperation as &$operation)
            $str .= self::getOperation($operation, $nn, $in);

        $str .= self::_getGeneralizations($interface);

        /*
        if ($tagFilename)
            $str .= $this->getComment(
                parent::generateID('comment', $nn.'#'.$in),
                'src_path', $interface->file->name
        );*/

        $str .= '</packagedElement>';
        return $str;
    }

    static private function _getRealizations(PHP_UML_Metamodel_Class &$client)
    {
        $str = '';
        $set = $client->implements;
        $cn  = $client->name;
        $nn  = $client->package->name;
        
        foreach ($set as &$rclass) {
            if (!empty($rclass)) {
                $rcn  = $rclass->name;
                $rnn  = $rclass->package->name;
                $str .= '<packagedElement xmi:type="uml:Realization" '.
                'xmi:id="'.parent::generateID('realize', $nn.'#'.$cn.'-'.$rnn.'#'.$rcn).'" '.
                'client="'.parent::generateID('class', $nn.'#'.$cn).'" '.
                'supplier="'.parent::generateID('class', $rnn.'#'.$rcn).'" '.
                'realizingClassifier="'.parent::generateID('class', $rnn.'#'.$rcn).'"/>';
            }
        }
        return $str;
    }
    
    static private function _getGeneralizations(PHP_UML_Metamodel_Type &$client)
    {
        $str = '';
        $set = $client->superClass;
        $cn  = $client->name;
        $nn  = $client->package->name;

        foreach ($set as &$gclass) {
            if (!empty($gclass)) {
                $gcn  = $gclass->name;
                $gnn  = $gclass->package->name;
                $str .= '<generalization xmi:type="uml:Generalization" '.
                    'xmi:id="'.parent::generateID('generalization', $nn.'#'.$cn.'-'.$gnn.'#'.$gcn).'"'.
                    ' general="'.parent::generateID('class', $gnn.'#'.$gcn).'"/> ';
            }
        }
        return $str;
    }

    static public function getProperty(PHP_UML_Metamodel_Property &$property)
    {
        $pn = $property->name;
        $cn = $property->class->name;
        $nn = $property->class->package->name;
        $id = parent::generateID('property', $nn.'#'.$cn.'#'.$pn);
 
        $str = '<ownedAttribute xmi:type="uml:Property"'.
            ' name="'.$pn.'"'.
            ' xmi:id="'.$id.'"'.
            ' visibility="'.$property->visibility.'" ';
        if (!$property->isInstantiable)
            $str .= ' isStatic="true"';
        if ($property->isReadOnly)
            $str .= ' isReadOnly="true" ';

        $str .= '>';
        $str .= self::_getTypeAndDefault($property,
            parent::generateID('literal', $nn.'#'.$cn.'#'.$pn.'##dv')
        );

        $str .= '</ownedAttribute>';
        return $str;
    }

    static public function getOperation(PHP_UML_Metamodel_Operation &$operation)
    {
        $on = $operation->name;
        $cn = $operation->class->name;
        $nn = $operation->class->package->name;

        $str = '<ownedOperation name="'.$on.'" xmi:id="'.
            parent::generateID('method', $nn.'#'.$cn.'#'.$on).'" 
            visibility="'.$operation->visibility.'" ';
        if (!$operation->isInstantiable)
            $str .= ' isStatic="true"';
        if ($operation->isAbstract)
            $str .= ' isAbstract="true"';
        $str .= '>';

        $i = 0;
        foreach ($operation->ownedParameter as &$parameter)
            $str .= self::getParameter($parameter, $i++);

        $str .= '</ownedOperation>';

        return $str;
    }
        
    static public function getParameter(PHP_UML_Metamodel_Parameter &$parameter, $order = 0)
    {   
        $pn   = $parameter->name;
        $on   = $parameter->operation->name;
        $cn   = $parameter->operation->class->name;
        $nn   = $parameter->operation->class->package->name;
        $id   = parent::generateID('parameter', $nn.'#'.$cn.'#'.$on.'#'.$pn.$order);
        $str  = '<ownedParameter name="'.$pn.'" xmi:id="'.$id.'" '.
            'direction="'.$parameter->direction.'">';
        $str .= self::_getTypeAndDefault($parameter,
                parent::generateID('literal', $nn.'#'.$cn.'#'.$on.'#'.$pn.'##dv')
        );
        $str .= '</ownedParameter>';

        return $str;
    }
    
    static private function _getTypeAndDefault(PHP_UML_Metamodel_TypedElement &$parameter, $id)
    {
        // Exception to MOF : a PHP class can have the name of a datatype
        $str = '';
        if (get_class($parameter->type)=='PHP_UML_Metamodel_Class') {
            $cn   = $parameter->type->name;
            $nn   = $parameter->type->package->name;
            $str .= '<type xmi:idref="'.self::generateID('class', $nn.'#'.$cn).'"/>';
        }
        elseif (get_class($parameter->type)=='PHP_UML_Metamodel_Type') {
            $cn   = $parameter->type->name;
            $str .= '<type xmi:idref="'.self::generateID('datatype', $cn).'"/>';
        }
        if ($parameter->default!='')
            $str .= '<defaultValue xmi:type="uml:LiteralString" xmi:id="'.$id.'"'.
                ' value="'.htmlentities($parameter->default, ENT_QUOTES, "UTF-8").'" />';
            //htmlentities($parameter->default, ENT_QUOTES, "UTF-8")
        return $str;
    }

    public function getComment($id, $name, $body)
    {
        $body = '';
        return '<ownedComment xmi:type="uml:Comment"
            xmi:id="'.$id.'" name="'.$name.'" body="'.$body.'"/>';
    }
    
    public function getArtifacts(Array &$obj = array(), $package = '')
    {
        $files_list = parent::getFilesInPackage($obj);
        $str        = '';
        foreach ($files_list as $name)
            $str .= '<packagedElement xmi:type="uml:Artifact"'.
                ' xmi:id="'.parent::generateID('artifact', $package.'#'.$name).'"'.
                ' name="'.$name.'"'.
                ' stereotype="'.parent::generateID('stereotype', self::PHP_FILE).'">'.
                '</packagedElement>';
        return $str;
    }
     
    public function getSubsystemOpen($name, $id = null)
    {
        return '<packagedElement xmi:type="uml:Component" xmi:id="'.
            (is_null($id) ? parent::generateID('subsystem', $name) : $id).
            '" name="'.$name.'" '.self::DEFAULT_CLASSIFIER_ATT.'>';
    }
    
    public function getSubsystemClose()
    {
        return '</packagedElement>';
    }
    
    public function getComponent($name, $id = null)
    {
        return '<packagedElement xmi:type="uml:Component" xmi:id="'.
            (is_null($id) ? parent::generateID('component', $name) : $id).
            '" name="'.$name.'" '.self::DEFAULT_CLASSIFIER_ATT.' />';
    }
    
    /**
     * Formates a Profile adapted to PHP_UML.
     *
     * TODO. Experimental.
     *
     * @return string
     */
    public function getProfile()
    {
        $str = '
        <uml:Profile xmi:version="'.self::XMI_VERSION.'"
        nsURI="http://PHP_UML" nsPrefix="PHP_UML"
        xmlns:uml="http://schema.omg.org/spec/UML/'.self::UML_VERSION.'/uml.xml"
        xmlns:xmi="http://schema.omg.org/spec/XMI/'.self::XMI_VERSION.'"
        xmi:id="'.parent::generateID('profile', 'PHP_UML').'" name="PHP_UML"
        metamodelReference="PHP_UML_Metamodel">
        <packageImport xmi:id="PHP_UML_Metamodel">
        <importedPackage href="http://schema.omg.org/spec/UML/'.self::UML_VERSION.'/uml.xml"/>
        </packageImport>
        <ownedMember xmi:type="uml:Stereotype" xmi:id="'.
        parent::generateID('stereotype', self::PHP_FILE).'" name="'.self::PHP_FILE.'" '.
        self::DEFAULT_CLASSIFIER_ATT.' />
        </uml:Profile>';
        return $str;
    }

}

/**
 * A combination of string iteration and regular expressions.
 * It stores all the elements if finds in MOF program elements :
 * $packages, $interfaces, $classes, $functions, $parameters
 * 
 * Most navigabilities between associated elements are bidirectional
 * (the packages know their owned elements, and the classes know their
 * nesting package)
 * At first, relations use string references (the name of the element).
 * Once the parsing is completed, the method finalize() must be called,
 * so that the named references be replaced by PHP references (&$xxx).
 *
 *
 */
class PHP_UML_PHP_Parser
{
    /**
     * Regular expressions for a PHP variable
     */
    const PREG_VARIABLE = '[a-z_\\x7f-\\xff][a-z0-9_\\x7f-\\xff]*';
    const PREG_HEREDOC  = '<<<([^<\n\r]*)[\n\r]';
    const PREG_COMMENT  = '\/\/[^\n]*\n|\/\*.*\*\/|#[^\n]*\n';
    const PREG_PACKAGE  = '\*[ \t]+@package[ \t]+([^\s]+)\s';

    /**
     * Reference to a PHP_UML_Metamodel_Superstructure
     * (where the parser stores all the program elements it finds)
     *
     * @var PHP_UML_Metamodel_Superstructure
     */
    public $model;

    private $_text = '';
    private $_filename;
    private $_docblocks;
    private $_dollar;
    private $_cancel;

    private $_packageSeqIdx;    // current pkg index

    /**
     * Constructor
     *
     * @param string $root      Root package name
     * @param bool   $docblocks True = docblocks are scanned
     * @param bool   $dollar    True = $ in variables is kept
     */
    public function __construct($root, $docblocks = true, $dollar = true)
    {
        $this->_docblocks = $docblocks;
        $this->_dollar    = $dollar;

        $this->model         = new PHP_UML_Metamodel_Superstructure();
        $this->_packageSeqId = $this->_addPackage($root);
    }

    /**
     * Parses a PHP file
     * 
     * @param string $filename File to parse
     */
    public function parse($filename)
    {
        if (file_exists($filename)) {
            $this->_text     = file_get_contents($filename);
            $this->_filename = $filename;
        }
        else
            throw new PHP_UML_Exception('File '.$filename.' does not exist.');

        $f       = new PHP_UML_Metamodel_File;
        $f->name = $filename;
        $this->model->files->add($f);

        $set         = array();
        $lenText     = strlen($this->_text);
        $modePHP     = false;
        $modeQuotesD = false;    // double quote
        $modeQuotesS = false;    // single quote
        $modeHeredoc = false;
        $modeQuotes  = $modeQuotesD || $modeQuotesS || $modeHeredoc;
        $heredoc     = '';
        $attributes  = array(); // a collector for attributes (public, static..)
        $clasName    = '';
        $clasType    = '';
        $propName    = '';
        $funcName    = '';

        $lastCsLevel  = 0; // braces level at which current class is defined
        $lastFnLevel  = 0; // braces level at which current function is defined
        $lastFnPos    = 0; // character position of last visited function
        $lastPrPos    = 0; // character position of last visited prop. default. value
        $lastDocblock = '';

        $modeClass      = false;
        $modeFunction   = false;
        $modeInterface  = false;
        $modeProperty   = false;
        $modeExpression = false;

        $i        = 0;
        $level    = 0;    // curly braces level
        $levelPar = 0;    // parens level
        
        $this->_packageSeqIdx = 0;
        if ($this->_docblocks) {
            // First, let's have a look at the file docblock :
            $package = $this->_getFilePackage();
            if ($package!='')
                $this->_packageSeqIdx = $this->_addPackage($package, 0);
        }

        while($i<$lenText) {
            $one       = substr($this->_text, $i, 1);
            $two       = substr($this->_text, $i, 2);
            $remaining = substr($this->_text, $i);

            if ((!$modePHP)) {
                if ($two=='<?') {
                    $modePHP = true;
                    $i      += 2;
                }
                else {
                    $nxt = strpos($this->_text, '<?', $i);
                    if ($nxt===false)
                        $i = $lenText;
                    else
                        $i = $nxt;
                }
            }
            else {
                if ((!$modeQuotes) && $two=='?>') {
                    $modePHP = false;
                    $i      += 2;
                }
                elseif ((!$modeQuotes) && $two=='/*') {
                    $nxt = strpos($this->_text, '*/', $i+2);
                    if ($nxt===false)
                        $i = $lenText;
                    else
                        $i = ($nxt+2);
                }
                elseif ((!$modeQuotes) && ($two=='//' || $one=='#')) {
                    $nxt = preg_match(
                        '/(\n|\?>)/', $this->_text, $set, PREG_OFFSET_CAPTURE, $i
                    );
                    if ($nxt==0)
                        $i = $lenText;
                    else
                        $i = $set[1][1];
                }
                elseif ($modeQuotes && $two=='\\\\') {
                    $i += 2;
                }
                elseif ($modeQuotesD && $two=='\"') {
                    $i += 2;
                }
                elseif ($modeQuotesS && $two=='\\\'') {
                    $i += 2;
                }
                elseif ((!$modeQuotes)
                    && preg_match('/^'.self::PREG_HEREDOC.'/s', $remaining, $set)>0
                ) {
                    $heredoc     = trim($set[1]);
                    $modeHeredoc = true;
                    $modeQuotes  = $modeQuotesD || $modeQuotesS || $modeHeredoc;
                    $i          += strlen($set[0]);
                }
                elseif ($modeHeredoc
                    && (preg_match('/^'.$heredoc.'/s', $remaining, $set)>0)
                ) {
                    $heredoc     = '';
                    $modeHeredoc = false;
                    $modeQuotes  = $modeQuotesD || $modeQuotesS || $modeHeredoc;
                    $i          += strlen($set[0]);
                }
                elseif ((!($modeQuotesS || $modeHeredoc)) && $this->_text[$i]=='"') {
                    $modeQuotesD = (!$modeQuotesD);
                    $modeQuotes  = $modeQuotesD || $modeQuotesS || $modeHeredoc;
                    $i++;
                }
                elseif ((!($modeQuotesD || $modeHeredoc)) && $this->_text[$i]=="'") {
                    $modeQuotesS = (!$modeQuotesS);
                    $modeQuotes  = $modeQuotesD || $modeQuotesS || $modeHeredoc;
                    $i++;
                }
                elseif ((!$modeQuotes)) {
                    if ($one=='{') {
                        if ($modeClass && $clasName!='') {
                            $idxNs = $this->_getClassDocIdx($lastDocblock);
                            if ($modeInterface)
                                $this->_addInterface($clasName, $attributes, $idxNs);
                            else
                                $this->_addClass($clasName, $attributes, $idxNs);
                            // Classes are not always defined at 1st level :
                            $lastCsLevel  = $level+1;
                            $attributes   = array();
                            $clasName     = '';
                            $lastDocblock = '';
                        }
                        $i++;
                        $level++;
                        
                    }
                    elseif ($one=='}') {
                        if ($modeClass && $level == $lastCsLevel)
                            $modeClass = false;
                        $level--;
                        $i++;
                    }
                    elseif ($one=='(') {
                        $levelPar++;
                        $i++;
                    }
                    elseif ($one==')') {
                        if ($modeFunction && $levelPar==1 && !$this->_cancel) {
                            $this->_addOperation(
                                $funcName, $attributes, $modeInterface
                            );
                            $str = substr($this->_text, $lastFnPos, $i-$lastFnPos);
                            if ($str!='')
                                $this->_addParameters($str, $lastDocblock);
                            $attributes   = array();
                            $propName     = '';
                            $modeFunction = false;
                            $lastDocblock = '';
                        }
                        $levelPar--;
                        $i++;
                    }
                    elseif ($this->_findNamespace($remaining, $set)>0) {
                        $this->_packageSeqIdx = $this->_addPackage($set[1], 0);
                        $i                   += strlen($set[0]);
                    }
                    elseif ($this->_findAttr($remaining, $set)>0) {
                        $attributes[]  = strtolower($set[1]);
                        $lastDocblock .= $this->_revDocblock(
                            substr($this->_text, 0, $i)
                        );
                        $i += strlen($set[0]);
                    }
                    elseif ($this->_findClass($remaining, $set)>0) {
                        // Class / Interface :
                        $modeClass     = true;
                        $clasName      = trim($set[2]);
                        $modeInterface = (strtolower($set[1])=='interface');
                        $lastDocblock .= $this->_revDocblock(
                            substr($this->_text, 0, $i)
                        );
                        $i += strlen($set[0]);
                    }
                    elseif ($modeClass && $clasName!=''
                    && $this->_findClassRelation($remaining, $set)>0) {
                        // Superclass :
                        $attributes[$set[1]] = $set[2];
                        $i += strlen($set[0]);
                    }
                    elseif ($modeClass && $level==$lastCsLevel
                    && $this->_findFunction($remaining, $set)>0
                    && !$this->_cancel) {
                        $lastDocblock .= $this->_revDocblock(
                            substr($this->_text, 0, $i)
                        );
                        $funcName      = $set[1];
                        $modeFunction  = true;
                        $levelPar      = 1;
                        $i            += strlen($set[0]);
                        $lastFnPos     = $i;
                        $lastFnLevel   = $level;
                    }
                    elseif ($modeClass && (!$modeFunction) && $level==$lastCsLevel
                    && (!$modeExpression)
                    && $this->_findProperty($remaining, $set)>0) {
                        $i           += strlen($set[0]);
                        $lastPrPos    = $i;
                        $propName     = $set[1];
                        $modeProperty = true;
                        if (isset($set[2]) && $set[2]=='=')
                            $modeExpression = true;
                    }
                    elseif ($modeProperty && $one==';' && !$this->_cancel) {
                        $default        = $this->_stripComments(
                            trim(substr($this->_text, $lastPrPos, $i-$lastPrPos))
                        );
                        $this->_addProperty($propName, $attributes, $default);
                        $modeProperty   = false;
                        $modeExpression = false;
                        $attributes     = array();
                        $lastDocblock   = '';
                        $propName       = '';
                        $i++;
                    }
                    else
                        $i++;
                }
                else
                    $i++;
            }
        }
    }

    /**
     * Launches the resolution of the references for all stacks from root
     * 
     * Every reference (a temporary string) is replaced by a PHP reference
     * to the corresponding type (that is, a class or a datatype)
     * To be run once the filesystem scan is complete.
     * 
     */
    public function finalize()
    {
        $oDef = &$this->model->packages->get(0);
        $this->_resolveReferences($oDef, $oDef);
    }


    /**
     * Adds a package to the "packages" stack
     *
     * @param string $name    Name of the package
     * @param int    $baseIdx Current nesting package index
     *
     * @return int Index of the newly created package (or of the existing one)
     */
    private function _addPackage($name, $baseIdx = null)
    {
        $index = $this->model->packages->searchElement($name);
        if ($index===false) {
            $p                 = new PHP_UML_Metamodel_Package;
            $p->name           = $name;
            $p->nestingPackage = $baseIdx;
            $p->nestedPackage  = array();
            $this->model->packages->add($p);
            $index = $this->model->packages->key();
            if (!is_null($baseIdx)) {
                $parent = $this->model->packages->get($baseIdx);
                $parent->nestedPackage[] = $index;
            }               
        }
        return $index;
    }

    /**
     * Get the index of the corresponding @package class docblock
     *
     * @param string $classDocblock Preceding code (before the class declaration)
     *
     * @return int
     */
    private function _getClassDocIdx($classDocblock)
    {
        // Where's the class package ?
        if ($this->_docblocks) {
            // Is there a @package in the class docblock ?
            $r = $this->_findPackageInDocblock($classDocblock, $set);
            if ($r) {
                return $this->_addPackage($set[1], $this->_packageSeqIdx);
            }
        }
        return $this->_packageSeqIdx;
    }

    /**
     * Adds an interface to the "interfaces" stack
     *
     * @param string $name          Interface name
     * @param array  $attr          Some interface attributes (superclasses)
     * @param int    $classPkgIndex The index of the current nesting package
     *
     */
    private function _addInterface($name, $attr, $classPkgIndex)
    {
        $c = new PHP_UML_Metamodel_Interface;

        if (isset($attr['extends'])) {
            $c->superClass[] = trim($attr['extends']);
        }
        $c->name       = $name;
        $c->isAbstract = in_array('abstract', $attr);
        $c->file       = &$this->model->files->current();
        $nestingPkg    = $this->model->packages->get($classPkgIndex);
        $c->package    = &$nestingPkg;
        if ($this->_searchIntoPackage($c->package, $c->name)===false) {
            $nestingPkg->ownedType[] = &$c;
            $c->implements           = null;
            $this->model->interfaces->add($c);
        }
        else
            PHP_UML_Warning::add(
                'Interface '.$c->name.' already defined in '.$this->_filename
            );
    }

    /**
     * Adds a class to the "classes" stack ($this->model->classes)
     *
     * @param string $name          Class name
     * @param array  $attr          Some class attributes (superclasses)
     * @param int    $classPkgIndex The index of the current nesting package
     */
    private function _addClass($name, $attr, $classPkgIndex)
    {
        $c = new PHP_UML_Metamodel_Class;

        if (isset($attr['extends'])) {
            $c->superClass[] = trim($attr['extends']);
        }
        if (isset($attr['implements'])) {
            $imp = explode(',', $attr['implements']);
            foreach ($imp as $item) {
                $c->implements[] = trim($item);
            }
        }
        $c->name       = $name;
        $c->isAbstract = in_array('abstract', $attr);
        $c->file       = &$this->model->files->current();
        $nestingPkg    = $this->model->packages->get($classPkgIndex);
        $c->package    = &$nestingPkg;
        if ($this->_searchIntoPackage($c->package, $c->name)===false) {
            $nestingPkg->ownedType[] = &$c;
            $this->model->classes->add($c);
            $this->_cancel = false;
        }
        else {
            PHP_UML_Warning::add(
                'Class '.$c->name.' already defined in '.$this->_filename
            );
            $this->_cancel = true;
        }
    }

    private function _addOperation($name, $attr, $modeInterface)
    {
        $f = new PHP_UML_Metamodel_Operation;

        $f->name           = trim(str_replace('&', '', $name));
        $f->isInstantiable = !in_array('static', $attr);
        $f->isAbstract     = in_array('abstract', $attr);
        $f->visibility     = self::_getVisibility($attr);
        $this->model->functions->add($f);
        $obj = null;
        if ($modeInterface)
            $obj = &$this->model->interfaces->current();
        else
            $obj = &$this->model->classes->current();
        $obj->ownedOperation[] = &$f;
        $f->class              = &$obj;
    }

    private function _addProperty($name, $attr, $default)
    {
        $p = new PHP_UML_Metamodel_Property;

        $p->name           = $name;
        $p->isReadOnly     = in_array('const', $attr);
        $p->isInstantiable = !(in_array('static', $attr) || $p->isReadOnly);
        $p->visibility     = self::_getVisibility($attr);
        $p->default        = self::_stripComments($default);
        $p->type           = self::_guessType($p->default);

        $class                   = &$this->model->classes->current();
        $class->ownedAttribute[] = &$p;
        $p->class                = &$class;
    }

    private function _addParameters($set, $docblock)
    {
        $function = &$this->model->functions->current();

        $docblockParameter = array();
        if ($this->_docblocks) {
            $set_comment = $this->_findParamInDocblock($docblock);
            $return = false;
            foreach ($set_comment as $k) {
                if (substr($k[1], 0, 6)=='return' && !$return) {
                    $pr                         = new PHP_UML_Metamodel_Parameter;
                    $pr->name                   = 'return';
                    $pr->direction              = 'return';
                    $pr->type                   = $k[2];
                    $pr->operation              = &$function;
                    $function->ownedParameter[] = &$pr;
                    $return                     = true;
                }
                elseif ($k[1]=='param') {
                    $docblockParameter[self::_cleanVariable($k[3])] = $k[2];
                }
            }
        }
        $arr = explode(',', $set);
        foreach ($arr as &$parameters) {
            $parametre     = explode('=', $parameters);
            $parameterName = $this->_cleanParameter($parametre[0]);
        
            // Any default value given ?
            if (count($parametre)>1)
                $default = $this->_cleanParameter($parametre[1]);
            else
                $default = '';
            // Any @param in the method docblock ?
            $tmpParameterName = self::_cleanVariable($parameterName);
            if (isset($docblockParameter[$tmpParameterName]))
                $param = $docblockParameter[$tmpParameterName];
            else
                $param = '';
            // By ref or by value ? (inout/in)
            if (strpos($parameterName, '&')===false)
                $direction = 'in';
            else
                $direction = 'inout';

            list($name, $type) = self::_splitNameType($parameterName, $default, $param);
            
            $p            = new PHP_UML_Metamodel_Parameter;
            $p->name      = $name;
            $p->default   = $default;
            $p->type      = $type;
            $p->direction = $direction;
            $p->operation = &$function;
            $this->model->parameters->add($p);
            $function->ownedParameter[] = $p;
        }
    }

    private function _findClass(&$text, &$set)
    {
        return preg_match(
            '/^\s+(class|interface)\s+('.self::PREG_VARIABLE.')/si', $text, $set
        );
    }

    private function _findClassRelation(&$text, &$set)
    {
        $r = preg_match(
            '/^\s+(implements)\s+(('.self::PREG_VARIABLE.'[ \t,]*)+)?/si', $text, $set
        );
        if ($r==0) {
            $r = preg_match(
                '/^\s+(extends)\s+('.self::PREG_VARIABLE.')?/si', $text, $set
            );    
            if ($r>0)
                $set = array($set[0], 'extends', $set[2]);
        }
        else
            $set = array($set[0], 'implements', $set[2]);
        return $r;
    }
    
    private function _findNamespace(&$text, &$set)
    {
        return preg_match('/^namespace\s+('.
            self::PREG_VARIABLE.')[ \t]*;/si', $text, $set
        );
    }

    private function _findPackageInDocblock($text, &$set)
    {
        return preg_match('/'.self::PREG_PACKAGE.'/si', $text, $set);
    }
    
    /**
     * Looks into file docblock, till it finds the 1st @package
     *
     * @param string &$text Content of file
     * @param array  &$set  Preg result
     *
     * @return int
     */
    private function _findFileDocblock(&$text, &$set)
    {
        return preg_match('/^\s*<\?(?:php)?\s+'.
            '(\/\/[^\n\r]*\s+|'.
            '\/\*[^\n\r]*?\*\/\s+)?'.
            '\/\*(.*?)\*\//si', $text, $set);
    }

    private function _findProperty(&$text, &$set)
    {
        return preg_match('/^(\$?'.self::PREG_VARIABLE.')\s*(=)?/si', $text, $set);
    }

    private function _findAttr(&$text, &$set)
    {
        return preg_match(
            '/^[\s](var|public|protected|private|const|static|abstract|final)/si',
            $text, $set
        );
    }

    private function _findFunction(&$text, &$set)
    {
        return preg_match(
            '/^function\s([&\s]*'.self::PREG_VARIABLE.')\s*\(/si', $text, $set
        );
    }

    /**
     * Looks for a docblock backwards
     *
     * @param string $text The text to search in (from the end)
     *
     * @return string
     */
    private function _revDocblock($text)
    {
        $r = preg_match('/^\s*\/\*(.*?)\*\//s', strrev($text), $set);
        if ($r>0)
            return strrev($set[1]);
        else
            return '';
    }

    /**
     * Search for docblock tags
     *
     * @param string $text Text
     *
     * @return array
     */
    private function _findParamInDocblock($text)
    {
        $r = preg_match_all(
            '/\*[ \t]*@([\w]+)[ \t]+([\w]+)[ \t]*([\w\$]*)\s/', $text, $set,
            PREG_SET_ORDER
        );
        return $set;
    }

    private function _getFilePackage()
    {
        $r = $this->_findFileDocblock($this->_text, $set);
        if ($r>0) {
            $r = $this->_findPackageInDocblock($set[1], $doc);
            if ($r>0)
                return $doc[1];    
            else {
                $r = $this->_findPackageInDocblock($set[2], $doc);
                if ($r>0)
                    return $doc[1];
            }
        }
        return '';
    }
    
    private function _cleanParameter($str)
    {
        if (!$this->_dollar)
            $str = str_replace('$', '', $str);
        return htmlspecialchars(preg_replace('/\s\s+/', ' ', trim($str)));
    }


    /**
     * Splits a parameter into its name and type
     *
     * @param string $parameter The parameter to analyse
     * @param string $default   Default value
     * @param string $param     Value of docblock "param"
     *
     * @return array 
     */
    private static function _splitNameType($parameter, $default = '', $param = '')
    {
        $exp_param_name = explode(' ', trim($parameter));
        $nat            = array();
        if (count($exp_param_name)>1) {
            // Parameter like "MyType $myVariable"
            $nat[0] = trim($exp_param_name[1]);
            $nat[1] = trim($exp_param_name[0]);
        }
        else {
            // Parameter like "$myVariable"
            $nat[0] = $exp_param_name[0];
            if ($param!='') {
                // if a @param was provided, let's use it : 
                $nat[1] = $param;
            }
            else
                $nat[1] = self::_guessType($default);
        }
        return $nat;
    }

    static private function _cleanVariable($str)
    {
        return str_replace('$', '', str_replace('&amp;', '', $str));
    }

    static private function _stripComments($str)
    {
        $patt = array('/'.self::PREG_COMMENT.'/s');
        $repl = array('');
        while ($str!=preg_replace($patt, $repl, $str))
            $str = preg_replace($patt, $repl, $str);
        return $str;
    }
    
    static private function _getVisibility($arr)
    {
        if (in_array('private', $arr))
            return 'private';
        elseif (in_array('protected', $arr))
            return 'protected';
        else
            return 'public';
    }

    /**
     * Tries to guess the type of a given value
     *
     * @param string $value The type to check
     *
     * @return string The corresponding XMI DataType.
     */
    static private function _guessType($value)
    {
        $value = trim(strtolower($value));
        if (substr($value, 0, 6) == 'array(')
            $type = 'array';
        elseif (!(strpos($value, "'")===false && strpos($value, '"')===false))
            $type = 'string';
        elseif ($value=='true' || $value=='false')
            $type = 'bool';
        elseif ($value=='void')
            $type = 'void';
        elseif (is_numeric($value)) {
            if (strpos($value, '.')===false)
                $type = 'int';
            else
                $type = 'float';
        }
        else
            $type = 'mixed';
        return $type;
    }

    /**
     * Recursively replaces the temporary string values by references in a package
     *
     * @param PHP_UML_Metamodel_Package &$ns    Package to look into
     * @param PHP_UML_Metamodel_Package &$_oDef Root package
     */
    private function _resolveReferences(PHP_UML_Metamodel_Package &$ns, PHP_UML_Metamodel_Package &$_oDef)
    { 
        if (!is_null($ns->nestedPackage)) {
            foreach ($ns->nestedPackage as &$nsIdx) {
                $this->_resolveReferences($this->model->packages->get($nsIdx), $_oDef);
            }
        }
        if (!is_null($ns->ownedType))
        foreach ($ns->ownedType as &$elt) {
            if (isset($elt->superClass) && !is_null($elt->superClass)) { 
                foreach ($elt->superClass as &$className) {
                    $this->_resolveType($ns, $className, $_oDef, $elt);
                }
            }
            if (isset($elt->implements) && !is_null($elt->implements)) { 
                foreach ($elt->implements as &$className) {
                    $this->_resolveType($ns, $className, $_oDef, $elt);
                }
            }
            foreach ($elt->ownedOperation as &$function) {
                foreach ($function->ownedParameter as &$parameter) {
                    $this->_resolveType($ns, $parameter->type, $_oDef, $elt); 
                }
            }
            if (isset($elt->ownedAttribute)) {
                foreach ($elt->ownedAttribute as &$attribute) {
                     $this->_resolveType($ns, $attribute->type, $_oDef, $elt);
                }
            }
        } 
    }
    
    private function _searchIntoPackage(PHP_UML_Metamodel_Package &$ns, $value)
    {
        foreach ($ns->ownedType as $key => &$o) {
            if (strtolower($o->name)==strtolower($value)) {
                return $o;
            }
        }
        return false;
    }
    
    private function _searchIntoDatatype($value)
    { 
        foreach ($this->model->datatypes->getIterator() as $dt) {
            if (strtolower($dt->name)==strtolower($value)) {
                return $dt;
            }
        }
        return false;
    }
    
    /**
     * Makes the type resolution for a given element in a given package
     *
     * @param PHP_UML_Metamodel_Package &$pkg     The nesting package
     * @param string                    &$element The element to resolve, provided as a name
     * @param PHP_UML_Metamodel_Package &$_pkgDef The root package
     * @param PHP_UML_Metamodel_Type    &$context The context (the nesting class/interface, which 
     *                                            is the only element to know the nesting file)
     */
    private function _resolveType(PHP_UML_Metamodel_Package &$pkg, &$element, 
        PHP_UML_Metamodel_Package &$_pkgDef, PHP_UML_Metamodel_Type &$context)
    {
        $_o = $this->_searchIntoPackage($pkg, $element);
        if (!($_o===false)) {
            $element = $_o;
        }
        else {
            $_o = $this->_searchIntoPackage($_pkgDef, $element);
            if (!($_o===false)) {
                $element = $_o;
            }
            else {
                $_o = $this->_searchIntoDatatype($element);
                if (!($_o===false)) {
                    $element = $_o;
                }
                else {
                    PHP_UML_Warning::add('Could not resolve '.$element.
                        ' in '.$context->file->name);
                    $element = null;
                }
            }
        }
    }
}

?>
