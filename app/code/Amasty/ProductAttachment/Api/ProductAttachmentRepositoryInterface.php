<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */

namespace Amasty\ProductAttachment\Api;


/**
 * Interface ProductAttachmentRepositoryInterface
 *
 * @package Amasty\ProductAttachment\Api
 */
interface ProductAttachmentRepositoryInterface
{
    /**
     * Save attachment.
     *
     * @param \Amasty\ProductAttachment\Api\Data\ProductAttachmentInterface $productAttachment
     *
     * @return \Amasty\ProductAttachment\Api\Data\ProductAttachmentInterface
     */
    public function save(Data\ProductAttachmentInterface $productAttachment);

    /**
     * Save attachment.
     *
     * @param \Amasty\ProductAttachment\Api\Data\ProductAttachmentInterface $productAttachment
     *
     * @return \Amasty\ProductAttachment\Api\Data\ProductAttachmentInterface
     */
    public function saveExist(Data\ProductAttachmentInterface $productAttachment);

    /**
     * Retrieve attachment.
     *
     * @param int $attachmentId
     * @return \Amasty\ProductAttachment\Api\Data\ProductAttachmentInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($attachmentId);

    /**
     * Retrieve attachments matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Amasty\ProductAttachment\Api\Data\ProductAttachmentSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete attachment.
     *
     * @param \Amasty\ProductAttachment\Api\Data\ProductAttachmentInterface $productAttachment
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(Data\ProductAttachmentInterface $productAttachment);

    /**
     * Delete attachment by ID.
     *
     * @param int $attachmentId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($attachmentId);
}
