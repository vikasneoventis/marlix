<?php
/**
 * This file is part of the Klarna Kred module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */
namespace Klarna\Kred\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface PushqueueRepositoryInterface
{
    public function save(PushqueueInterface $page);

    public function getById($id);

    public function getByCheckoutId($checkout_id);

    public function getList(SearchCriteriaInterface $criteria);

    public function delete(PushqueueInterface $page);

    public function deleteById($id);
}
