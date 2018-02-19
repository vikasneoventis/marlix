<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageWorx\SeoXTemplates\Controller\Adminhtml\Templateproduct;

use MageWorx\SeoXTemplates\Controller\Adminhtml\Templateproduct as TemplateController;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use MageWorx\SeoXTemplates\Model\Template\ProductFactory as TemplateProductFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use MageWorx\SeoXTemplates\Model\DbWriterProductFactory;
use MageWorx\SeoXTemplates\Helper\Data as HelperData;
use MageWorx\SeoXTemplates\Helper\Store as HelperStore;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

class Apply extends TemplateController
{

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var DbWriterProductFactory
     */
    protected $dbWriterProductFactory;

    /**
     *
     * @var PageFactory
     */
    protected $resultPageFactor;

    /**
     *
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var HelperStore
     */
    protected $helperStore;

    /**
     *
     * @param Registry $registry
     * @param PageFactory $resultPageFactory
     * @param TemplateProductFactory $templateProductFactory
     * @param DateTime $date
     * @param DbWriterProductFactory $dbWriterProductFactory
     * @param HelperData $helperData
     * @param HelperStore $helperStore
     * @param Context $context
     */
    public function __construct(
        Registry $registry,
        PageFactory $resultPageFactory,
        TemplateProductFactory $templateProductFactory,
        DateTime $date,
        DbWriterProductFactory $dbWriterProductFactory,
        HelperData $helperData,
        HelperStore $helperStore,
        Context $context
    ) {
    
        $this->date = $date;
        $this->dbWriterProductFactory = $dbWriterProductFactory;
        $this->helperData = $helperData;
        $this->helperStore = $helperStore;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($registry, $templateProductFactory, $context);
    }

    /**
     *
     * @param \MageWorx\SeoXTemplates\Model\Template\Product $template $template
     * @param int|null $nestedStoreId
     */
    protected function writeTemplateForStore($template, $nestedStoreId = null)
    {
        $from      = 0;
        $limit     = $this->helperData->getTemplateLimitForCurrentStore();
        $dbWriter = $this->dbWriterProductFactory->create($template->getTypeId());

        $productCollection = $template->getItemCollectionForApply($from, $limit, null, $nestedStoreId);

        while (is_object($productCollection) && $productCollection->count() > 0) {
            $dbWriter->write($productCollection, $template, $nestedStoreId);
            $from += $limit;
            $productCollection = $template->getItemCollectionForApply($from, $limit, null, $nestedStoreId);
        }
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('template_id');

        if ($id) {
            $name = "";
            try {
                /** @var \MageWorx\SeoXTemplates\Model\Template\Product $template */
                $template = $this->templateProductFactory->create();
                $template->load($id);
                $template->setDateApplyStart($this->date->gmtDate());
                $name = $template->getName();

                if ($template->getStoreId() == 0) {
                    $storeIds = array_keys($this->helperStore->getActiveStores());
                    foreach ($storeIds as $storeId) {
                        $this->writeTemplateForStore($template, $storeId);
                    }
                } else {
                    $this->writeTemplateForStore($template);
                }

                $this->messageManager->addSuccess(__('Template "%1" has been applied.', $name));
                $this->_eventManager->dispatch(
                    'adminhtml_mageworx_seoxtemplates_template_product_on_apply',
                    ['name' => $name, 'status' => 'success']
                );
                $resultRedirect->setPath('mageworx_seoxtemplates/*/');

                $template->setDateApplyFinish($this->date->gmtDate());
                $template->save();

                return $resultRedirect;
            } catch (\Exception $e) {
                $this->_eventManager->dispatch(
                    'adminhtml_mageworx_seoxtemplates_template_product_on_apply',
                    ['name' => $name, 'status' => 'fail']
                );
                $this->messageManager->addError($e->getMessage());
                $resultRedirect->setPath('mageworx_seoxtemplates/*/index', ['template_id' => $id]);
                return $resultRedirect;
            }
        }
        $this->messageManager->addError(__('We can\'t find a product template to apply.'));
        $resultRedirect->setPath('mageworx_seoxtemplates/*/');
        return $resultRedirect;
    }
}
