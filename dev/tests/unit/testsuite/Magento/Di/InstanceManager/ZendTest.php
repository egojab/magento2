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
 * @package     Magento
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Magento_Di_InstanceManager_Zend
 */
class Magento_Di_InstanceManager_ZendTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test object alias
     */
    const TEST_ALIAS = 'test_alias';

    /**
     * Shared instances attribute name
     */
    const SHARED_ATTRIBUTE = 'sharedInstances';

    /**
     * Test class name
     */
    const TEST_CLASS = 'Test_Class_Name';

    public function testRemoveSharedInstance()
    {
        $instanceManager = new Magento_Di_InstanceManager_Zend();
        $this->assertAttributeEmpty(self::SHARED_ATTRIBUTE, $instanceManager);

        $testObject = new Varien_Object();
        $instanceManager->addSharedInstance($testObject, self::TEST_ALIAS);
        $this->assertAttributeEquals(
            array(self::TEST_ALIAS => $testObject),
            self::SHARED_ATTRIBUTE,
            $instanceManager
        );

        $instanceManager->removeSharedInstance(self::TEST_ALIAS);
        $this->assertAttributeEmpty(self::SHARED_ATTRIBUTE, $instanceManager);
    }

    public function testAddTypePreference()
    {
        $generatorMock = $this->getMock('Magento_Di_Generator', array('generateClass'), array(), '', false);
        $generatorMock->expects($this->once())
            ->method('generateClass')
            ->with(self::TEST_CLASS);

        $instanceManager = new Magento_Di_InstanceManager_Zend($generatorMock);
        $this->assertInstanceOf(
            'Magento_Di_InstanceManager_Zend',
            $instanceManager->addTypePreference('Interface', self::TEST_CLASS)
        );
    }

    public function testSetParameters()
    {
        $generatorMock = $this->getMock('Magento_Di_Generator', array('generateClass'), array(), '', false);
        $generatorMock->expects($this->once())
            ->method('generateClass')
            ->with(self::TEST_CLASS);

        $instanceManager = new Magento_Di_InstanceManager_Zend($generatorMock);
        $instanceManager->setParameters('Class', array('parameter' => self::TEST_CLASS));
    }
}
