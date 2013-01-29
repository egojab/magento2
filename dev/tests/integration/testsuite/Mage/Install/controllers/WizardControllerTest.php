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
 * @package     Mage_Install
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Install_WizardControllerTest extends Magento_Test_TestCase_ControllerAbstract
{
    /**
     * @var string
     */
    protected static $_tmpMediaDir;

    /**
     * @var string
     */
    protected static $_tmpThemeDir;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        self::$_tmpMediaDir = realpath(Magento_Test_Bootstrap::getInstance()->getTmpDir())
            . DIRECTORY_SEPARATOR . 'media';
        self::$_tmpThemeDir = self::$_tmpMediaDir . DIRECTORY_SEPARATOR . 'theme';
    }

    public function setUp()
    {
        parent::setUp();
        // emulate non-installed application
        $this->_runOptions[Mage_Core_Model_Config::OPTION_LOCAL_CONFIG_EXTRA_DATA]
            = sprintf(Mage_Core_Model_Config::CONFIG_TEMPLATE_INSTALL_DATE, 'invalid');
    }

    public function tearDown()
    {
        Varien_Io_File::rmdirRecursive(self::$_tmpMediaDir);
        parent::tearDown();
    }

    /**
     * @magentoAppIsolation enabled
     */
    public function testPreDispatch()
    {
        $this->dispatch('install/index');
        $this->assertEquals(200, $this->getResponse()->getHttpResponseCode());
    }

    public function testPreDispatchNonWritableMedia()
    {
        mkdir(self::$_tmpMediaDir, 0444);
        $this->_runOptions['media_dir'] = self::$_tmpMediaDir;

        $this->_testInstallProhibitedWhenNonWritable(self::$_tmpMediaDir);
    }

    public function testPreDispatchNonWritableTheme()
    {
        mkdir(self::$_tmpMediaDir, 0777);
        $this->_runOptions['media_dir'] = self::$_tmpMediaDir;

        mkdir(self::$_tmpThemeDir, 0444);
        $this->_testInstallProhibitedWhenNonWritable(self::$_tmpThemeDir);
    }

    /**
     * Tests that when $nonWritableDir folder is read-only, the installation controller prohibits continuing
     * installation and points to fix issue with theme directory.
     *
     * @param string $nonWritableDir
     */
    protected function _testInstallProhibitedWhenNonWritable($nonWritableDir)
    {
        if (is_writable($nonWritableDir)) {
            $this->markTestSkipped("Current OS doesn't support setting write-access for folders via mode flags");
        }

        $this->dispatch('install/index');

        $this->assertEquals(503, $this->getResponse()->getHttpResponseCode());
        $this->assertContains(self::$_tmpThemeDir, $this->getResponse()->getBody());
    }
}
