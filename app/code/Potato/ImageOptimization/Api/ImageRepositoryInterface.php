<?php

namespace Potato\ImageOptimization\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * @api
 */
interface ImageRepositoryInterface
{
    /**
     * Create new empty image interface
     *
     * @return \Potato\ImageOptimization\Api\Data\ImageInterface
     */
    public function create();
        
    /**
     * Save Image
     *
     * @param \Potato\ImageOptimization\Api\Data\ImageInterface $image
     * @return \Potato\ImageOptimization\Api\Data\ImageInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(Data\ImageInterface $image);

    /**
     * Get info about Image by image id
     *
     * @param int $imageId
     * @return \Potato\ImageOptimization\Api\Data\ImageInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($imageId);

    /**
     * Get info about Image by path
     *
     * @param string $path
     * @return \Potato\ImageOptimization\Api\Data\ImageInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getByPath($path);
    
    /**
     * Delete image
     *
     * @param \Potato\ImageOptimization\Api\Data\ImageInterface $image
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function delete(Data\ImageInterface $image);

    /**
     * Retrieve list of images
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return \Potato\ImageOptimization\Api\Data\ImageSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Retrieve list of all images
     *
     * @return \Potato\ImageOptimization\Api\Data\ImageSearchResultsInterface
     */
    public function getAllList();

    /**
     * Retrieve list of images for optimization 
     *
     * @return \Potato\ImageOptimization\Api\Data\ImageSearchResultsInterface
     */
    public function getNeedToOptimizationList();

    /**
     * Retrieve list of images for optimization
     *
     * @param string $status
     * @return \Potato\ImageOptimization\Api\Data\ImageSearchResultsInterface
     */
    public function getListByStatus($status);
}
