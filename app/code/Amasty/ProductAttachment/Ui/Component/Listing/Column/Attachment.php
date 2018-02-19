<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */

namespace Amasty\ProductAttachment\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class Attachment extends \Magento\Ui\Component\Listing\Columns\Column
{
    const NAME = 'attachment';

    const ALT_FIELD = 'name';

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param ContextInterface                          $context
     * @param UiComponentFactory                        $uiComponentFactory
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array                                     $components
     * @param array                                     $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->objectManager = $objectManager;
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            $productIds = array_column($dataSource['data']['items'], 'entity_id');
            $storeId = $this->context->getRequestParam('store');
            $files = $this->getFileModel()->getFilesProductGrid($productIds, $storeId);
            foreach ($dataSource['data']['items'] as &$item) {
                $product = new \Magento\Framework\DataObject($item);
                $item[$fieldName . '_alt'] = $this->getAlt($item) . ' Attachment';
                $item[$fieldName . '_list'] =
                    array_key_exists($product->getEntityId(), $files)
                        ? $files[$product->getEntityId()] : [];
            }
        }

        return $dataSource;
    }

    /**
     * @param array $row
     *
     * @return null|string
     */
    protected function getAlt($row)
    {
        $altField = $this->getData('config/altField') ?: self::ALT_FIELD;
        return isset($row[$altField]) ? $row[$altField] : null;
    }

    /**
     * @return \Amasty\ProductAttachment\Model\File
     */
    protected function getFileModel()
    {
        return $this->objectManager->get('Amasty\ProductAttachment\Model\File');
    }
}
