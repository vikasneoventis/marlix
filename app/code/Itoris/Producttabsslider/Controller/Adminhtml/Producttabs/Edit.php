<?php
/**
 * ITORIS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the ITORIS's Magento Extensions License Agreement
 * which is available through the world-wide-web at this URL:
 * http://www.itoris.com/magento-extensions-license.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to sales@itoris.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extensions to newer
 * versions in the future. If you wish to customize the extension for your
 * needs please refer to the license agreement or contact sales@itoris.com for more information.
 *
 * @category   ITORIS
 * @package    ITORIS_M2_PRODUCT_TABS
 * @copyright  Copyright (c) 2016 ITORIS INC. (http://www.itoris.com)
 * @license    http://www.itoris.com/magento-extensions-license.html  Commercial License
 */

namespace Itoris\Producttabsslider\Controller\Adminhtml\Producttabs;

use Magento\Backend\App\Action;

class Edit extends \Magento\Backend\App\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->_coreRegistry = $registry;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Itoris_Producttabsslider::product_tabs');
    }

    /**
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Itoris_Producttabsslider::product_tabs')
            ->addBreadcrumb(__('Tabs'), __('Tabs'))
            ->addBreadcrumb(__('Manage Products Tab'), __('Manage Products Tab'));
        return $resultPage;
    }

    /**
     * Edit Blog post
     *
     * @return \Magento\Backend\Model\View\Result\Page|\Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $prodId = $this->getRequest()->getParam('prod_id');
        $id= $this->getRequest()->getParam('id');
        $model = $this->_objectManager->create('Itoris\Producttabsslider\Model\ProductTabs');
        if($prodId!=null) {
            if ($id) {
                $model->load($id);
                if (!$model->getId()) {
                    $this->messageManager->addError(__('This tab does not exist.'));
                    $resultRedirect = $this->resultRedirectFactory->create();

                    return $resultRedirect->setPath('*/*/');
                }
            }
            $this->_coreRegistry->register('itoris_producttabsslider', $model);

            /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
            $resultPage = $this->_initAction();
            $resultPage->addBreadcrumb(
                $id ? __('Edit Tab') : __('New Tab'),
                $id ? __('Edit Tab') : __('New Tab')
            );
            $resultPage->getConfig()->getTitle()->prepend(__('Tab'));
            $resultPage->getConfig()->getTitle()
                ->prepend($model->getId() ? $model->getTitle() : __('New Tab'));
            /*if ($prodId == null)
                $resultPage->getLayout()->unsetElement('category.store.switcher');*/
            return $resultPage;
        }else{
            if ($id) {
                $model->load($id);
                if (!$model->getId()) {
                    $this->messageManager->addError(__('This tab does not exist.'));
                    $resultRedirect = $this->resultRedirectFactory->create();

                    return $resultRedirect->setPath('*/*/');
                }
            }
            $this->_coreRegistry->register('itoris_producttabsslider', $model);

            /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
            $resultPage = $this->_initAction();
            $resultPage->addBreadcrumb(
                $id ? __('Edit Tab') : __('New Tab'),
                $id ? __('Edit Tab') : __('New Tab')
            );
            $resultPage->getConfig()->getTitle()->prepend(__('Tab'));
            $resultPage->getConfig()->getTitle()
                ->prepend($model->getId() ? $model->getTitle() : __('New Tab'));
            return $resultPage;
        }
    }
}