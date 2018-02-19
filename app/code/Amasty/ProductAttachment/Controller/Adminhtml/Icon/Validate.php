<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */

namespace Amasty\ProductAttachment\Controller\Adminhtml\Icon;

use Magento\Framework\Exception\LocalizedException;

/**
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Validate extends \Amasty\ProductAttachment\Controller\Adminhtml\Icon
{

    /**
     * Validate icon
     *
     * @return \Magento\Framework\Controller\Result\Json
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $response = new \Magento\Framework\DataObject();
        $response->setError(false);

        try {
            $iconData = $this->getRequest()->getPost('icon');

            $this->validateIcon($response, $iconData);

        } catch (\Exception $e) {
            $this->addErrorToResponse($response, $e->getMessage());
        }

        return $this->resultJsonFactory->create()->setData($response);
    }

    protected function validateIcon($response, $iconData)
    {
        try {
            if (!$iconData['type']) {
                throw new LocalizedException(__('Field "Type" is required'));
            }
        } catch (LocalizedException $e) {
            $this->addErrorToResponse($response, $e->getMessage());
        }

    }

    protected function addErrorToResponse($response, $message)
    {
        $response->setError(true);
        $messages = $response->hasMessages() ? $response->getMessages() : [];
        $messages[] = $message;
        $response->setMessages($messages);
    }

}
