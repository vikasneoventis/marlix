<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */
namespace Yosto\Slider\Controller\Adminhtml\Image;

use Yosto\Slider\Controller\Adminhtml\Image;

/**
 * Class Edit
 * @package Yosto\Slider\Controller\Adminhtml\Image
 */
class Edit extends Image
{
    public function execute()
    {
        $imageId = $this->getRequest()->getParam('image_id');
        $model = $this->imageFactory->create();
        $imageName = "";
        if ($imageId) {
            $model->load($imageId);
            if (!$model->getImageId()) {
                $this->messageManager->addError(__('This image no longer exists.'));
                $this->_redirect('*/*/');
                return;
            } else {
                $imageName = $model->getData('name');
            }
        }

// Restore previously entered form data from session
        $data = $this->_session->getImageData(true);
        if (!empty($data)) {
            $model->setData($data);
        }
        $this->coreRegistry->register('slider_image', $model);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Yosto_Slider::main_menu');
        $resultPage->getConfig()->getTitle()->prepend(__('Image ').$imageName);

        return $resultPage;
    }

}