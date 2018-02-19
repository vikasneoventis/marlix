<?php


namespace Ves\Megamenu\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class Cleancache extends Command
{

    const NAME_ARGUMENT = "name";
    const NAME_OPTION = "option";

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * Constructor.
     *
     * @param \Magento\Framework\App\ResourceConnection $resource 
     * @api
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource
        ) {
        $this->_resource = $resource;
        parent::__construct();
        
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {

        try {
            $resource   = $this->_resource;
            $table      = $resource->getTableName('ves_megamenu_cache');
            $connection = $resource->getConnection();
            $connection->truncateTable($table);
            $output->writeln("The Mega Menu Cache has been flushed.");
        } catch (\Exception $e) {
            $output->writeln("Something went wrong in progressing.");
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName("ves_megamenu:cleancache");
        $this->setDescription("Clean megamenu cache");
        $this->setDefinition([
            new InputArgument(self::NAME_ARGUMENT, InputArgument::OPTIONAL, "Name"),
            new InputOption(self::NAME_OPTION, "-a", InputOption::VALUE_NONE, "Option functionality")
        ]);
        parent::configure();
    }
}
