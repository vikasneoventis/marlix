<?php
namespace Smartwave\Core\Controller\Adminhtml\Product;

use Magento\Catalog\Controller\Adminhtml\Product\Save;

class Saveproduct extends Save
{
	 /**
     * @var StoreManagerInterface
     */
    private $storeManager;

	/**
     * Save product action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $storeId = $this->getRequest()->getParam('store', 0);
        $store = $this->getStoreManager()->getStore($storeId);
        $this->getStoreManager()->setCurrentStore($store->getCode());
        $redirectBack = $this->getRequest()->getParam('back', false);
        $productId = $this->getRequest()->getParam('id');
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        $productAttributeSetId = $this->getRequest()->getParam('set');
        $productTypeId = $this->getRequest()->getParam('type');
        if ($data) {
            try {
                $product = $this->initializationHelper->initialize(
                    $this->productBuilder->build($this->getRequest())
                );
                $this->productTypeManager->processProduct($product);

                if (isset($data['product'][$product->getIdFieldName()])) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('Unable to save product'));
                }

                $originalSku = $product->getSku();
                $product->save();
                $this->handleImageRemoveError($data, $product->getId());
                $this->getCategoryLinkManagement()->assignProductToCategories(
                    $product->getSku(),
                    $product->getCategoryIds()
                );
                $productId = $product->getEntityId();
                $productAttributeSetId = $product->getAttributeSetId();
                $productTypeId = $product->getTypeId();

                $this->copyToStores($data, $productId);

                $this->messageManager->addSuccess(__('You saved the product.'));
                $this->getDataPersistor()->clear('catalog_product');
                if ($product->getSku() != $originalSku) {
                    $this->messageManager->addNotice(
                        __(
                            'SKU for product %1 has been changed to %2.',
                            $this->_objectManager->get('Magento\Framework\Escaper')->escapeHtml($product->getName()),
                            $this->_objectManager->get('Magento\Framework\Escaper')->escapeHtml($product->getSku())
                        )
                    );
                }
                $this->_eventManager->dispatch(
                    'controller_action_catalog_product_save_entity_after',
                    ['controller' => $this, 'product' => $product]
                );

                if ($redirectBack === 'duplicate') {
                    $newProduct = $this->productCopier->copy($product);
                    $this->messageManager->addSuccess(__('You duplicated the product.'));
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                $this->getDataPersistor()->set('catalog_product', $data);
                $redirectBack = $productId ? true : 'new';
            } catch (\Exception $e) {
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $this->messageManager->addError($e->getMessage());
                $this->getDataPersistor()->set('catalog_product', $data);
                $redirectBack = $productId ? true : 'new';
            }
        } else {
            $resultRedirect->setPath('catalog/*/', ['store' => $storeId]);
            $this->messageManager->addError('No data to save');
            return $resultRedirect;
        }

        if ($redirectBack === 'new') {
            $resultRedirect->setPath(
                'catalog/*/new',
                ['set' => $productAttributeSetId, 'type' => $productTypeId]
            );
        } elseif ($redirectBack === 'duplicate' && isset($newProduct)) {
            $resultRedirect->setPath(
                'catalog/*/edit',
                ['id' => $newProduct->getEntityId(), 'back' => null, '_current' => true]
            );
        } elseif ($redirectBack === 'prev') {
			$prevProduct = $this->getNextPrevProduct($productId, 'prev');
            if (is_object($prevProduct) && $prevProduct->getId()) {			
				$resultRedirect->setPath(
					'catalog/*/edit',
					['id' => $prevProduct->getId()]
				);
			} else {
				$this->messageManager->addNotice('No previous product to edit');
				$resultRedirect->setPath('catalog/*/', ['store' => $storeId]);
			}
        } elseif ($redirectBack === 'next') {
			$nextProduct = $this->getNextPrevProduct($productId, 'next');
			if (is_object($nextProduct) && $nextProduct->getId()) {			
				$resultRedirect->setPath(
					'catalog/*/edit',
					['id' => $nextProduct->getId()]
				);
			} else {
				$this->messageManager->addNotice('No next product to edit');
				$resultRedirect->setPath('catalog/*/', ['store' => $storeId]);
			}
        } elseif ($redirectBack) {
            $resultRedirect->setPath(
                'catalog/*/edit',
                ['id' => $productId, '_current' => true, 'set' => $productAttributeSetId]
            );
        } else {
            $resultRedirect->setPath('catalog/*/', ['store' => $storeId]);
        }
        return $resultRedirect;
    }
	
	private function getNextPrevProduct($productId, $back = ''){		

		$storeId = $this->getRequest()->getParam('store', 0);

		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$connection = $objectManager->create('\Magento\Framework\App\ResourceConnection');
		$productModel = $objectManager->create('Magento\Catalog\Model\Product');
		$productCollection = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
		$productListBookmark= $objectManager->create('Magento\Ui\Model\BookmarkManagement')->getByIdentifierNamespace('current','product_listing');
		$filterConfig = $productListBookmark->getConfig();
		$filters = $filterConfig['current']['filters']['applied'];
		$columns = $filterConfig['current']['columns'];
		unset($filters['placeholder']);		

		if ($productId) {
		    $collection = $productCollection->create()
		                                ->addAttributeToSelect('*')
										->addStoreFilter($this->getStoreManager()->getStore($storeId));
										//->addFieldToFilter('entity_id', array('in' => $productIds));
			$collection->joinField('qty',
				 $connection->getTableName('cataloginventory_stock_item'),
				 'qty',
				 'product_id=entity_id',
				 '{{table}}.stock_id=1',
				 'left'
			);
			foreach($filters as $filter_key => $filter_val){
				if (is_array($filter_val) && empty(array_diff(array_keys($filter_val), array('from','to')))) {
					if ($filter_key == 'entity_id' || $filter_key == 'price') {
						$collection->addFieldToFilter($filter_key, array('from' => $filter_val['from'],
					                                                     'to'=> $filter_val['to']));
					} else {
						$collection->addAttributeToFilter($filter_key, array('from' => $filter_val['from'],
					                                                      'to'=> $filter_val['to']));
					}
				}else{
					$collection->addAttributeToFilter($filter_key, array('eq' => $filter_val));
				}
		    }
			foreach($columns as $column => $column_val) {
			    if ($column_val['sorting']) {
					$collection->addAttributeToSort($column, $column_val['sorting']);
				}				
		    }
			$collection->load();
			$product_Ids = array();
			//$product_Ids = $collection->getAllIds();
			foreach ($collection as $collectProduct){
				 $product_Ids[] = $collectProduct->getID();
			}
			$product_Ids = array_unique($product_Ids);

			$_pos = array_search($productId, $product_Ids);
			$_next = $_pos+1;
			$_prev = $_pos-1;

			$pId = 0;
			if ($back == 'next') {
			    if(isset($product_Ids[$_next])){
					$pId = $product_Ids[$_next];
				}
			} else if($back == 'prev') {
			    if(isset($product_Ids[$_prev])) {
					$pId = $product_Ids[$_prev];
				}
			}

		    if ($pId) {
			    $product = $productModel->load($pId);
			    return $product;
		    }
		}
		return false;
	}

	/**
     * Notify customer when image was not deleted in specific case.
     * TODO: temporary workaround must be eliminated in MAGETWO-45306
     *
     * @param array $postData
     * @param int $productId
     * @return void
     */
    private function handleImageRemoveError($postData, $productId)
    {
        if (isset($postData['product']['media_gallery']['images'])) {
            $removedImagesAmount = 0;
            foreach ($postData['product']['media_gallery']['images'] as $image) {
                if (!empty($image['removed'])) {
                    $removedImagesAmount++;
                }
            }
            if ($removedImagesAmount) {
                $expectedImagesAmount = count($postData['product']['media_gallery']['images']) - $removedImagesAmount;
                $product = $this->productRepository->getById($productId);
                if ($expectedImagesAmount != count($product->getMediaGallery('images'))) {
                    $this->messageManager->addNotice(
                        __('The image cannot be removed as it has been assigned to the other image role')
                    );
                }
            }
        }
    }

	/**
     * @return \Magento\Catalog\Api\CategoryLinkManagementInterface
     */
    private function getCategoryLinkManagement()
    {
        if (null === $this->categoryLinkManagement) {
            $this->categoryLinkManagement = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Magento\Catalog\Api\CategoryLinkManagementInterface');
        }
        return $this->categoryLinkManagement;
    }

	/**
     * @return StoreManagerInterface
     * @deprecated
     */
    private function getStoreManager()
    {
        if (null === $this->storeManager) {
            $this->storeManager = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Magento\Store\Model\StoreManagerInterface');
        }
        return $this->storeManager;
    }
}