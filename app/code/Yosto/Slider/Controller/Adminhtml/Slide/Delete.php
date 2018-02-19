<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */
namespace Yosto\Slider\Controller\Adminhtml\Slide;


use Yosto\Slider\Controller\Adminhtml\Slide;

/**
 * Class Delete
 * @package Yosto\Slider\Controller\Adminhtml\Slide
 */
class Delete extends Slide
{
    public function execute()
    {
        $slideId=$this->getRequest()->getParam('slide_id');
        if($slideId) {
            $slideModel = $this->slideFactory->create();
            $slideModel->load($slideId);
            if (!$slideModel->getSlideId()) {
                $this->messageManager->addError(__('Slide is no longer exist'));
            } else {
                try {
                    $slideModel->delete();
                    $this->messageManager->addSuccess(__('Deleted Successfully!'));
                    $this->_redirect('*/*/');
                } catch (\Exception $e) {
                    $this->messageManager->addError($e->getMessage());
                    $this->_redirect('*/*/edit', ['id' => $slideModel->getId()]);
                }
            }
        }
    }
}