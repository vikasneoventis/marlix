<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */

namespace Amasty\ProductAttachment\Block\Adminhtml\Icon;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class DeleteButton extends GenericButton implements ButtonProviderInterface
{

    /**
     * @return array
     */
    public function getButtonData()
    {

        $data = [
            'label' => __('Delete Icon'),
            'class' => 'delete',
            'id' => 'icon-edit-delete-button',
            'data_attribute' => [
                'url' => $this->getDeleteUrl()
            ],
            'on_click' => '',
            'sort_order' => 20,
        ];
        return $this->getIconId() ? $data : [];
    }

    /**
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->getUrl('*/*/delete', ['id' => $this->getIconId()]);
    }

    public function getIconId()
    {
        return $this->registry->registry('amfile_icon_id');
    }
}
