<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */

namespace Amasty\ProductAttachment\Controller\Adminhtml;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

abstract class Icon extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Registry $registry,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
    ) {

        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->registry = $registry;

    }

    /**
     * Check the permission to run it
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_ProductAttachment::icon');
    }

    /**
     * @return \Amasty\ProductAttachment\Model\Icon
     */
    protected function createIconModel()
    {
        return $this->_objectManager->create('Amasty\ProductAttachment\Model\Icon');
    }

    /**
     * @return \Amasty\ProductAttachment\Model\Icon
     */
    protected function getIconModel()
    {
        return $this->_objectManager->get('Amasty\ProductAttachment\Model\Icon');
    }

    protected function getIconId($iconData)
    {
        return array_key_exists('id', $iconData) ? $iconData['id'] : null;
    }
}
