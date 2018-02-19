<?php
namespace BoostMyShop\BarcodeLabel\Controller\Adminhtml\Configuration;

use Magento\Framework\Controller\ResultFactory;

class Initialize extends \Magento\Backend\App\AbstractAction
{
    protected $_config;
    protected $_assignment;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\User\Model\UserFactory $userFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory,
        \BoostMyShop\BarcodeLabel\Model\ConfigFactory $config,
        \BoostMyShop\BarcodeLabel\Model\Assignment $assignment
    ) {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->_resultLayoutFactory = $resultLayoutFactory;
        $this->_config = $config;
        $this->_assignment = $assignment;
    }


    public function execute()
    {
        try{
            $this->_assignment->assignForAllProducts();
            $this->messageManager->addSuccess(__('Barcode values initialized for all products'));
        }
        catch(\Exception $ex)
        {
            $this->messageManager->addError($ex->getMessage());
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('adminhtml/system_config/edit', ['section' => 'barcodelabel']);

    }

    protected function _isAllowed()
    {
        return true;
    }

}