<?php

namespace Potato\ImageOptimization\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Api\BookmarkManagementInterface;
use Magento\Ui\Api\BookmarkRepositoryInterface;
use Magento\Ui\Api\Data\BookmarkInterfaceFactory;
use Magento\Framework\Json\EncoderInterface as JsonEncoder;
use Magento\Authorization\Model\UserContextInterface;

/**
 * Class Filter
 */
abstract class Filter extends Action
{
    const ADMIN_RESOURCE = 'Potato_ImageOptimization::po_image_grid';

    /**
     * @var \Magento\Ui\Api\BookmarkManagementInterface
     */
    protected $bookmarkManagement;

    /**
     * @var \Magento\Ui\Api\BookmarkRepositoryInterface
     */
    protected $bookmarkRepository;

    /**
     * @var BookmarkInterfaceFactory
     */
    protected $bookmarkDataFactory;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncode;

    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    protected $userContext;

    /**
     * QueueFilter constructor.
     * @param Context $context
     * @param BookmarkManagementInterface $bookmarkManagement
     * @param BookmarkRepositoryInterface $bookmarkRepository
     * @param BookmarkInterfaceFactory $bookmarkDataFactory
     * @param JsonEncoder $jsonEncode
     * @param UserContextInterface $userContext
     */
    public function __construct(
        Context $context,
        BookmarkManagementInterface $bookmarkManagement,
        BookmarkRepositoryInterface $bookmarkRepository,
        BookmarkInterfaceFactory $bookmarkDataFactory,
        JsonEncoder $jsonEncode,
        UserContextInterface $userContext
    ) {
        parent::__construct($context);
        $this->bookmarkManagement = $bookmarkManagement;
        $this->bookmarkRepository = $bookmarkRepository;
        $this->bookmarkDataFactory = $bookmarkDataFactory;
        $this->jsonEncode = $jsonEncode;
        $this->userContext = $userContext;
    }

    /**
     * @return $this
     */
    protected function getCurrentBookmark()
    {
        $identifier = 'current';
        $namespace = 'image_listing';
        /** @var \Magento\Ui\Api\Data\BookmarkInterface $currentBookmark */
        $currentBookmark = $this->bookmarkManagement->getByIdentifierNamespace($identifier, $namespace);
        if (!$currentBookmark) {
            $currentBookmark = $this->bookmarkDataFactory->create();
            $currentBookmark
                ->setIdentifier($identifier)
                ->setNamespace($namespace)
                ->setUserId($this->userContext->getUserId());
            ;
        }
        return $currentBookmark;
    }
}
