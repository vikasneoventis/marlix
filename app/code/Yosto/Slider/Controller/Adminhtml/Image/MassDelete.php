<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */
namespace Yosto\Slider\Controller\Adminhtml\Image;

use Yosto\Slider\Controller\Adminhtml\Image;
/**
 * Class MassDelete
 * @package Yosto\Slider\Controller\Adminhtml\Image
 */
class MassDelete extends Image
{
    public function execute()
    {
        $imageIds = $this->getRequest()->getParam('image');
        $imageModel = $this->imageFactory->create();
        foreach ($imageIds as $imageId) {
            try {
                $imageModel->load($imageId)->delete();
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }

        if (count($imageIds)) {
            $this->messageManager->addSuccess(
                __('A total of %1 record(s) were deleted.', count($imageIds))
            );
        }

        $this->_redirect('*/*/index');
    }
}