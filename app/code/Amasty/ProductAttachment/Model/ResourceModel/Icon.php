<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */

namespace Amasty\ProductAttachment\Model\ResourceModel;

class Icon extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Model Initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('amasty_file_icon', 'id');
    }

    public function getIcon($fileExtension)
    {
        $select = $this->getConnection()
                       ->select()
                       ->from($this->getTable('amasty_file_icon'), 'image')
                       ->where('is_active = 1')
                       ->where('FIND_IN_SET(?, type)', $fileExtension);

        return $this->getConnection()->fetchOne($select);
    }
}
