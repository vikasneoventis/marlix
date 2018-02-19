<?php
/**
 * Copyright © 2016 x-mage2(Yosto). All rights reserved.
 * See README.md for details.
 */
namespace Yosto\Slider\Controller\Adminhtml\Slide;

use Yosto\Slider\Controller\Adminhtml\Slide;

/**
 * Class Edit
 * @package Yosto\Slider\Controller\Adminhtml\Slide
 */
class Edit extends Slide
{
    public function execute()
    {
        $slideId = $this->getRequest()->getParam('slide_id');
        $model = $this->slideFactory->create();
        $slideImageModel=$this->slideImageFactory->create();
        $slideImageArray=[];
        $slideTitle = "";

        if ($slideId) {
            $model->load($slideId);
            if (!$model->getSlideId()) {
                $this->messageManager->addError(__('This slide no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }else{
                $slideImageCollection=$slideImageModel->getCollection()->addFieldToFilter('slide_id',array('eq'=>$slideId));
                foreach($slideImageCollection as $item){
                    $slideImageArray[]=$item->getImageId();
                }

                $slideTitle = $model->getTitle();
            }
        }
        // Restore previously entered form data from session
        $data = $this->_session->getSlideData(true);
        if (!empty($data)) {
            $model->setData($data);
        }
        $this->coreRegistry->register('slider_slide', $model);
        $this->coreRegistry->register('slider_slide_selected_images',$slideImageArray);
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Yosto_Slider::main_menu');
        $resultPage->getConfig()->getTitle()->prepend(__('Slider ').$slideTitle);

        return $resultPage;
    }

}