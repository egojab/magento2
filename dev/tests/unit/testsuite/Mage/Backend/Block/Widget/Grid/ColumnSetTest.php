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
 * @package     Mage_Backend
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Backend_Block_Widget_Grid_ColumnSetTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Backend_Block_Widget_Grid_ColumnSet
     */
    protected $_model;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_layoutMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_columnMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_helperMock;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    protected $_factoryMock;

    protected function setUp()
    {
        $this->_columnMock = $this->getMock('Mage_Backend_Block_Widget_Grid_Column',
            array('setSortable', 'setRendererType', 'setFilterType'), array(), '', false);
        $this->_layoutMock = $this->getMock('Mage_Core_Model_Layout', array(), array(), '', false);
        $this->_layoutMock
            ->expects($this->any())
            ->method('getChildBlocks')
            ->will($this->returnValue(array($this->_columnMock)));
        $this->_helperMock = $this->getMock('Mage_Backend_Helper_Data', array(), array(), '', false);
        $this->_helperMock
            ->expects($this->any())
            ->method('__')
            ->will($this->returnValue('TRANSLATED STRING'));
        $this->_factoryMock = $this->getMock('Mage_Backend_Model_Widget_Grid_Row_UrlGeneratorFactory', array(), array(),
            '', false
        );

        $arguments = array(
            'layout'           => $this->_layoutMock,
            'helper'           => $this->_helperMock,
            'generatorFactory' => $this->_factoryMock
        );

        $objectManagerHelper = new Magento_Test_Helper_ObjectManager($this);
        $this->_model = $objectManagerHelper->getBlock('Mage_Backend_Block_Widget_Grid_ColumnSet', $arguments);
    }

    public function tearDown()
    {
        unset($this->_model);
        unset($this->_layoutMock);
        unset($this->_columnMock);
        unset($this->_helperMock);
        unset($this->_factoryMock);
    }

    public function testSetSortablePropagatesSortabilityToChildren()
    {
        $this->_columnMock->expects($this->once())->method('setSortable')->with(false);
        $this->_model->setSortable(false);
    }

    public function testSetSortablePropagatesSortabilityToChildrenOnlyIfSortabilityIsFalse()
    {
        $this->_columnMock->expects($this->never())->method('setSortable');
        $this->_model->setSortable(true);
    }

    public function testSetRendererTypePropagatesRendererTypeToColumns()
    {
        $this->_columnMock->expects($this->once())->method('setRendererType')->with('renderer', 'Renderer_Class');
        $this->_model->setRendererType('renderer', 'Renderer_Class');
    }

    public function testSetFilterTypePropagatesFilterTypeToColumns()
    {
        $this->_columnMock->expects($this->once())->method('setFilterType')->with('filter', 'Filter_Class');
        $this->_model->setFilterType('filter', 'Filter_Class');
    }

    public function testGetRowUrlIfUrlPathNotSet()
    {
        $this->assertEquals('#', $this->_model->getRowUrl(new StdClass()));
    }

    public function testGetRowUrl()
    {
        $generatorClass = 'Mage_Backend_Model_Widget_Grid_Row_UrlGenerator';

        $itemMock = $this->getMock('Varien_Object', array(), array(), '', false);

        $rowUrlGenerator = $this->getMock('Mage_Backend_Model_Widget_Grid_Row_UrlGenerator', array('getUrl'), array(),
            '', false
        );
        $rowUrlGenerator->expects($this->once())
            ->method('getUrl')
            ->with($this->equalTo($itemMock))
            ->will($this->returnValue('http://localhost/mng/item/edit'));

        $factoryMock = $this->getMock('Mage_Backend_Model_Widget_Grid_Row_UrlGeneratorFactory',
            array('createUrlGenerator'), array(), '', false
        );
        $factoryMock->expects($this->once())
            ->method('createUrlGenerator')
            ->with($this->equalTo($generatorClass),
            $this->equalTo(array('args' => array('generatorClass' => $generatorClass)))
        )
            ->will($this->returnValue($rowUrlGenerator));

        $arguments = array(
            'layout'           => $this->_layoutMock,
            'helper'           => $this->_helperMock,
            'generatorFactory' => $factoryMock,
            'data'             => array(
                'rowUrl' => array('generatorClass' => $generatorClass)
            )
        );

        $objectManagerHelper = new Magento_Test_Helper_ObjectManager($this);
        /** @var $model Mage_Backend_Block_Widget_Grid_ColumnSet */
        $model = $objectManagerHelper->getBlock('Mage_Backend_Block_Widget_Grid_ColumnSet', $arguments);

        $url = $model->getRowUrl($itemMock);
        $this->assertEquals('http://localhost/mng/item/edit', $url);
    }
}
