<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_DuplicateCategories
 */


namespace Amasty\DuplicateCategories\Controller\Adminhtml\DuplicateCategory;

class Duplicate extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;
    protected $_helper;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $pageResult = $this->resultPageFactory->create();
        $pageResult->getLayout();
        $pageResult->setActiveMenu('Amasty_DuplicateCategories::amdupcat');
        $pageResult->addBreadcrumb(__('Duplicate Category'), __('Duplicate Category'));
        $this->_addContent($pageResult->getLayout()->createBlock('Amasty\DuplicateCategories\Block\Adminhtml\Category\Duplicate', 'template'));

        $categoryId = $this->getRequest()->getParam('id');
        /** @var \Magento\Catalog\Model\Category $category */
        $category = $this->_objectManager->create('Magento\Catalog\Model\Category')->load($categoryId);

        $pageResult->getConfig()->getTitle()->prepend(__('Duplicate Category "' . $category->getName() . '"'));

        $this->_view->renderLayout();
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Catalog::categories');
    }
}
