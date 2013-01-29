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
 * @package     Mage_Core
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Core_Model_Config_OptionsTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Config_Options
     */
    protected $_model;

    protected static $_keys = array(
        'app_dir'     => 'app',
        'base_dir'    => 'base',
        'code_dir'    => 'code',
        'design_dir'  => 'design',
        'etc_dir'     => 'etc',
        'lib_dir'     => 'lib',
        'locale_dir'  => 'locale',
        'pub_dir'     => 'pub',
        'js_dir'      => 'js',
        'media_dir'   => 'media',
        'var_dir'     => 'var',
        'tmp_dir'     => 'tmp',
        'cache_dir'   => 'cache',
        'log_dir'     => 'log',
        'session_dir' => 'session',
        'upload_dir'  => 'upload',
        'export_dir'  => 'export',
    );

    protected function setUp()
    {
        $this->_model = Mage::getModel('Mage_Core_Model_Config_Options');
    }

    protected function tearDown()
    {
        $this->_model = null;
    }

    public function testConstruct()
    {
        $data = $this->_model->getData();
        foreach (array_keys(self::$_keys) as $key) {
            $this->assertArrayHasKey($key, $data);
            unset($data[$key]);
        }
        $this->assertEmpty($data);
    }

    public function testGetDir()
    {
        foreach (self::$_keys as $full => $partial) {
            $this->assertEquals($this->_model->getData($full), $this->_model->getDir($partial));
        }
    }

    /**
     * @expectedException Mage_Core_Exception
     */
    public function testGetDirException()
    {
        $this->_model->getDir('invalid');
    }

    /**
     * @covers Mage_Core_Model_Config_Options::getAppDir
     * @covers Mage_Core_Model_Config_Options::getBaseDir
     * @covers Mage_Core_Model_Config_Options::getCodeDir
     * @covers Mage_Core_Model_Config_Options::getDesignDir
     * @covers Mage_Core_Model_Config_Options::getEtcDir
     * @covers Mage_Core_Model_Config_Options::getLibDir
     * @covers Mage_Core_Model_Config_Options::getMediaDir
     * @covers Mage_Core_Model_Config_Options::getVarDir
     * @covers Mage_Core_Model_Config_Options::getTmpDir
     * @covers Mage_Core_Model_Config_Options::getCacheDir
     * @covers Mage_Core_Model_Config_Options::getLogDir
     * @covers Mage_Core_Model_Config_Options::getSessionDir
     * @covers Mage_Core_Model_Config_Options::getUploadDir
     * @covers Mage_Core_Model_Config_Options::getExportDir
     * @dataProvider getGettersDataProvider
     * @param string $method
     */
    public function testGetters($method)
    {
        $dir = $this->_model->$method();
        $this->assertFileExists($dir, "Method '{$method}()' returned directory that doesn't exist: '{$dir}'");
    }

    /**
     * @return array
     */
    public function getGettersDataProvider()
    {
        return array(
            array('getAppDir'),
            array('getBaseDir'),
            array('getCodeDir'),
            array('getDesignDir'),
            array('getEtcDir'),
            array('getLibDir'),
            array('getMediaDir'),
            array('getVarDir'),
            array('getTmpDir'),
            array('getCacheDir'),
            array('getLogDir'),
            array('getSessionDir'),
            array('getUploadDir'),
            array('getExportDir'),
        );
    }

    public function testCreateDirIfNotExists()
    {
        $var = $this->_model->getVarDir();

        $sampleDir = uniqid($var);
        $this->assertTrue($this->_model->createDirIfNotExists($sampleDir));
        $this->assertTrue($this->_model->createDirIfNotExists($sampleDir));
        rmdir($sampleDir);

        $sampleFile = "{$var}/" . uniqid('file') . '.txt';
        file_put_contents($sampleFile, '1');
        $this->assertFalse($this->_model->createDirIfNotExists($sampleFile));
        unlink($sampleFile);
    }
}
