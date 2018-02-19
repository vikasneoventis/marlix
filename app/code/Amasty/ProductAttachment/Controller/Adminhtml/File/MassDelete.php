<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */

/**
 * Copyright © 2016 Amasty. All rights reserved.
 */

namespace Amasty\ProductAttachment\Controller\Adminhtml\File;

use Amasty\ProductAttachment\Controller\Adminhtml;
use Magento\Backend\App\Action;

class MassDelete extends Adminhtml\File
{
    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $filter;

    /**
     * @var \Amasty\ProductAttachment\Model\ResourceModel\File\CollectionFactory
     */
    protected $collectionFactory;


    public function __construct(
        Action\Context $context,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Amasty\ProductAttachment\Model\ResourceModel\File\CollectionFactory $collectionFactory
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }


    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $productDeleted = 0;
            foreach($collection as $item) {
                $item->delete();
                $productDeleted++;
            }
            $this->messageManager->addSuccess(
                __('A total of %1 record(s) have been deleted.', $productDeleted)
            );

        } catch (\Exception $e) {
            $this->messageManager->addError(
                __('Something went wrong while export feed data. Please review the error log.')
            );
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
        }

        $this->_redirect('*/*/index');
    }
}
