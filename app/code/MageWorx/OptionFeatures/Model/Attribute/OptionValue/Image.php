<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionFeatures\Model\Attribute\OptionValue;

use Magento\Framework\App\ResourceConnection;
use MageWorx\OptionFeatures\Helper\Data as Helper;
use MageWorx\OptionBase\Model\AttributeInterface;
use MageWorx\OptionFeatures\Model\Image as ImageModel;
use MageWorx\OptionFeatures\Model\ResourceModel\Image\Collection as ImageCollection;
use MageWorx\OptionFeatures\Model\ImageFactory;

class Image implements AttributeInterface
{
    /**
     * @var Helper
     */
    protected $helper;

    /**
     * @var ImageFactory
     */
    protected $imageFactory;

    /**
     * @var ImageCollection
     */
    protected $imageCollection;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    /**
     * @var mixed
     */
    protected $entity;

    /**
     * @param ResourceConnection $resource
     * @param ImageFactory $imageFactory
     * @param ImageCollection $imageCollection
     * @param Helper $helper
     */
    public function __construct(
        ResourceConnection $resource,
        ImageFactory $imageFactory,
        ImageCollection $imageCollection,
        Helper $helper
    ) {
        $this->resource = $resource;
        $this->helper = $helper;
        $this->imageFactory = $imageFactory;
        $this->imageCollection = $imageCollection;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return Helper::KEY_IMAGE;
    }

    /**
     * {@inheritdoc}
     */
    public function getTableName()
    {
        $map = [
            'product' => ImageModel::TABLE_NAME,
            'group' => ImageModel::OPTIONTEMPLATES_TABLE_NAME
        ];
        return $map[$this->entity->getType()];
    }

    /**
     * {@inheritdoc}
     */
    public function clearData()
    {
        return;
    }

    /**
     * {@inheritdoc}
     */
    public function applyData($entity, $options)
    {
        $this->entity = $entity;

        $images = [];
        foreach ($options as $option) {
            if (empty($option['values'])) {
                continue;
            }
            foreach ($option['values'] as $value) {
                if (!isset($value[Helper::KEY_IMAGE])) {
                    continue;
                }
                $data = json_decode($value[Helper::KEY_IMAGE], true);
                if (json_last_error() == JSON_ERROR_NONE) {
                    $images[$value['mageworx_option_type_id']] = $data;
                } else {
                    parse_str($value[Helper::KEY_IMAGE], $images[$value['mageworx_option_type_id']]);
                }
            }
        }

        $this->saveImage($images);
    }

    /**
     * Save images
     *
     * @param $items
     * @return void
     */
    protected function saveImage($items)
    {
        foreach ($items as $imageKey => $images) {
            $connection = $this->resource->getConnection();
            $tableName = $this->resource->getTableName($this->getTableName($this->entity->getType()));

            if (isset($images['optionfeatures']['media_gallery']['images'])) {
                $this->deleteOldImages($imageKey);

                foreach ($images['optionfeatures']['media_gallery']['images'] as $imageItem) {
                    if (!empty($imageItem['removed'])) {
                        continue;
                    }
                    $imageText = $this->removeSpecialChars($imageItem['label']);
                    $data = [
                        'mageworx_option_type_id' => $imageKey,
                        'sort_order' => $imageItem['position'],
                        'title_text' => htmlspecialchars($imageText, ENT_COMPAT, 'UTF-8', false),
                        'media_type' => $imageItem['custom_media_type'],
                        'color' => $imageItem['color'],
                        'value' => $imageItem['file'],
                        ImageModel::COLUMN_HIDE_IN_GALLERY => $imageItem[ImageModel::COLUMN_HIDE_IN_GALLERY],
                    ];
                    foreach ($this->helper->getImageAttributes() as $attributeCode => $attributeLabel) {
                        if (isset($images[$attributeCode])
                            && $imageItem['file']
                            && $images[$attributeCode] == $imageItem['file']
                        ) {
                            $data[$attributeCode] = 1;
                        }
                    }
                    $connection->insert($tableName, $data);
                }
            } elseif (!empty($images) && !isset($images['base_image'])) {
                $this->deleteOldImages($imageKey);

                foreach ($images as $imageItem) {
                    if (!empty($imageItem['removed'])) {
                        continue;
                    }
                    $imageText = $this->removeSpecialChars($imageItem['title_text']);
                    $data = [
                        'mageworx_option_type_id' => $imageKey,
                        'sort_order' => $imageItem['sort_order'],
                        'title_text' => htmlspecialchars($imageText, ENT_COMPAT, 'UTF-8', false),
                        'media_type' => $imageItem['custom_media_type'],
                        'color' => $imageItem['color'],
                        'value' => $imageItem['value'],
                        ImageModel::COLUMN_HIDE_IN_GALLERY => $imageItem[ImageModel::COLUMN_HIDE_IN_GALLERY],
                    ];
                    foreach ($this->helper->getImageAttributes() as $attributeCode => $attributeLabel) {
                        $data[$attributeCode] = $imageItem[$attributeCode];
                    }
                    $connection->insert($tableName, $data);
                }
            }
        }
    }

    /**
     * Delete old option value images
     *
     * @param $mageworxOptionTypeId
     * @return void
     */
    protected function deleteOldImages($mageworxOptionTypeId)
    {
        $connection = $this->resource->getConnection();
        $tableName = $this->resource->getTableName($this->getTableName($this->entity->getType()));
        $connection->delete(
            $tableName,
            "mageworx_option_type_id = '".$mageworxOptionTypeId."'"
        );
    }

    /**
     * {@inheritdoc}
     */
    public function prepareData($object)
    {
        $imagesData = [];
        $tooltipImage = '';
        if (!empty($object->getTooltipImage())) {
            $tooltipImage = $this->helper->getThumbImageUrl(
                $object->getTooltipImage(),
                Helper::IMAGE_MEDIA_ATTRIBUTE_TOOLTIP_IMAGE
            );
        };
        $imagesData['tooltip_image'] = $tooltipImage;
        return $imagesData;
    }

    /**
     * Remove backslashes and new line symbols from string
     *
     * @param $string string
     * @return string
     */
    public function removeSpecialChars($string)
    {
        $string = str_replace(["\n","\r"], '', $string);
        return stripslashes($string);
    }
}
