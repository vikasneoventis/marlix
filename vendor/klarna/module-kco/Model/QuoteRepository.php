<?php
/**
 * This file is part of the Klarna KCO module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Kco\Model;

use Klarna\Kco\Api\QuoteInterface;
use Klarna\Kco\Api\QuoteRepositoryInterface;
use Klarna\Kco\Model\QuoteFactory;
use Klarna\Kco\Model\QuoteRepository\SaveHandler;
use Klarna\Kco\Model\ResourceModel\Quote as QuoteResource;
use Klarna\Kco\Model\ResourceModel\Quote\CollectionFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Api\Data\CartInterface as MageQuoteInterface;

/**
 * Class QuoteRepository
 *
 * @package Klarna\Core\Model
 */
class QuoteRepository implements QuoteRepositoryInterface
{
    /**
     * @var QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var QuoteResource
     */
    protected $resourceModel;

    /**
     * @var array
     */
    protected $instances = [];

    /**
     * @var array
     */
    protected $instancesById = [];

    /**
     * @var SaveHandler
     */
    protected $saveHandler;

    /**
     * QuoteRepository constructor.
     *
     * @param QuoteFactory  $quoteFactory
     * @param QuoteResource $resourceModel
     * @param SaveHandler   $saveHandler
     */
    public function __construct(
        QuoteFactory $quoteFactory,
        QuoteResource $resourceModel,
        SaveHandler $saveHandler
    ) {
        $this->quoteFactory = $quoteFactory;
        $this->resourceModel = $resourceModel;
        $this->saveHandler = $saveHandler;
    }

    /**
     * {@inheritdoc}
     */
    public function getByCheckoutId($checkoutId, $forceReload = false)
    {
        if ($forceReload || !isset($this->instances[$checkoutId])) {
            $quoteId = $this->resourceModel->getIdByCheckoutId($checkoutId);
            if (!$quoteId) {
                /** @var QuoteInterface $quote */
                $quote = $this->quoteFactory->create();
                $quote->setKlarnaCheckoutId($checkoutId);
                $this->save($quote);
                $quoteId = $quote->getId();
            }
            $quote = $this->loadQuote('load', 'kco_quote_id', $quoteId);
            $this->instances[$checkoutId] = $quote;
            $this->instancesById[$quote->getId()] = $quote;
        }
        return $this->instances[$checkoutId];
    }

    /**
     * {@inheritdoc}
     */
    public function save(QuoteInterface $quote)
    {
        return $this->saveHandler->save($quote);
    }

    /**
     * Load quote with different methods
     *
     * @param string $loadMethod
     * @param string $loadField
     * @param int    $identifier
     * @throws NoSuchEntityException
     * @return Quote
     */
    public function loadQuote($loadMethod, $loadField, $identifier)
    {
        /** @var Quote $quote */
        $quote = $this->quoteFactory->create();
        $quote->$loadMethod($identifier);
        if (!$quote->getId()) {
            throw NoSuchEntityException::singleField($loadField, $identifier);
        }
        return $quote;
    }

    /**
     * {@inheritdoc}
     */
    public function getActiveByQuote(MageQuoteInterface $mageQuote)
    {
        $quoteId = $this->resourceModel->getActiveByQuote($mageQuote);
        if (!$quoteId) {
            $quote = $this->create();
            $quote->setQuoteId($mageQuote->getId());
            return $quote;
        }
        return $this->loadQuote('load', 'kco_quote_id', $quoteId);
    }

    /**
     * Create quote
     *
     * @param array $data
     * @return QuoteInterface
     * @deprecated
     */
    public function create($data = [])
    {
        return $this->quoteFactory->create($data);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($id)
    {
        return $this->delete($this->getById($id));
    }

    /**
     * {@inheritdoc}
     */
    public function delete(QuoteInterface $quote)
    {
        $quoteId = $quote->getId();
        $checkoutId = $quote->getKlarnaCheckoutId();
        $quote->delete();
        unset($this->instances[$checkoutId]);
        unset($this->instancesById[$quoteId]);
    }

    /**
     * {@inheritdoc}
     */
    public function getById($quoteId, $forceReload = false)
    {
        if (!isset($this->instancesById[$quoteId]) || $forceReload) {
            $quote = $this->loadQuote('load', 'kco_quote_id', $quoteId);
            $this->instancesById[$quoteId] = $quote;
            $this->instances[$quote->getKlarnaCheckoutId()] = $quote;
        }
        return $this->instancesById[$quoteId];
    }
}
