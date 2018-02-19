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
use Magento\Framework\View\Result\PageFactory;

class Index extends Adminhtml\File
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    public function __construct(Action\Context $context, PageFactory $pageFactory)
    {
        $this->resultPageFactory = $pageFactory;
        parent::__construct($context);
    }


    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Amasty_ProductAttachment::files_list');
        $resultPage->addBreadcrumb(__('List Attachments'), __('List Attachments'));
        $resultPage->getConfig()->getTitle()->prepend(__('List Attachments'));

        return $resultPage;
    }
}
