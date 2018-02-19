<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */

namespace Amasty\ProductAttachment\Controller\Adminhtml;

abstract class File extends \Magento\Backend\App\Action
{

    protected function _isAllowed()
    {
        return true;
    }
}
