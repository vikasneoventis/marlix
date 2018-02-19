<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */
namespace Yosto\Slider\Controller\Adminhtml\Slide;
use Yosto\Slider\Controller\Adminhtml\Slide;

/**
 * Class MassDelete
 * @package Yosto\Slider\Controller\Adminhtml\Slide
 */
class MassDelete extends Slide
{
    public function execute()
    {
        $slideIds = $this->getRequest()->getParam('slide');
        $slideModel = $this->slideFactory->create();
        foreach ($slideIds as $slideId) {
            try {
                $slideModel->load($slideId)->delete();
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }

        if (count($slideIds)) {
            $this->messageManager->addSuccess(
                __('A total of %1 record(s) were deleted.', count($slideIds))
            );
        }

        $this->_redirect('*/*/index');
    }
}