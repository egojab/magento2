<?php
/**
 * Web API Role edit page tabs.
 *
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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @method Mage_Webapi_Block_Adminhtml_Role_Edit_Tabs setApiRole() setApiRole(Mage_Webapi_Model_Acl_Role $role)
 * @method Mage_Webapi_Model_Acl_Role getApiRole() getApiRole()
 */
class Mage_Webapi_Block_Adminhtml_Role_Edit_Tabs extends Mage_Backend_Block_Widget_Tabs
{
    /**
     * Internal Constructor.
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setId('page_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle($this->__('Role Information'));
    }

    /**
     * Prepare child blocks.
     *
     * @return Mage_Core_Block_Abstract
     */
    protected function _beforeToHtml()
    {
        /** @var Mage_Webapi_Block_Adminhtml_Role_Edit_Tab_Main $mainBlock */
        $mainBlock = $this->getLayout()->getBlock('webapi.role.edit.tab.main');
        $mainBlock->setApiRole($this->getApiRole());
        $this->addTab('main_section', array(
            'label' => $this->__('Role Info'),
            'title' => $this->__('Role Info'),
            'content' => $mainBlock->toHtml(),
            'active' => true
        ));

        /** @var Mage_Webapi_Block_Adminhtml_Role_Edit_Tab_Resource $resourceBlock */
        $resourceBlock = $this->getLayout()->getBlock('webapi.role.edit.tab.resource');
        $resourceBlock->setApiRole($this->getApiRole());
        $this->addTab('resource_section', array(
            'label' => $this->__('Resources'),
            'title' => $this->__('Resources'),
            'content' => $resourceBlock->toHtml()
        ));

        if ($this->getApiRole() && $this->getApiRole()->getRoleId() > 0) {
            $usersGrid = $this->getLayout()->getBlock('webapi.role.edit.tab.users.grid');
            $this->addTab('user_section', array(
                'label' => $this->__('Users'),
                'title' => $this->__('Users'),
                'content' => $usersGrid->toHtml()
            ));
        }

        return parent::_beforeToHtml();
    }

}
