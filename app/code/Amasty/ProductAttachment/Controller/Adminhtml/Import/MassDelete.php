<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */

namespace Amasty\ProductAttachment\Controller\Adminhtml\Import;

class MassDelete extends \Amasty\ProductAttachment\Controller\Adminhtml\Import
{
    /**
     * @return \Magento\Backend\App\Action
     */
    public function execute()
    {
        $backupIds = $this->getRequest()->getParam('ids', []);

        if (!is_array($backupIds) || !count($backupIds)) {
            return $this->_redirect('amfile/import/index/active_tab/files_section');
        }

        $resultData = new \Magento\Framework\DataObject();
        $resultData->setIsSuccess(false);
        $resultData->setDeleteResult([]);

        try {
            $allBackupsDeleted = true;

            foreach ($backupIds as $fileName) {
                $ftpFileModel = $this->createFtpFileModel();
                $ftpFileModel->loadByFileName($fileName);
                $ftpFileModel->deleteFile();

                if ($ftpFileModel->exists()) {
                    $allBackupsDeleted = false;
                    $result = __('failed');
                } else {
                    $result = __('successful');
                }

                $resultData->setDeleteResult(
                    array_merge($resultData->getDeleteResult(), [$ftpFileModel->getName() . ' ' . $result])
                );
            }

            $resultData->setIsSuccess(true);
            if ($allBackupsDeleted) {
                $this->messageManager->addSuccess(__('You deleted the selected files(s).'));
            } else {
                throw new \Exception('We can\'t delete one or more files.');
            }
        } catch (\Exception $e) {
            $resultData->setIsSuccess(false);
            $this->messageManager->addError(__($e->getMessage()));
        }

        return $this->_redirect('amfile/import/index/active_tab/files_section');
    }

    /**
     * @return \Amasty\ProductAttachment\Model\FtpFile
     */
    public function createFtpFileModel()
    {
        return $this->_objectManager->create('Amasty\ProductAttachment\Model\FtpFile');
    }

}
