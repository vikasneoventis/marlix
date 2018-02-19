<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */

namespace Amasty\ProductAttachment\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponentFactory;

class ViewAction extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * Constructor
     *
     * @param array $components
     * @param ContextInterface $context
     * @param array $data
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        UiComponentFactory $uiComponentFactory,
        ContextInterface $context,
        UrlInterface $urlBuilder,
        array $data = [],
        array $components = []
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
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['id'])) {
                    $viewUrlPath = $this->getData('config/viewUrlPath') ?: '#';
                    $urlEntityParamName = $this->getData('config/urlEntityParamName') ?: 'entity_id';
                    $item[$this->getData('name')] = [
                        'view' => [
                            'label' => __('Edit'),
                            'href'  => $this->urlBuilder->getUrl(
                                $viewUrlPath,
                                [
                                    $urlEntityParamName => $item['id']
                                ]
                            ),
                        ]
                    ];
                }
            }
        }

        return $dataSource;
    }
}
