<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */


namespace Amasty\ProductAttachment\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Downloadable\Model\Source\TypeUpload;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Ui\Component\DynamicRows;
use Magento\Ui\Component\Form;
use Magento\Ui\Component\Container;
use Magento\Framework\UrlInterface;

class AttachmentsPanel extends AbstractModifier
{
    /**
     * @var ArrayManager
     */
    protected $arrayManager;
    /**
     * @var \Amasty\ProductAttachment\Helper\Config
     */
    protected $attachConfig;
    /**
     * @var LocatorInterface
     */
    protected $locator;
    /**
     * @var \Amasty\ProductAttachment\Model\Config\Source\CustomerGroup
     */
    protected $groupSource;
    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $yesnoSource;
    /**
     * @var TypeUpload
     */
    protected $typeUpload;
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;
    /**
     * @var \Amasty\ProductAttachment\Model\File
     */
    protected $fileModel;
    /**
     * @var \Magento\Downloadable\Helper\File
     */
    protected $downloadableFile;
    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @param ArrayManager                                                $arrayManager
     * @param LocatorInterface                                            $locator
     * @param \Amasty\ProductAttachment\Model\Config\Source\CustomerGroup $groupSource
     * @param \Magento\Config\Model\Config\Source\Yesno                   $yesnoSource
     * @param TypeUpload                                                  $typeUpload
     * @param UrlInterface                                                $urlBuilder
     * @param \Amasty\ProductAttachment\Model\File                        $fileModel
     * @param \Magento\Downloadable\Helper\File                           $downloadableFile
     * @param \Magento\Framework\Escaper                                  $escaper
     * @param \Amasty\ProductAttachment\Helper\Config                     $attachConfig
     */
    public function __construct(
        ArrayManager $arrayManager,
        LocatorInterface $locator,
        \Amasty\ProductAttachment\Model\Config\Source\CustomerGroup $groupSource,
        \Magento\Config\Model\Config\Source\Yesno $yesnoSource,
        TypeUpload $typeUpload,
        UrlInterface $urlBuilder,
        \Amasty\ProductAttachment\Model\File $fileModel,
        \Magento\Downloadable\Helper\File $downloadableFile,
        \Magento\Framework\Escaper $escaper,
        \Amasty\ProductAttachment\Helper\Config $attachConfig
    ) {
        $this->arrayManager = $arrayManager;
        $this->attachConfig = $attachConfig;
        $this->locator = $locator;
        $this->groupSource = $groupSource;
        $this->yesnoSource = $yesnoSource;
        $this->typeUpload = $typeUpload;
        $this->urlBuilder = $urlBuilder;
        $this->fileModel = $fileModel;
        $this->downloadableFile = $downloadableFile;
        $this->escaper = $escaper;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        $model = $this->locator->getProduct();

        $data[$model->getId()]['amasty_product_attachments']['attachments'] = $this->getLinkData();

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        $panelConfig['arguments']['data']['config'] = [
            'componentType' => Form\Fieldset::NAME,
            'label' => $this->attachConfig->getBlockLabel() ? $this->attachConfig->getBlockLabel() : __('Product Attachments'),
            'additionalClasses' => 'admin__fieldset-section',
            'collapsible' => true,
            'opened' => false,
            'dataScope' => 'data',
        ];

        // @codingStandardsIgnoreStart
        $information['arguments']['data']['config'] = [
            'componentType' => Container::NAME,
            'component' => 'Magento_Ui/js/form/components/html',
            'additionalClasses' => 'admin__fieldset-note',
            'content' => __('Alphanumeric, dash and underscore characters are recommended for filenames. Improper characters are replaced with \'_\'.'),
        ];
        // @codingStandardsIgnoreEnd

        $panelConfig = $this->arrayManager->set(
            'children',
            $panelConfig,
            [
                'attachments' => $this->getDynamicRows(),
                'information_links' => $information,
            ]
        );


        return $this->arrayManager->set('amasty_product_attachments', $meta, $panelConfig);
    }

    /**
     * @return array
     */
    protected function getDynamicRows()
    {
        $dynamicRows['arguments']['data']['config'] = [
            'addButtonLabel' => __('Add New File'),
            'componentType' => DynamicRows::NAME,
            'itemTemplate' => 'record',
            'renderDefaultRecord' => false,
            'columnsHeader' => true,
            'additionalClasses' => 'admin__field-wide',
            'dataScope' => 'amasty_product_attachments',
            'deleteProperty' => 'is_delete',
            'deleteValue' => '1',
        ];

        return $this->arrayManager->set('children/record', $dynamicRows, $this->getRecord());
    }

    /**
     * @return array
     */
    protected function getRecord()
    {
        $record['arguments']['data']['config'] = [
            'componentType' => Container::NAME,
            'isTemplate' => true,
            'is_collection' => true,
            'component' => 'Magento_Ui/js/dynamic-rows/record',
            'dataScope' => '',
        ];
        $recordPosition['arguments']['data']['config'] = [
            'componentType' => Form\Field::NAME,
            'formElement' => Form\Element\Input::NAME,
            'dataType' => Form\Element\DataType\Number::NAME,
            'dataScope' => 'position',
            'visible' => false,
        ];
        $recordActionDelete['arguments']['data']['config'] = [
            'label' => null,
            'componentType' => 'actionDelete',
            'fit' => true,
        ];

        return $this->arrayManager->set(
            'children',
            $record,
            [
                'container_label' => $this->getLabelColumn(),
                'container_file_name' => $this->getFileNameColumn(),
                'container_file' => $this->getFileColumn(),
                'container_customer_group' => $this->getCustomerGroupColumn(),
                'container_show_for_ordered' => $this->getShowOrderedColumn(),
                'container_is_visible' => $this->getIsVisibleColumn(),
                'position' => $recordPosition,
                'action_delete' => $recordActionDelete,
            ]
        );
    }

    /**
     * @return array
     */
    protected function getLabelColumn()
    {
        $labelContainer['arguments']['data']['config'] = [
            'componentType' => Container::NAME,
            'formElement' => Container::NAME,
            'component' => 'Magento_Ui/js/form/components/group',
            'label' => __('Label'),
            'dataScope' => '',
        ];
        $labelField['arguments']['data']['config'] = [
            'formElement' => Form\Element\Input::NAME,
            'componentType' => Form\Field::NAME,
            'dataType' => Form\Element\DataType\Text::NAME,
            'dataScope' => 'label',
            'validation' => [
                'required-entry' => true,
            ],
        ];

        return $this->arrayManager->set('children/label', $labelContainer, $labelField);
    }

    /**
     * @return array
     */
    protected function getFileNameColumn()
    {
        $fileNameContainer['arguments']['data']['config'] = [
            'componentType' => Container::NAME,
            'formElement' => Container::NAME,
            'component' => 'Magento_Ui/js/form/components/group',
            'label' => __('File Name'),
            'dataScope' => '',
        ];
        $fileNameField['arguments']['data']['config'] = [
            'formElement' => Form\Element\Input::NAME,
            'componentType' => Form\Field::NAME,
            'dataType' => Form\Element\DataType\Text::NAME,
            'dataScope' => 'file_name',
            'validation' => [
                'required-entry' => true,
            ],
        ];

        return $this->arrayManager->set('children/file_name', $fileNameContainer, $fileNameField);
    }

    /**
     * @return array
     */
    protected function getFileColumn()
    {
        $fileContainer['arguments']['data']['config'] = [
            'componentType' => Container::NAME,
            'formElement' => Container::NAME,
            'component' => 'Magento_Ui/js/form/components/group',
            'label' => __('Attach File or Enter Link'),
            'dataScope' => '',
        ];
        $fileTypeField['arguments']['data']['config'] = [
            'formElement' => Form\Element\Select::NAME,
            'componentType' => Form\Field::NAME,
            'component' => 'Magento_Downloadable/js/components/upload-type-handler',
            'dataType' => Form\Element\DataType\Text::NAME,
            'dataScope' => 'file_type',
            'options' => $this->typeUpload->toOptionArray(),
            'typeFile' => 'file_upload',
            'typeUrl' => 'file_url',
        ];
        $fileLinkUrl['arguments']['data']['config'] = [
            'formElement' => Form\Element\Input::NAME,
            'componentType' => Form\Field::NAME,
            'dataType' => Form\Element\DataType\Text::NAME,
            'dataScope' => 'file_url',
            'placeholder' => 'URL',
            'validation' => [
                'required-entry' => true,
                'validate-url' => true,
            ],
        ];
        $fileUploader['arguments']['data']['config'] = [
            'formElement' => 'fileUploader',
            'componentType' => 'fileUploader',
            'component' => 'Magento_Downloadable/js/components/file-uploader',
            'elementTmpl' => 'Magento_Downloadable/components/file-uploader',
            'fileInputName' => 'links',
            'uploaderConfig' => [
                'url' => $this->urlBuilder->addSessionParam()->getUrl(
                    'amfile/file/upload',
                    ['type' => 'links', '_secure' => true]
                ),
            ],
            'dataScope' => 'file',
            'validation' => [
                'required-entry' => true,
            ],
        ];

        return $this->arrayManager->set(
            'children',
            $fileContainer,
            [
                'file_type' => $fileTypeField,
                'file_url' => $fileLinkUrl,
                'file_upload' => $fileUploader
            ]
        );
    }

    /**
     * @return array
     */
    protected function getCustomerGroupColumn()
    {
        $labelContainer['arguments']['data']['config'] = [
            'componentType' => Container::NAME,
            'formElement' => Container::NAME,
            'component' => 'Magento_Ui/js/form/components/group',
            'label' => __('Customer Group'),
            'dataScope' => '',
        ];
        $labelField['arguments']['data']['config'] = [
            'formElement' => Form\Element\MultiSelect::NAME,
            'componentType' => Form\Field::NAME,
            'dataType' => Form\Element\DataType\Number::NAME,
            'dataScope' => 'customer_group',
            'options' => $this->groupSource->toOptionArray()
        ];

        return $this->arrayManager->set('children/customer_group', $labelContainer, $labelField);
    }

    /**
     * @return array
     */
    protected function getShowOrderedColumn()
    {
        $labelContainer['arguments']['data']['config'] = [
            'componentType' => Container::NAME,
            'formElement' => Container::NAME,
            'component' => 'Magento_Ui/js/form/components/group',
            'label' => __('Show only if a Product has been Ordered'),
            'dataScope' => '',
        ];
        $labelField['arguments']['data']['config'] = [
            'formElement' => Form\Element\Select::NAME,
            'componentType' => Form\Field::NAME,
            'dataType' => Form\Element\DataType\Text::NAME,
            'dataScope' => 'show_for_ordered',
            'options' => $this->yesnoSource->toOptionArray()
        ];

        return $this->arrayManager->set('children/show_for_ordered', $labelContainer, $labelField);
    }

    /**
     * @return array
     */
    protected function getIsVisibleColumn()
    {
        $labelContainer['arguments']['data']['config'] = [
            'componentType' => Container::NAME,
            'formElement' => Container::NAME,
            'component' => 'Magento_Ui/js/form/components/group',
            'label' => __('Visible'),
            'dataScope' => '',
        ];
        $labelField['arguments']['data']['config'] = [
            'formElement' => Form\Element\Select::NAME,
            'componentType' => Form\Field::NAME,
            'dataType' => Form\Element\DataType\Text::NAME,
            'dataScope' => 'is_visible',
            'options' => $this->yesnoSource->toOptionArray()
        ];

        return $this->arrayManager->set('children/is_visible', $labelContainer, $labelField);
    }

    /**
     * Return array of links
     *
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getLinkData()
    {
        $linkArr = [];

        $product = $this->locator->getProduct();

        $productId = $product->getId();
        $storeId = $product->getStoreId();

        /**
         * @var \Amasty\ProductAttachment\Model\ResourceModel\File\Collection $fileCollection
         */
        $files = $this->fileModel->getCollection();
        $files->getFilesAdminByProductId($productId,$storeId);
        $fileHelper = $this->downloadableFile;
        /**
         * TODO: Magento bug, customer group id 0 don't selected in form by default
         */
        foreach ($files as $item) {
            /**
             * @var \Amasty\ProductAttachment\Model\File $item
             */
            $tmpLinkItem = [
                'id'                          => $item->getFileId(),
                'label'                       => $this->escaper->escapeHtml(
                    $item->getLabel()
                ),
                'use_default_label'           => $item->getLabelIsDefault() ? '1' : '0',
                'file_name'                   => $this->escaper->escapeHtml(
                    $item->getFileName()
                ),
                'customer_group'              => $item->getCustomerGroups(),
                'use_default_customer_group'  => $item->getCustomerGroupIsDefault() ? '1' : '0',
                'file_type'                   => $item->getFileType(),
                'file_url'                    => $item->getFileUrl(),
                'show_for_ordered'            => $item->getShowForOrdered(),
                'use_default_show_for_ordered'=> $item->getShowForOrderedIsDefault() ? '1' : '0',
                'is_visible'                  => $item->getIsVisible(),
                'use_default_is_visible'      => $item->getIsVisibleIsDefault() ? '1' : '0',
                'position'                    => $item->getPosition(),
            ];

            $linkFile = $item->getFilePath();
            if ($linkFile) {
                $file = $fileHelper->getFilePath($this->fileModel->getBasePath(), $linkFile);

                $fileExist = $fileHelper->ensureFileInFilesystem($file);

                if ($fileExist) {
                    $tmpLinkItem['file'] = [
                        [
                            'file' => $linkFile,
                            'name' => $item->getFileName(),
                            'size' => $fileHelper->getFileSize($file),
                            'status' => 'old',
                            'url'  => $this->getDownloadUrl($item->getFileId())
                        ],
                    ];
                }
            }

            $linkArr[] = $tmpLinkItem;
        }
        return $linkArr;
    }

    /**
     * @param \Amasty\ProductAttachment\Model\File
     *
     * @return string
     */
    public function getDownloadUrl($fileId)
    {
        return $this->fileModel->getDownloadUrlBackend($fileId);
    }
}
