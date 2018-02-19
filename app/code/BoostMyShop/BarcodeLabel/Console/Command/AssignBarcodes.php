<?php
namespace BoostMyShop\BarcodeLabel\Console\Command;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ObjectManager\ConfigLoader;
use Magento\Framework\ObjectManagerInterface;
use Symfony\Component\Console\Command\Command;
use Magento\Framework\App\ObjectManagerFactory;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Magento\Framework\Setup\SchemaSetupInterface;


class AssignBarcodes extends Command
{
    protected $_assignment;

    /**
     * Constructor
     * @param ObjectManagerFactory $objectManagerFactory
     */
    public function __construct(
        \Magento\Framework\App\State $state,
        \BoostMyShop\BarcodeLabel\Model\Assignment $assignment
    )
    {
        $this->_assignment = $assignment;
        $this->_state = $state;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('bms_barcodelabel:assign_barcodes')->setDescription('Assign barcodes to products');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('START barcode assignment');

        $this->_state->setAreaCode('adminhtml');

        $this->_assignment->assignForAllProducts();

        $output->writeln('END barcode assignment');
    }


}
