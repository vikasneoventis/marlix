<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */
namespace Yosto\Slider\Controller\Adminhtml\Image;

use Yosto\Slider\Controller\Adminhtml\Image;
/**
 * Class Delete
 * @package Yosto\Slider\Controller\Adminhtml\Image
 */
class Delete extends Image
{
    public function execute()
    {
       $imageId=$this->getRequest()->getParam('image_id');
        if($imageId) {
            $imageModel = $this->imageFactory->create();
            $imageModel->load($imageId);
            if (!$imageModel->getImageId()) {
                $this->messageManager->addError(__('Image is no longer exist'));
            } else {
                try {
                    $imageModel->delete();
                    $this->messageManager->addSuccess(__('Deleted Successfully!'));
                    $this->_redirect('*/*/');
                } catch (\Exception $e) {
                    $this->messageManager->addError($e->getMessage());
                    $this->_redirect('*/*/edit', ['id' => $imageModel->getId()]);
                }
            }
        }
    }

}