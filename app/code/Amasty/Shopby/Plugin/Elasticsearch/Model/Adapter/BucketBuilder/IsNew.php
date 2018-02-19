<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */


namespace Amasty\Shopby\Plugin\Elasticsearch\Model\Adapter\BucketBuilder;

use Amasty\Shopby\Plugin\Elasticsearch\Model\Adapter\BucketBuilderInterface as BucketBuilderInterface;
use Magento\Framework\Search\Request\BucketInterface as RequestBucketInterface;
use Magento\Framework\Search\Dynamic\DataProviderInterface;

/**
 * Class IsNew
 * @package Amasty\Shopby\Plugin\Elasticsearch\Model\Adapter\BucketBuilder
 */
class IsNew implements BucketBuilderInterface
{
    const IS_NEW_FROM_INDEX = 1;

    /**
     * @param RequestBucketInterface $bucket
     * @param array $dimensions
     * @param array $queryResult
     * @param DataProviderInterface $dataProvider
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function build(
        RequestBucketInterface $bucket,
        array $dimensions,
        array $queryResult,
        DataProviderInterface $dataProvider
    ) {
        $values = [];
        foreach ($queryResult['aggregations'][$bucket->getName()]['buckets'] as $resultBucket) {
            $values[$resultBucket['key']] = [
                'value' => $resultBucket['key'],
                'count' => $resultBucket['doc_count'],
            ];
        }
        return $values;
    }
}
