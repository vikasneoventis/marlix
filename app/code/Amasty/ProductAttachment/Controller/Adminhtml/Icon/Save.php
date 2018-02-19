<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */

namespace Amasty\ProductAttachment\Controller\Adminhtml\Icon;

use Magento\Framework\Exception\LocalizedException;

class Save extends \Amasty\ProductAttachment\Controller\Adminhtml\Icon
{

    /**
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $returnToEdit = false;
        $originalRequestData = $this->getRequest()->getPostValue();

        if ($originalRequestData) {
            try {

                $iconData = $this->extractIconData();
                $iconId = $this->getIconId($iconData);

                $iconModel = $this->createIconModel(['id' => $iconId]);

                if ($iconId) {
                    $iconModel->load($iconId);
                    if ($iconId != $iconModel->getId()) {
                        throw new \Magento\Framework\Exception\LocalizedException(__('The wrong icon is specified.'));
                    }
                }

                $session = $this->_objectManager->get('Magento\Backend\Model\Session');
                $session->setPageData($iconModel->getData());

                $iconModel->addData($iconData);
                $newImageFileName = $this->saveIconFileIfExists();
                if ($newImageFileName != '') {
                    $iconModel->setImage($newImageFileName);
                }
                $iconModel->save();

                $session->setPageData(false);

                $this->messageManager->addSuccess(__('You saved the Icon.'));
                $returnToEdit = (bool)$this->getRequest()->getParam('back', false);
            } catch (\Magento\Framework\Validator\Exception $exception) {
                $messages = $exception->getMessages();
                if (empty($messages)) {
                    $messages = $exception->getMessage();
                }
                $this->_addSessionErrorMessages($messages);
                $returnToEdit = true;
            } catch (LocalizedException $exception) {
                $this->_addSessionErrorMessages($exception->getMessage());
                $returnToEdit = true;
            } catch (\Exception $exception) {
                $this->messageManager->addException($exception, __('Something went wrong while saving the Icon.'));
                $returnToEdit = true;
            }
        }
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($returnToEdit) {
            if ($iconId) {
                $resultRedirect->setPath(
                    '*/*/edit',
                    ['id' => $iconId, '_current' => true]
                );
            } else {
                $resultRedirect->setPath(
                    '*/*/new',
                    ['_current' => true]
                );
            }
        } else {
            $resultRedirect->setPath('amfile/icon');
        }
        return $resultRedirect;
    }

    protected function saveIconFileIfExists()
    {
        $type = 'image';

        $newFileName = '';
        try {
            $result = $this->uploadFile($type);
            $newFileName = $result['file'];
        } catch (\Exception $e) {
            if ($e->getCode() != 666) {
                throw $e;
            }
        }
        return $newFileName;
    }

    protected function uploadFile($fileId)
    {
        return $this->getUploader()->uploadFileToIconFolder($fileId);
    }

    /**
     * @return \Amasty\ProductAttachment\Helper\Uploader
     */
    public function getUploader()
    {
        return $this->_objectManager->create('Amasty\ProductAttachment\Helper\Uploader');
    }

    /**
     * @return array
     */
    protected function extractIconData()
    {
        return $this->getRequest()->getPost('icon');
    }
}
