<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */

namespace Amasty\ProductAttachment\Model;

/**
 *
 * @method getFileId
 * @method getProductId
 * @method getStoreId
 * @method getCustomerId
 * @method getFilePath
 * @method getFileName
 * @method getDownloadedAt
 *
 * @package Amasty\ProductAttachment\Model
 */
class Stat extends \Magento\Framework\Model\AbstractModel
{

    protected function _construct()
    {
        parent::_construct();
        $this->_init('Amasty\ProductAttachment\Model\ResourceModel\Stat');
    }
}