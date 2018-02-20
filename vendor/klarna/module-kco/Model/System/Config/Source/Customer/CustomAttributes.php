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


class CustomAttributes extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @var array
     */
    protected $options = [];

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;
    /**
     * @var \Magento\Eav\Api\AttributeRepositoryInterface
     */
    protected $attributeRepository;


    /**
     * CustomAttributes constructor.
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository
     */
    public function __construct(
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository
    )
    {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @param bool $withEmpty
     * @return array
     */
    public function getAllOptions($withEmpty = true)
    {
        if (empty($this->options)) {
            try {
                $listInfo = $this->loadAllCustomAttributes();
                foreach ($listInfo as $items) {
                    $this->options[] = [
                        'value' => $items->getAttributeCode(),
                        'label' =>  $items->getFrontendLabel()
                    ];
                }
            } catch (\Exception $e) {
                return [['value' => '0', 'label' => __('Select')]];
            }
        }
        if ($withEmpty) {
            if (empty($this->options)) {
                return [['value' => '0', 'label' => __('Select')]];
            }
            return array_merge([['value' => '0', 'label' => __('Select')]], $this->options);
        }
        return $this->options;
    }

    /**
     * @return \Magento\Eav\Api\Data\AttributeInterface[]
     */
    protected function loadAllCustomAttributes()
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('is_user_defined', 1)->create();
        $attributeRepository = $this->attributeRepository->getList(
            \Magento\Customer\Model\Customer::ENTITY,
            $searchCriteria
        );

        return $attributeRepository->getItems();
    }
}