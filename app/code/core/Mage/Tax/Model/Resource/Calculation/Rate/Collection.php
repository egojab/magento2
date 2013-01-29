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
 * @category    Mage
 * @package     Mage_Tax
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Tax rate collection
 *
 * @category    Mage
 * @package     Mage_Tax
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Tax_Model_Resource_Calculation_Rate_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Resource initialization
     */
    protected function _construct()
    {
        $this->_init('Mage_Tax_Model_Calculation_Rate', 'Mage_Tax_Model_Resource_Calculation_Rate');
    }

    /**
     * Join country table to result
     *
     * @return Mage_Tax_Model_Resource_Calculation_Rate_Collection
     */
    public function joinCountryTable()
    {
        $this->_select->join(
            array('country_table' => $this->getTable('directory_country')),
            'main_table.tax_country_id = country_table.country_id',
            array('country_name' => 'iso2_code')
        );

        return $this;
    }

    /**
     * Join Region Table
     *
     * @return Mage_Tax_Model_Resource_Calculation_Rate_Collection
     */
    public function joinRegionTable()
    {
        $this->_select->joinLeft(
            array('region_table' => $this->getTable('directory_country_region')),
            'main_table.tax_region_id = region_table.region_id',
            array('region_name' => 'code')
        );
        return $this;
    }

    /**
     * Join rate title for specified store
     *
     * @param Mage_Core_Model_Store|string|int $store
     * @return Mage_Tax_Model_Resource_Calculation_Rate_Collection
     */
    public function joinTitle($store = null)
    {
        $storeId = (int)Mage::app()->getStore($store)->getId();
        $this->_select->joinLeft(
            array('title_table' => $this->getTable('tax_calculation_rate_title')),
            $this->getConnection()->quoteInto('main_table.tax_calculation_rate_id = title_table.tax_calculation_rate_id AND title_table.store_id = ?', $storeId),
            array('title' => 'value')
        );

        return $this;
    }

    /**
     * Joins store titles for rates
     *
     * @return Mage_Tax_Model_Resource_Calculation_Rate_Collection
     */
    public function joinStoreTitles()
    {
        $storeCollection =  Mage::app()->getStores(true);
        foreach ($storeCollection as $store) {
            $tableAlias    = sprintf('title_table_%s', $store->getId());
            $joinCondition = implode(' AND ', array(
                "main_table.tax_calculation_rate_id = {$tableAlias}.tax_calculation_rate_id",
                $this->getConnection()->quoteInto($tableAlias . '.store_id = ?', $store->getId())
            ));
            $this->_select->joinLeft(
                array($tableAlias => $this->getTable('tax_calculation_rate_title')),
                $joinCondition,
                array($tableAlias => 'value')
            );
        }
        return $this;
    }

    /**
     * Add rate filter
     *
     * @param int $rateId
     * @return Mage_Tax_Model_Resource_Calculation_Rate_Collection
     */
    public function addRateFilter($rateId)
    {
        if (is_int($rateId) && $rateId > 0) {
            return $this->addFieldToFilter('main_table.tax_rate_id', $rateId);
        }

        return $this;
    }

    /**
     * Retrieve option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_toOptionArray('tax_calculation_rate_id', 'code');
    }

    /**
     * Retrieve option hash
     *
     * @return array
     */
    public function toOptionHash()
    {
        return $this->_toOptionHash('tax_calculation_rate_id', 'code');
    }

    /**
     * Convert items array to hash for select options
     * unsing fetchItem method
     *
     * @see     _toOptionHashOptimized()
     *
     * @return  array
     */
    public function toOptionHashOptimized()
    {
        return $this->_toOptionHashOptimized('tax_calculation_rate_id', 'code');
    }
}

