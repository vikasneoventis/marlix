<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\SeoXTemplates\Ui\Component\Listing\Column;

abstract class TemplateActions extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * URL builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @return \Magento\Framework\Phrase|string
     */
    abstract protected function getApplyUrlPath();

    /**
     * @return \Magento\Framework\Phrase|string
     */
    abstract protected function getTestApplyUrlPath();

    /**
     * @return \Magento\Framework\Phrase|string
     */
    abstract protected function getEditUrlPath();

    /**
     * @return \Magento\Framework\Phrase|string
     */
    abstract protected function getDeleteUrlPath();

    /**
     * @return \Magento\Framework\Phrase|string
     */
    abstract protected function getDeleteMessage();

    /**
     * @return \Magento\Framework\Phrase|string
     */
    abstract protected function getApplyMessage();

    /**
     * Constructor
     *
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {

        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if (isset($item['template_id'])) {
                    $item[$this->getData('name')] = [
                        'test_apply' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_TEST_APPLY,
                                [
                                    'template_id' => $item['template_id']
                                ]
                            ),
                            'label' => __('Test Apply')
                        ],
                        'apply' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_APPLY,
                                [
                                    'template_id' => $item['template_id']
                                ]
                            ),
                            'label' => __('Apply'),
                            'confirm' => [
                                'title' => __('Apply "${ $.$data.name }"'),
                                'message' => $this->getApplyMessage()
                            ]
                        ],
                        'edit' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_EDIT,
                                [
                                    'template_id' => $item['template_id']
                                ]
                            ),
                            'label' => __('Edit')
                        ],
                        'delete' => [
                            'href' => $this->urlBuilder->getUrl(
                                static::URL_PATH_DELETE,
                                [
                                    'template_id' => $item['template_id']
                                ]
                            ),
                            'label' => __('Delete'),
                            'confirm' => [
                                'title' => __('Delete "${ $.$data.name }"'),
                                'message' => $this->getDeleteMessage()
                            ]
                        ]
                    ];
                }
            }
        }
        return $dataSource;
    }
}
