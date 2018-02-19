<?php
namespace Potato\ImageOptimization\Model\ResourceModel\Image;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Potato\ImageOptimization\Model;
use Magento\Framework\DB\Select;

/**
 * Class Collection
 */
class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            Model\Image::class,
            Model\ResourceModel\Image::class
        );
    }

    /**
     * @param string $valueField
     * @param string $labelField
     * @return array
     */
    protected function _toOptionHash($valueField = 'id', $labelField = 'path')
    {
        return parent::_toOptionHash($valueField, $labelField);
    }

    /**
     * @param int $status
     * @return $this
     */
    public function addFilterByStatus($status)
    {
        $this->addFilter('status', $status);
        return $this;
    }
    
    public function selectErrorInfoByGroup()
    {
        $this->getSelect()->reset(Select::COLUMNS);
        $this->getSelect()->columns([
            'code' => 'main_table.error_type',
            'count' => 'COALESCE(COUNT(main_table.id), 0)'
        ]);
        $this->addFieldToFilter('error_type', ['notnull' => true]);
        $this->getSelect()->group('main_table.error_type');
        return $this;
    }
}
