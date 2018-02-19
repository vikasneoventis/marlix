<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */
namespace Yosto\Slider\Controller\Adminhtml\Slide;
use Yosto\Slider\Controller\Adminhtml\Slide;
use Yosto\Slider\Helper\Constant;

/**
 * Class Save
 * @package Yosto\Slider\Controller\Adminhtml\Slide
 */
class Save extends Slide
{
    public function execute()
    {
        $isPost = $this->getRequest()->getPost();
        if ($isPost) {
            $slideModel = $this->slideFactory->create();
            $slide = $this->getRequest()->getParam('slide');
            if(array_key_exists('slide_id', $slide)) {
                $slideId = $slide['slide_id'];
                if ($slideId) {
                    $slideModel->load($slideId);
                    $slideImageModel = $this->slideImageFactory->create()->getCollection()->addFieldToFilter('slide_id', $slideId);
                    foreach ($slideImageModel as $slideImage) {
                        $slideImage->delete();
                    }

                }
            }
            $formData = $this->getRequest()->getParam('slide');
            $slideModel->setData($formData);
            try {
                // Save slide
                $slideModel->save();

                if(array_key_exists('image',$slide)){
                    $selectedImages=$slide['image'];
                    for($i=0;$i<count($selectedImages);$i++){
                        $slideImageModel=$this->slideImageFactory->create();
                        $slideImageModel->setData('image_id',$selectedImages[$i]);
                        $slideImageModel->setData('slide_id',$slideModel->getSlideId());
                        $slideImageModel->setData('is_active',true);
                        $slideImageModel->save();
                    }
                }

                // Display success message
                $this->messageManager->addSuccess(__('The slide has been saved.'));

                // Check if 'Save and Continue'
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', ['slide_id' => $slideModel->getSlideId(), '_current' => true]);
                    return;
                }

                // Go to grid page
                $this->_redirect('*/*/');
                return;
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }

            $this->_getSession()->setFormData($formData);
            $this->_redirect('*/*/index', ['slide_id' => $slideId]);

        }

    }

}