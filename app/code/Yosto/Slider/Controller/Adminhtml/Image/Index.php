<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */
namespace Yosto\Slider\Controller\Adminhtml\Image;

use Yosto\Slider\Controller\Adminhtml\Image;
/**
 * Class Index
 * @package Yosto\Slider\Controller\Adminhtml\Image
 */
class Index extends Image
{
    public function execute()
    {
        if ($this->getRequest()->getQuery('ajax')) {
            $resultForward = $this->resultForwardFactory->create();
            $resultForward->forward('grid');
            return $resultForward;
        }
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Yosto_Slider::manage_image');
        $resultPage->getConfig()->getTitle()->prepend(__('Images'));
        $resultPage->addBreadcrumb(__('Images'), __('Images'));
        $resultPage->addBreadcrumb(__('Manage Images'), __('Manage Images'));
        return $resultPage;
    }

}