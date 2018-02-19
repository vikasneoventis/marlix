<?php
/**
 * This file is part of the Klarna KCO module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Kco\Model\System\Config\Source\Customer;

use Magento\Customer\Api\GroupManagementInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory;
use Magento\Framework\Convert\DataObject;

class Group extends \Magento\Eav\Model\Entity\Attribute\Source\Table
{
    /**
     * @var GroupManagementInterface
     */
    protected $_groupManagement;

    /**
     * @var DataObject
     */
    protected $_converter;

    /**
     * @param CollectionFactory        $attrOptionCollectionFactory
     * @param OptionFactory            $attrOptionFactory
     * @param GroupManagementInterface $groupManagement
     * @param DataObject               $converter
     */
    public function __construct(
        CollectionFactory $attrOptionCollectionFactory,
        OptionFactory $attrOptionFactory,
        GroupManagementInterface $groupManagement,
        DataObject $converter
    ) {
        $this->_groupManagement = $groupManagement;
        $this->_converter = $converter;
        parent::__construct($attrOptionCollectionFactory, $attrOptionFactory);
    }

    /**
     * Return array of customer groups
     *
     * @return array
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $groups = $this->_groupManagement->getLoggedInGroups();
            array_unshift($groups, $this->_groupManagement->getNotLoggedInGroup());
            $this->_options = $this->_converter->toOptionArray($groups, 'id', 'code');
            array_unshift($this->_options, ['value' => -1, 'label' => ' ']);
        }
        return $this->_options;
    }
}
