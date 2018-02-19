<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */

namespace Amasty\ProductAttachment\Model;

/**
 *
 * @method int getProductId
 * @method int getFileId
 * @method int getStoreId
 * @method int getCustomerId
 * @method string getFilePath
 * @method string getFileName
 * @method string getDownloadedAt
 * @method string getRating
 * @method int setProductId
 *
 * @method int setFileId
 * @method int setStoreId
 * @method int setCustomerId
 * @method string setFilePath
 * @method string setFileName
 * @method string setDownloadedAt
 * @method string setRating
 *
 * @package Amasty\ProductAttachment\Model
 */
class Downloads extends \Magento\Framework\Model\AbstractModel
{

    /**
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Amasty\ProductAttachment\Model\ResourceModel\Downloads');
    }

}