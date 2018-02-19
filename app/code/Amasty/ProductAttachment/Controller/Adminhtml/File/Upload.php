<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */

namespace Amasty\ProductAttachment\Controller\Adminhtml\File;

use Amasty\ProductAttachment\Controller\Adminhtml;
use Magento\Framework\Controller\ResultFactory;

class Upload extends Adminhtml\File
{

    /**
     * Downloadable file helper.
     *
     * @var \Magento\Downloadable\Helper\File
     */
    protected $fileHelper;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Downloadable\Helper\File $fileHelper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Downloadable\Helper\File $fileHelper
    ) {
        parent::__construct($context);

        $this->fileHelper = $fileHelper;
    }

    /**
     * Upload file controller action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $type = $this->getRequest()->getParam('type');

        try {
            $result = $this->getUploader()->uploadFileToAttachTmpFolder($type);

            $result['cookie'] = [
                'name' => $this->_getSession()->getName(),
                'value' => $this->_getSession()->getSessionId(),
                'lifetime' => $this->_getSession()->getCookieLifetime(),
                'path' => $this->_getSession()->getCookiePath(),
                'domain' => $this->_getSession()->getCookieDomain(),
            ];
        } catch (\Exception $e) {
            $result = ['error' => $e->getMessage(), 'errorcode' => $e->getCode()];
        }
        return $this->resultFactory->create(ResultFactory::TYPE_JSON)->setData($result);
    }

    /**
     * @return \Amasty\ProductAttachment\Helper\Uploader
     */
    public function getUploader()
    {
        return $this->_objectManager->create('Amasty\ProductAttachment\Helper\Uploader');
    }
}
