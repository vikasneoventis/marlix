<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */
namespace Yosto\Slider\Controller\Adminhtml\Slide;
use Yosto\Slider\Controller\Adminhtml\Slide;

/**
 * Class Index
 * @package Yosto\Slider\Controller\Adminhtml\Slide
 */
class Index extends Slide
{
    public function execute()
    {
        if ($this->getRequest()->getQuery('ajax')) {
            $resultForward = $this->resultForwardFactory->create();
            $resultForward->forward('grid');
            return $resultForward;
        }
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Yosto_Slider::manage_slide');
        $resultPage->getConfig()->getTitle()->prepend(__('Sliders'));
        $resultPage->addBreadcrumb(__('Sliders'), __('Sliders'));
        $resultPage->addBreadcrumb(__('Manage Sliders'), __('Manage Sliders'));
        return $resultPage;
    }
}