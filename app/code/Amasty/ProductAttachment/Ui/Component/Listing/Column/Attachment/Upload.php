<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */

namespace Amasty\ProductAttachment\Ui\Component\Listing\Column\Attachment;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class Upload extends \Magento\Ui\Component\Listing\Columns\Column
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
            $storeId = $this->context->getRequestParam('store');

            foreach ($dataSource['data']['items'] as &$item) {
                $item[$fieldName . '_form_key'] = $this->getFormKey();
                $item[$fieldName . '_upload_max_size'] = $this->getMaxFileSize();
                $item[$fieldName . '_store_id'] = $storeId;
                $item[$fieldName . '_upload_url'] = $this->getUploadUrl();
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

    protected function getFormKey()
    {
        /**
         * @var \Magento\Framework\Data\Form\FormKey $formKeyInstance
         */
        $formKeyInstance = $this->objectManager->get('Magento\Framework\Data\Form\FormKey');

        return $formKeyInstance->getFormKey();
    }

    protected function getUploadUrl()
    {
        /**
         * @var \Magento\Backend\Model\Url $urlBuilder
         */
        $urlBuilder = $this->objectManager->get('Magento\Backend\Model\UrlFactory')->create();

        return $urlBuilder->getUrl('amfile/product/upload', ['_secure' => true]);
    }

    protected function getMaxFileSize() {
        /**
         * @var \Magento\Framework\File\Size $uploaderSize
         */
        $uploaderSize = $this->objectManager->create('Magento\Framework\File\Size');

        return $uploaderSize->getMaxFileSize();
    }
}
