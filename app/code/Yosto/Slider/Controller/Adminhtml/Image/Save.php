<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */
namespace Yosto\Slider\Controller\Adminhtml\Image;

use Magento\Framework\App\Filesystem\DirectoryList;
use Yosto\Slider\Controller\Adminhtml\Image;
/**
 * Class Save
 * @package Yosto\Slider\Controller\Adminhtml\Image
 */
class Save extends Image
{
    public function execute()
    {
        $isPost = $this->getRequest()->getPost();

        if ($isPost) {
            $imageModel = $this->imageFactory->create();
            $data = $this->getRequest()->getParam('image');
            if (array_key_exists('image_id', $data)) {
                $imageId = $data['image_id'];

                if ($imageId) {
                    $imageModel->load($imageId);
                }
            }

            $imageModel->setData($data);
            try {
                // Save news
                $imageModel->save();

                // Display success message
                $this->messageManager->addSuccess(__('The image has been saved.'));

                // Check if 'Save and Continue'
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', ['image_id' => $imageModel->getImageId(), '_current' => true]);
                    return;
                }

                // Go to grid page
                $this->_redirect('*/*/');
                return;
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }

            $this->_getSession()->setFormData($data);
            $this->_redirect('*/*/index', ['image_id' => $imageId]);
        }
    }

}