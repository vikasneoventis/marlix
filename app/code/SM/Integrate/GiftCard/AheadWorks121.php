<?php
/**
 * Created by KhoiLe - mr.vjcspy@gmail.com
 * Date: 10/13/17
 * Time: 3:00 PM
 */

namespace SM\Integrate\GiftCard;


use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use SM\Integrate\Data\GiftCardQuoteData;
use SM\Integrate\GiftCard\Contract\AbstractGCIntegrate;
use SM\Integrate\GiftCard\Contract\GCIntegrateInterface;

class AheadWorks121 extends AbstractGCIntegrate implements GCIntegrateInterface {

    protected $_gcRepository;
    private   $_gcValidator;
    private   $_gcQuoteCollectionFactory;
    private   $_gcQuoteFactory;
    private   $_cartExtensionFactory;

    /**
     * @param $giftData
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveGCDataBeforeQuoteCollect($giftData) {
        if (isset($giftData['is_delete']) && $giftData['is_delete'] === true) {
            $this->removeGiftCard($giftData);

            return;
        }

        if (!isset($giftData['gift_code'])) {
            return;
        }

        try {
            $giftcardCode = $giftData['gift_code'];
            $giftcard     = $this->getGiftCardRepository()->getByCode($giftcardCode, $this->getQuote()->getStore()->getWebsiteId());
        }
        catch (NoSuchEntityException $e) {
            throw new NoSuchEntityException(__('The specified Gift Card code is not valid'));
        }


        if (!$this->getGiftCardValidator()->isValid($giftcard)) {
            $messages = $this->getGiftCardValidator()->getMessages();
            throw new LocalizedException($messages[0]);
        }

        $giftcardQuoteItems = $this->getGiftCardQuoteCollectionFactory()
                                   ->create()
                                   ->addFieldToFilter('quote_id', $this->getQuote()->getId())
                                   ->addFieldToFilter('giftcard_id', $giftcard->getId())
                                   ->load()
                                   ->getItems();
        if ($giftcardQuoteItems) {
            throw new LocalizedException(__('The specified Gift Card code already in the quote'));
        }

        $this->addGiftcardToQuote($giftcard, $this->getQuote());
        $this->getQuote()->getShippingAddress()->setCollectShippingRates(true);
    }

    /**
     * @param                            $giftcard
     * @param \Magento\Quote\Model\Quote $quote
     *
     * @return $this
     */
    protected function addGiftcardToQuote($giftcard, \Magento\Quote\Model\Quote $quote) {
        $extensionAttributes = $quote->getExtensionAttributes()
            ? $quote->getExtensionAttributes()
            : $this->getCartExtensionFactory()->create();

        /** @var GiftcardQuoteInterface $giftcardQuoteObject */
        $giftcardQuoteObject = $this->getGiftCardQuoteFactory()->create();
        $giftcardQuoteObject
            ->setGiftcardId($giftcard->getId())
            ->setGiftcardCode($giftcard->getCode())
            ->setGiftcardBalance($giftcard->getBalance())
            ->setQuoteId($quote->getId())
            ->setBaseGiftcardAmount($giftcard->getBalance());

        $giftcards = [$giftcardQuoteObject];
        if ($extensionAttributes->getAwGiftcardCodes()) {
            $giftcards = array_merge($giftcards, $extensionAttributes->getAwGiftcardCodes());
        }
        $giftcards = $this->sortGiftcards($giftcards);
        $extensionAttributes->setAwGiftcardCodes($giftcards);

        $quote->setExtensionAttributes($extensionAttributes);

        return $this;
    }

    /**
     * @return \Magento\Quote\Api\Data\CartExtensionFactory
     */
    protected function getCartExtensionFactory() {
        if (is_null($this->_cartExtensionFactory)) {
            $this->_cartExtensionFactory = $this->objectManager->create('Magento\Quote\Api\Data\CartExtensionFactory');
        }

        return $this->_cartExtensionFactory;
    }

    /**
     * @return \Aheadworks\Giftcard\Api\GiftcardRepositoryInterface
     */
    protected function getGiftCardRepository() {
        if (is_null($this->_gcRepository)) {
            $this->_gcRepository = $this->objectManager->create('Aheadworks\Giftcard\Api\GiftcardRepositoryInterface');
        }

        return $this->_gcRepository;
    }

    /**
     * @return \Aheadworks\Giftcard\Model\Giftcard\Validator
     */
    protected function getGiftCardValidator() {
        if (is_null($this->_gcValidator)) {
            $this->_gcValidator = $this->objectManager->create('Aheadworks\Giftcard\Model\Giftcard\Validator');
        }

        return $this->_gcValidator;
    }

    /**
     * @return \Aheadworks\Giftcard\Model\ResourceModel\Giftcard\Quote\CollectionFactory
     */
    protected function getGiftCardQuoteCollectionFactory() {
        if (is_null($this->_gcQuoteCollectionFactory)) {
            $this->_gcQuoteCollectionFactory = $this->objectManager->create(
                'Aheadworks\Giftcard\Model\ResourceModel\Giftcard\Quote\CollectionFactory');
        }

        return $this->_gcQuoteCollectionFactory;
    }

    /**
     * @return \Aheadworks\Giftcard\Api\Data\Giftcard\QuoteInterfaceFactory
     */
    protected function getGiftCardQuoteFactory() {
        if (is_null($this->_gcQuoteFactory)) {
            $this->_gcQuoteFactory = $this->objectManager->create('Aheadworks\Giftcard\Api\Data\Giftcard\QuoteInterfaceFactory');
        }

        return $this->_gcQuoteFactory;
    }

    /**
     * @return \SM\Integrate\Data\GiftCardQuoteData
     */
    public function getQuoteGCData() {
        $quoteGcData = new GiftCardQuoteData();
        $quote       = $this->getQuote();
        $quoteGcData->addData(
            [
                'base_giftcard_amount' => -$quote->getData('base_aw_giftcard_amount'),
                'giftcard_amount'      => -$quote->getData('aw_giftcard_amount'),
            ]);

        return $quoteGcData;
    }

    /**
     * Sort Gift Card codes by asc
     *
     * @param GiftcardQuoteInterface[] $giftcards
     *
     * @return GiftcardQuoteInterface[]
     */
    private function sortGiftcards($giftcards) {
        usort(
            $giftcards,
            function ($a, $b) {
                if ($a->getGiftcardBalance() == $b->getGiftcardBalance()) {
                    return 0;
                }

                return $a->getGiftcardBalance() > $b->getGiftcardBalance() ? 1 : -1;
            });

        return $giftcards;
    }

    public function removeGiftCard($giftData) {
        $quote = $this->getQuote();
        if ($quote->getExtensionAttributes() && $quoteGiftcards = $quote->getExtensionAttributes()->getAwGiftcardCodes()) {
            foreach ($quoteGiftcards as $quoteGiftcard) {
                $quoteGiftcard->setIsRemove(true);
            }
        }
    }
}