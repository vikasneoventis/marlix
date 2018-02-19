<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */

/**
 * Copyright © 2016 Amasty. All rights reserved.
 */

namespace Amasty\ProductAttachment\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;

class FileSize extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (!empty($item[$this->getData('name')])) {
                    $item[$this->getData('name')] = $this->formatFileSize($item[$this->getData('name')]);
                } else {
                    $item[$this->getData('name')] = '';
                }
            }
        }

        return $dataSource;
    }

    protected function formatFileSize($size){
        if($size >= 1073741824){
            $fileSize = round($size/1024/1024/1024,1) . 'GB';
        }elseif($size >= 1048576){
            $fileSize = round($size/1024/1024,1) . 'MB';
        }elseif($size >= 1024){
            $fileSize = round($size/1024,1) . 'KB';
        }else{
            $fileSize = $size . ' bytes';
        }
        return $fileSize;
    }
}
