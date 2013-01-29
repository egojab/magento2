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
 * @package     Mage_ImportExport
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * ImportExport data helper
 *
 * @category    Mage
 * @package     Mage_ImportExport
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_ImportExport_Helper_Data extends Mage_Core_Helper_Data
{
    /**#@+
     * XML path for config data
     */
    const XML_PATH_EXPORT_LOCAL_VALID_PATH = 'general/file/importexport_local_valid_paths';
    const XML_PATH_BUNCH_SIZE = 'general/file/bunch_size';
    /**#@-*/

    /**
     * Get maximum upload size message
     *
     * @return string
     */
    public function getMaxUploadSizeMessage()
    {
        $maxImageSize = Mage::getObjectManager()->get('Magento_File_Size')->getMaxFileSizeInMb();
        if ($maxImageSize) {
            $message = $this->__('Total size of uploadable files must not exceed %sM', $maxImageSize);
        } else {
            $message = $this->__('System doesn\'t allow to get file upload settings');
        }
        return $message;
    }

    /**
     * Get valid path masks to files for importing/exporting
     *
     * @return array
     */
    public function getLocalValidPaths()
    {
        $paths = Mage::getStoreConfig(self::XML_PATH_EXPORT_LOCAL_VALID_PATH);
        return $paths;
    }

    /**
     * Retrieve size of bunch (how much products should be involved in one import iteration)
     *
     * @return int
     */
    public function getBunchSize()
    {
        return (int)Mage::getStoreConfig(self::XML_PATH_BUNCH_SIZE);
    }
}
