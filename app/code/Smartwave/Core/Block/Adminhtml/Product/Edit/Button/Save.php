<?php 
namespace Smartwave\Core\Block\Adminhtml\Product\Edit\Button;
//use Magento\Catalog\Block\Adminhtml\Product\Edit\Button\Save;
class Save extends \Magento\Catalog\Block\Adminhtml\Product\Edit\Button\Save
{
    /**
     * Retrieve options
     *
     * @return array
     */
	protected function getOptions()
    {
        $options = parent::getOptions();
		$options[] = [
            'id_hard' => 'save_and_prev',
            'label' => __('Save & Previous'),
            'data_attribute' => [
                'mage-init' => [
                    'buttonAdapter' => [
                        'actions' => [
                            [
                                'targetName' => 'product_form.product_form',
                                'actionName' => 'save',
                                'params' => [
                                    true,
                                    [
                                        'back' => 'prev'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
        ];
		$options[] = [
            'id_hard' => 'save_and_next',
            'label' => __('Save & Next'),
            'data_attribute' => [
                'mage-init' => [
                    'buttonAdapter' => [
                        'actions' => [
                            [
                                'targetName' => 'product_form.product_form',
                                'actionName' => 'save',
                                'params' => [
                                    true,
                                    [
                                        'back' => 'next'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
        ];
        return $options;
    }
}