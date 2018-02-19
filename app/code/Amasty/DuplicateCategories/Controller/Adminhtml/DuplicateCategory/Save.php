<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_DuplicateCategories
 */


namespace Amasty\DuplicateCategories\Controller\Adminhtml\DuplicateCategory;

class Save extends \Magento\Backend\App\Action
{
    protected $_categoryTree;
    protected $_storeManager;

    protected $_notForCopy = [];

    /**
     * Define template
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Catalog\Model\ResourceModel\Category\Tree $categoryTree,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->_categoryTree = $categoryTree;
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    public function execute()
    {
        $this->_initAction();

        $fromCategoryId = $this->getRequest()->getParam('id');
        $toParentId = $this->getRequest()->getParam('parent_category_id');
        if (!$toParentId) {
            $toParentId = $fromCategoryId;
        }

        $toCategoryId = $this->_duplicateCategory($fromCategoryId, $toParentId);

        $this->_handleSubcategories($fromCategoryId, $toCategoryId);

        $this->_redirect(
            'catalog/category/index',
            [
                '_current' => true,
                'id' => $toCategoryId
            ]
        );
    }

    protected function _handleSubcategories($fromCategoryId, $toCategoryId)
    {
        if ($this->getRequest()->getParam('include_subcats')) {
            $tree = $this->_categoryTree->load();
            $node = $tree->getNodeById($fromCategoryId);
            if ($childNodes = $node->getAllChildNodes()) {
                $childNodes = $this->_removeChildChild($childNodes, $fromCategoryId);
                //for necessary order of elements
                $childNodes = array_reverse($childNodes);
                foreach ($childNodes as $subcategory) {
                    $fromSubCategoryId = $subcategory->getId();
                    $toSubCategoryId = $this->_duplicateCategory($fromSubCategoryId, $toCategoryId);
                    $subNode = $tree->getNodeById($fromSubCategoryId);
                    if ($node->getAllChildNodes()) {
                        $this->_handleSubcategories($subNode->getId(), $toSubCategoryId);
                    }
                }
            }
        }
    }

    protected function _removeChildChild($childNodes, $fromCategoryId)
    {
        foreach ($childNodes as $key => $child) {
            if ($child->getParentId() != $fromCategoryId) {
                unset($childNodes[$key]);
            }
        }

        return $childNodes;
    }

    protected function _duplicateCategory($fromCategoryId, $toParentId)
    {
        //fix for unnecessary duplicating one child if ($fromCategoryId == $toParentId) part #1
        if (in_array($fromCategoryId, $this->_notForCopy)) {
            return $fromCategoryId;
        }

        /** @var \Magento\Catalog\Model\Category $fromCategory */
        $fromCategory = $this->_objectManager->create('Magento\Catalog\Model\Category');
        $fromCategory->load($fromCategoryId);

        /** @var \Magento\Catalog\Model\Category $toCategory */
        $toCategory = $this->_objectManager->create('Magento\Catalog\Model\Category');
        $toCategory->setData($fromCategory->getData());
        $toCategory->setId(null);

        /** @var \Magento\Catalog\Model\Category $toParentCategory */
        $toParentCategory = $this->_objectManager->create('Magento\Catalog\Model\Category')->load($toParentId);
        $toParentCategoryPath = $toParentCategory->getUrlPath();
        $toCategory->setUrlPath($toParentCategoryPath . '/' . $toCategory->getUrlKey());
        $toCategory->setUrlKey(false);

        $toCategory->setPosition($toCategory->getPosition() + 100);//+100 - fix for not changing positions of original categories

        $this->_save($toCategory);

        $this->_move($toCategory, $toParentId);

        //fix for unnecessary duplicating one child if ($fromCategoryId == $toParentId) part #2
        if ($fromCategoryId == $toParentId) {
            $this->_notForCopy[] = $toCategory->getId();
        }

        $this->_copyStoreData($fromCategory->getId(), $toCategory->getId());

        return $toCategory->getId();
    }

    /**
     * @param \Magento\Catalog\Model\Category $toCategory
     * @param int $toParentId
     */
    protected function _move($toCategory, $toParentId)
    {
        try {
            $toCategory->move($toParentId, null);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            if ($message == 'URL key for specified store already exists.') {
                $newUrlPath = $this->suggestUrlPath($toCategory->getUrlPath());
                $toCategory->setUrlPath($newUrlPath);
                $toCategory->save();
                $this->_move($toCategory, $toParentId);
            }
        }
    }

    /**
     * @param \Magento\Catalog\Model\Category $toCategory
     */
    protected function _save($toCategory)
    {
        try {
            $urlPath = $toCategory->getUrlPath();
            $toCategory->save();
        } catch (\Exception $e) {
            $message = $e->getMessage();
            if ($message == 'URL key for specified store already exists.') {
                $newUrlPath = $this->suggestUrlPath($urlPath);
                $toCategory->setUrlPath($newUrlPath);
                $toCategory->setUrlKey(false);
                $this->_save($toCategory);
            }
        }
    }

    public function suggestUrlPath($urlPath)
    {
        if (preg_match('@([^\d]+)(\d+)$@', $urlPath, $matches)) {
            if (isset($matches[2])) {
                $urlPath = substr($urlPath, 0, -strlen($matches[2]));
                $urlPath = $urlPath . ++$matches[2];
                return $urlPath;
            }
        }
        return $urlPath . '-1';
    }

    protected function _copyStoreData($fromCategoryId, $toCategoryId)
    {
        $stores = $this->_storeManager->getStores();
        if (!empty($stores)) {
            foreach ($stores as $store) {
                $this->_copyCategoryData($fromCategoryId, $toCategoryId, $store->getId());
            }
        }
        $this->_copyCategoryData($fromCategoryId, $toCategoryId, 0);
    }

    protected function _copyCategoryData($fromCategoryId, $toCategoryId, $storeId)
    {
        /** @var \Magento\Catalog\Model\Category $fromCategory */
        $fromCategory = $this->_objectManager->create('Magento\Catalog\Model\Category');
        $fromCategory->setStoreId($storeId);
        $fromCategory->load($fromCategoryId);

        /** @var \Magento\Catalog\Model\Category $toCategory */
        $toCategory = $this->_objectManager->create('Magento\Catalog\Model\Category');
        $toCategory->setStoreId($storeId);
        $toCategory->load($toCategoryId);

        $fromCategoryData = $fromCategory->getData();

        $unsetArray = ['is_anchor', 'path', 'position', 'url_path', 'level', 'entity_id', 'parent_id', 'created_at', 'updated_at'];
        foreach ($unsetArray as $field) {
            unset($fromCategoryData[$field]);
        }

        $toCategory->addData($fromCategoryData);

        if ($this->getRequest()->getParam('copy_products')) {
            $toCategory->setPostedProducts($fromCategory->getProductsPosition());
            $toCategory->setAffectedProductIds(array_keys($fromCategory->getProductsPosition()));
        }

        $this->_searchAndReplace($toCategory);

        $this->_save($toCategory);
    }

    /**
     * @param \Magento\Catalog\Model\Category $category
     */
    protected function _searchAndReplace($category)
    {
        $fieldsToReplaceIn = [
            'name',
            'description',
            'meta_title',
            'meta_keywords',
            'meta_description',
        ];

        $search = $this->getRequest()->getParam('search');
        $replace = $this->getRequest()->getParam('replace');
        if ($search && $replace) {
            foreach ($fieldsToReplaceIn as $field) {
                if (!is_null($category->getData($field))) {
                    $value = $category->getData($field);
                    if ($value) {
                        foreach ($search as $i => $searchEntity) {
                            if ($searchEntity && isset($replace[$i])) {
                                $value = str_replace($searchEntity, $replace[$i], $value);
                            }
                        }
                        $category->setData($field, $value);
                    }
                }
            }
        }
    }

    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Amasty_DuplicateCategories::amdupcat')->_addBreadcrumb(__('Duplicate Category'), __('Duplicate Category'));
        return $this;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magento_Catalog::categories');
    }
}
