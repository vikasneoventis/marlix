<?php
/**
 * This file is part of the Klarna KCO module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Kco\Model\QuoteRepository;

use Klarna\Kco\Api\QuoteInterface;
use Klarna\Kco\Model\ResourceModel\Quote as QuoteResourceModel;

class SaveHandler
{
    /**
     * @var QuoteResourceModel
     */
    private $quoteResourceModel;

    /**
     * @param QuoteResourceModel $quoteResource
     */
    public function __construct(
        QuoteResourceModel $quoteResource
    ) {
        $this->quoteResourceModel = $quoteResource;
    }

    /**
     * @param QuoteInterface $quote
     * @return QuoteInterface
     *
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function save(QuoteInterface $quote)
    {
        $this->quoteResourceModel->save($quote);
        return $quote;
    }
}
