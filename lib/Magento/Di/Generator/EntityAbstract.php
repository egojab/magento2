<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Di
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

abstract class Magento_Di_Generator_EntityAbstract
{
    /**
     * Entity type
     */
    const ENTITY_TYPE = 'abstract';

    /**
     * @var array
     */
    private $_errors = array();

    /**
     * Source model class name
     *
     * @var string
     */
    private $_sourceClassName;

    /**
     * Result model class name
     *
     * @var string
     */
    private $_resultClassName;

    /**
     * @var Magento_Di_Generator_Io
     */
    private $_ioObject;

    /**
     * Autoloader instance
     *
     * @var Magento_Autoload_IncludePath
     */
    private $_autoloader;

    /**
     * Class generator object
     *
     * @var Magento_Di_Generator_CodeGenerator_Interface
     */
    protected $_classGenerator;

    /**
     * @param string $sourceClassName
     * @param string $resultClassName
     * @param Magento_Di_Generator_Io $ioObject
     * @param Magento_Di_Generator_CodeGenerator_Interface $classGenerator
     * @param Magento_Autoload_IncludePath $autoLoader
     */
    public function __construct(
        $sourceClassName = null,
        $resultClassName = null,
        Magento_Di_Generator_Io $ioObject = null,
        Magento_Di_Generator_CodeGenerator_Interface $classGenerator = null,
        Magento_Autoload_IncludePath $autoLoader = null
    ) {
        if ($autoLoader) {
            $this->_autoloader = $autoLoader;
        } else {
            $this->_autoloader = new Magento_Autoload_IncludePath();
        }
        if ($ioObject) {
            $this->_ioObject = $ioObject;
        } else {
            $this->_ioObject = new Magento_Di_Generator_Io(new Varien_Io_File(), $this->_autoloader);
        }
        if ($classGenerator) {
            $this->_classGenerator = $classGenerator;
        } else {
            $this->_classGenerator = new Magento_Di_Generator_CodeGenerator_Zend();
        }

        $this->_sourceClassName = ltrim($sourceClassName, Magento_Autoload_IncludePath::NS_SEPARATOR);
        if ($resultClassName) {
            $this->_resultClassName = $resultClassName;
        } elseif ($sourceClassName) {
            $this->_resultClassName = $this->_getDefaultResultClassName($sourceClassName);
        }
    }

    /**
     * Generation template method
     *
     * @return bool
     */
    public function generate()
    {
        try {
            if ($this->_validateData()) {
                $sourceCode = $this->_generateCode();
                if ($sourceCode) {
                    $fileName = $this->_ioObject->getResultFileName($this->_getResultClassName());
                    $this->_ioObject->writeResultFile($fileName, $sourceCode);
                    return true;
                } else {
                    $this->_addError('Can\'t generate source code.');
                }
            }
        } catch (Exception $e) {
            $this->_addError($e->getMessage());
        }
        return false;
    }

    /**
     * List of occurred generation errors
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->_errors;
    }

    /**
     * @return string
     */
    protected function _getSourceClassName()
    {
        return $this->_sourceClassName;
    }

    /**
     * @param string $className
     * @return string
     */
    protected function _getFullyQualifiedClassName($className)
    {
        return Magento_Autoload_IncludePath::NS_SEPARATOR
            . ltrim($className, Magento_Autoload_IncludePath::NS_SEPARATOR);
    }

    /**
     * @return string
     */
    protected function _getResultClassName()
    {
        return $this->_resultClassName;
    }

    /**
     * @param string $modelClassName
     * @return string
     */
    protected function _getDefaultResultClassName($modelClassName)
    {
        return $modelClassName . ucfirst(static::ENTITY_TYPE);
    }

    /**
     * Returns list of properties for class generator
     *
     * @return array
     */
    protected function _getClassProperties()
    {
        // const CLASS_NAME = '<source_class_name>';
        $className = array(
            'name'         => 'CLASS_NAME',
            'const'        => true,
            'defaultValue' => $this->_getSourceClassName(),
            'docblock'     => array('shortDescription' => 'Entity class name'),
        );

        // protected $_objectManager = null;
        $objectManager = array(
            'name'       => '_objectManager',
            'visibility' => 'protected',
            'docblock'   => array(
                'shortDescription' => 'Object Manager instance',
                'tags'             => array(
                    array('name' => 'var', 'description' => '\Magento_ObjectManager')
                )
            ),
        );

        return array($className, $objectManager);
    }

    /**
     * Get default constructor definition for generated class
     *
     * @return array
     */
    protected function _getDefaultConstructorDefinition()
    {
        // public function __construct(\Magento_ObjectManager $objectManager)
        return array(
            'name'       => '__construct',
            'parameters' => array(
                array('name' => 'objectManager', 'type' => '\Magento_ObjectManager'),
            ),
            'body' => '$this->_objectManager = $objectManager;',
            'docblock' => array(
                'shortDescription' => ucfirst(static::ENTITY_TYPE) . ' constructor',
                'tags'             => array(
                    array(
                        'name'        => 'param',
                        'description' => '\Magento_ObjectManager $objectManager'
                    ),
                ),
            ),
        );
    }

    /**
     * Returns list of methods for class generator
     *
     * @return mixed
     */
    abstract protected function _getClassMethods();

    /**
     * @return string
     */
    protected function _generateCode()
    {
        $this->_classGenerator
            ->setName($this->_getResultClassName())
            ->addProperties($this->_getClassProperties())
            ->addMethods($this->_getClassMethods())
            ->setClassDocBlock($this->_getClassDocBlock());

        return $this->_getGeneratedCode();
    }

    /**
     * @param string $message
     * @return Magento_Di_Generator_EntityAbstract
     */
    protected function _addError($message)
    {
        $this->_errors[] = $message;
        return $this;
    }

    /**
     * @return bool
     */
    protected function _validateData()
    {
        $sourceClassName = $this->_getSourceClassName();
        $resultClassName = $this->_getResultClassName();
        $resultFileName  = $this->_ioObject->getResultFileName($resultClassName);

        $autoloader = $this->_autoloader;

        if (!$autoloader::getFile($sourceClassName)) {
            $this->_addError('Source class ' . $sourceClassName . ' doesn\'t exist.');
            return false;
        } elseif ($autoloader::getFile($resultClassName)) {
            $this->_addError('Result class ' . $resultClassName . ' already exists.');
            return false;
        } elseif (!$this->_ioObject->makeGenerationDirectory()) {
            $this->_addError('Can\'t create directory ' . $this->_ioObject->getGenerationDirectory() . '.');
            return false;
        } elseif (!$this->_ioObject->makeResultFileDirectory($resultClassName)) {
            $this->_addError(
                'Can\'t create directory ' . $this->_ioObject->getResultFileDirectory($resultClassName) . '.'
            );
            return false;
        } elseif ($this->_ioObject->fileExists($resultFileName)) {
            $this->_addError('Result file ' . $resultFileName . ' already exists.');
            return false;
        }
        return true;
    }

    /**
     * @return array
     */
    protected function _getClassDocBlock()
    {
        $description = ucfirst(static::ENTITY_TYPE) . ' class for ' . $this->_getSourceClassName();
        return array('shortDescription' => $description);
    }

    /**
     * @return string
     */
    protected function _getGeneratedCode()
    {
        $sourceCode = $this->_classGenerator->generate();
        return $this->_fixCodeStyle($sourceCode);
    }

    /**
     * @param string $sourceCode
     * @return mixed
     */
    protected function _fixCodeStyle($sourceCode)
    {
        $sourceCode = str_replace(' array (', ' array(', $sourceCode);
        $sourceCode = preg_replace("/{\n{2,}/m", "{\n", $sourceCode);
        $sourceCode = preg_replace("/\n{2,}}/m", "\n}", $sourceCode);
        return $sourceCode;
    }

    /**
     * Escape method parameter default value
     *
     * @param string $value
     * @return string
     */
    protected function _escapeDefaultValue($value)
    {
        // escape slashes
        return str_replace('\\', '\\\\', $value);
    }

    /**
     * Get value generator for null default value
     *
     * @return \Zend\Code\Generator\ValueGenerator
     */
    protected function _getNullDefaultValue()
    {
        $value = new \Zend\Code\Generator\ValueGenerator(null, \Zend\Code\Generator\ValueGenerator::TYPE_NULL);

        return $value;
    }
}
