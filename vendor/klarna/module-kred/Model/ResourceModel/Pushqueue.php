<?php
/**
 * This file is part of the Klarna Kred module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Kred\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

class Pushqueue extends AbstractDb
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_coreDate;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @param Context                                     $context
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $coreDate
     * @param \Magento\Framework\Stdlib\DateTime          $dateTime
     * @param string                                      $connectionName
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $coreDate,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        $connectionName = null
    ) {
        $this->_coreDate = $coreDate;
        $this->dateTime = $dateTime;
        parent::__construct($context, $connectionName);
    }

    /**
     * Get pushqueue identifier by checkout_id
     *
     * @param string $checkout_id
     * @return int|false
     */
    public function getIdByCheckoutId($checkout_id)
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from($this->getMainTable(), 'push_queue_id')
                             ->where('klarna_checkout_id = :klarna_checkout_id');

        $bind = [':klarna_checkout_id' => (string)$checkout_id];

        return $connection->fetchOne($select, $bind);
    }

    protected function _construct()
    {
        $this->_init('klarna_kco_push_queue', 'push_queue_id');
    }

    /**
     * Set date of last update
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $object->setUpdateTime($this->dateTime->formatDate($this->_coreDate->gmtDate()));
        return parent::_beforeSave($object);
    }
}
