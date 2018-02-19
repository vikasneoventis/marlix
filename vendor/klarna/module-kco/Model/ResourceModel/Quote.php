<?php
/**
 * This file is part of the Klarna KCO module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Kco\Model\ResourceModel;

class Quote extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Get quote identifier by checkout_id
     *
     * @param $checkout_id
     * @return int|false
     */
    public function getIdByCheckoutId($checkout_id)
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from($this->getMainTable(), 'kco_quote_id')
                             ->where('klarna_checkout_id = :klarna_checkout_id');

        $bind = [':klarna_checkout_id' => (string)$checkout_id];

        return $connection->fetchOne($select, $bind);
    }

    /**
     * Get quote identifier by active Magento quote
     *
     * @param \Magento\Quote\Api\Data\CartInterface $mageQuote
     * @return int|false
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getActiveByQuote(\Magento\Quote\Api\Data\CartInterface $mageQuote)
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from($this->getMainTable(), 'kco_quote_id')->where('is_active = 1')
                             ->where('quote_id = :quote_id');

        $bind = [':quote_id' => (string)$mageQuote->getId()];

        return $connection->fetchOne($select, $bind);
    }

    protected function _construct()
    {
        $this->_init('klarna_kco_quote', 'kco_quote_id');
    }
}
