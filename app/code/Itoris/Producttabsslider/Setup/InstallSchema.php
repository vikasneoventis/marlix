<?php namespace Itoris\Producttabsslider\Setup;
/**
 * ITORIS
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the ITORIS's Magento Extensions License Agreement
 * which is available through the world-wide-web at this URL:
 * http://www.itoris.com/magento-extensions-license.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to sales@itoris.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extensions to newer
 * versions in the future. If you wish to customize the extension for your
 * needs please refer to the license agreement or contact sales@itoris.com for more information.
 *
 * @category   ITORIS
 * @package    ITORIS_M2_PRODUCT_TABS
 * @copyright  Copyright (c) 2016 ITORIS INC. (http://www.itoris.com)
 * @license    http://www.itoris.com/magento-extensions-license.html  Commercial License
 */

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    const ITORIS_PRODUCTTABS_TABS_ATTRIBUTES='itoris_producttabs_tabs_attributes';
    const ITORIS_PRODUCTTABS_TABS='itoris_producttabs_tabs';
    const ITORIS_PRODUCT_TABS_VALUE_INT='itoris_product_tabs_value_int';
    const ITORIS_PRODUCT_TABS_VALUE_VARCHAR = 'itoris_product_tabs_value_varchar';
    const ITTORIS_PRODUCT_TABS_VALUE_TEXT = 'itoris_product_tabs_value_text';
    const XML_PATH_MODULE_ENABLED = 'itoris_producttabsslider/general/enabled';
    protected  $_backendConfig;
    public $magentoConfigTable = 'core_config_data';
    /**
     * Installs DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $obectManager= \Magento\Framework\App\ObjectManager::getInstance();
        $this->_backendConfig=$obectManager->create('Magento\Backend\App\ConfigInterface');
        $helperVersion = $obectManager->create('Itoris\Producttabsslider\Helper\MagentoVersion');
        $setup->startSetup();
        if (!$setup->tableExists($setup->getTable(self::ITORIS_PRODUCTTABS_TABS))) {
            $setup->run('
                CREATE TABLE ' . $setup->getTable(self::ITORIS_PRODUCTTABS_TABS).
                ' (
                    tab_id  INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
                      PRIMARY KEY (tab_id)
                    ) ENGINE=InnoDB;');
            $setup->run('
            INSERT INTO '.$setup->getTable(self::ITORIS_PRODUCTTABS_TABS).'
            VALUES(1);
           ');
            $setup->run('
            INSERT INTO '.$setup->getTable(self::ITORIS_PRODUCTTABS_TABS).'
            VALUES(2);
           ');
            $setup->run('
            INSERT INTO '.$setup->getTable(self::ITORIS_PRODUCTTABS_TABS).'
            VALUES(3);
           ');
        }
        if (!$setup->tableExists($setup->getTable(self::ITORIS_PRODUCTTABS_TABS_ATTRIBUTES))) {
            $setup->run('
                    CREATE TABLE ' .$setup->getTable(self::ITORIS_PRODUCTTABS_TABS_ATTRIBUTES) . ' (
                    attribute_id  INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                    attribute_code  VARCHAR(255) NOT NULL,
                    backend_type   VARCHAR(8) NOT NULL,
                      PRIMARY KEY (attribute_id)
                    ) ENGINE=InnoDB
              ');
            $setup->run('
            INSERT INTO '.$setup->getTable(self::ITORIS_PRODUCTTABS_TABS_ATTRIBUTES).'
            VALUES(1,\'label\',\'varchar\');
           ');
            $setup->run('
             INSERT INTO '.$setup->getTable(self::ITORIS_PRODUCTTABS_TABS_ATTRIBUTES).'
            VALUES(2,\'status\',\'int\');');
            $setup->run('
             INSERT INTO '.$setup->getTable(self::ITORIS_PRODUCTTABS_TABS_ATTRIBUTES).'
            VALUES (3, \'order\', \'int\');');
            $setup->run('
            INSERT INTO '.$setup->getTable(self::ITORIS_PRODUCTTABS_TABS_ATTRIBUTES).'
            VALUES (4, \'content\', \'text\');');
            $setup->run('
             INSERT INTO '.$setup->getTable(self::ITORIS_PRODUCTTABS_TABS_ATTRIBUTES).'
            VALUES (5, \'show_purchased\', \'int\');');
            $setup->run('
             INSERT INTO '.$setup->getTable(self::ITORIS_PRODUCTTABS_TABS_ATTRIBUTES).'
            VALUES (6, \'groups\', \'text\');
                    ');
         

        }
        if (!$setup->tableExists($setup->getTable(self::ITORIS_PRODUCT_TABS_VALUE_INT))) {
            $setup->run('

                    CREATE TABLE ' . $setup->getTable(self::ITORIS_PRODUCT_TABS_VALUE_INT) . " (
                    value_id  INT(11) UNSIGNED  NOT NULL AUTO_INCREMENT,
                    tab_id   INT(10)  UNSIGNED NOT NULL,
                    store_id   SMALLINT (5)  UNSIGNED,
                    attribute_id    INT(11)  UNSIGNED NOT NULL,
                    product_id    INT(10)  UNSIGNED,
                    value   INT(11) NOT NULL,
                    FOREIGN KEY (`store_id`)
                    REFERENCES {$setup->getTable('store')} (`store_id`) ON DELETE cascade ON UPDATE CASCADE,
                    FOREIGN KEY (`tab_id`)
                    REFERENCES {$setup->getTable('itoris_producttabs_tabs')} (`tab_id`)  ON DELETE cascade ON UPDATE CASCADE,
                    FOREIGN KEY (`product_id`)
                    REFERENCES {$setup->getTable('catalog_product_entity')} (`entity_id`) ON DELETE cascade ON UPDATE CASCADE,
                    FOREIGN KEY (attribute_id)
                    REFERENCES {$setup->getTable('itoris_producttabs_tabs_attributes')} (`attribute_id`) ON DELETE cascade ON UPDATE CASCADE,
                      PRIMARY KEY (value_id)
                    ) ENGINE=InnoDB;");
            $setup->run('
            INSERT INTO '.$setup->getTable(self::ITORIS_PRODUCT_TABS_VALUE_INT).' (value_id,tab_id,attribute_id,value)
            VALUES(1,1,2,1),(2,1,3,1),(3,1,5,1),
            (4,2,2,1),(5,2,3,2),(6,2,5,1),
            (7,3,2,1),(8,3,3,3),(9,3,5,1)
           ');

        }
        if (!$setup->tableExists($setup->getTable(self::ITORIS_PRODUCT_TABS_VALUE_VARCHAR))) {
            $setup->run('
                CREATE TABLE  ' . $setup->getTable(self::ITORIS_PRODUCT_TABS_VALUE_VARCHAR) . " (
                    value_id  INT(11) UNSIGNED  NOT NULL AUTO_INCREMENT,
                    tab_id   INT(10)  UNSIGNED NOT NULL,
                    store_id   SMALLINT (5)  UNSIGNED,
                    attribute_id    INT(11)  UNSIGNED NOT NULL,
                    product_id    INT(10)  UNSIGNED,
                    value   VARCHAR(255) NOT NULL,
                    FOREIGN KEY (`store_id`)
                    REFERENCES {$setup->getTable('store')} (`store_id`) ON DELETE cascade ON UPDATE CASCADE,
                    FOREIGN KEY (`product_id`)
                    REFERENCES {$setup->getTable('catalog_product_entity')} (`entity_id`) ON DELETE cascade ON UPDATE CASCADE,
                    FOREIGN KEY (`tab_id`)
                    REFERENCES {$setup->getTable('itoris_producttabs_tabs')} (`tab_id`)  ON DELETE cascade ON UPDATE CASCADE,
                    FOREIGN KEY (attribute_id)
                    REFERENCES {$setup->getTable('itoris_producttabs_tabs_attributes')} (`attribute_id`) ON DELETE cascade ON UPDATE CASCADE,
                      PRIMARY KEY (value_id)
                    ) ENGINE=InnoDB;");
            $setup->run("
            INSERT INTO ".$setup->getTable(self::ITORIS_PRODUCT_TABS_VALUE_VARCHAR)." (value_id,tab_id,attribute_id,value)
            VALUES(1,1,1,'Details'),(2,2,1,'More Information'),(3,3,1,'Reviews')
           ");

        }
        if (!$setup->tableExists($setup->getTable(self::ITTORIS_PRODUCT_TABS_VALUE_TEXT))) {
            $objectManager=\Magento\Framework\App\ObjectManager::getInstance();
            $resource = $objectManager->create('Magento\Framework\App\ResourceConnection');
            $sql="SELECT GROUP_CONCAT(DISTINCT `main_table`.customer_group_id) as `group_concat` FROM `{$setup->getTable('customer_group')}` AS `main_table`";
            $conn = $resource->getConnection();
            $data=$conn->fetchAll($sql);
            $data[0]['group_concat']=$data[0]['group_concat'].',-1';
            $setup->run('
                CREATE TABLE ' . $setup->getTable(self::ITTORIS_PRODUCT_TABS_VALUE_TEXT) . " (
                    value_id  INT(11) UNSIGNED  NOT NULL AUTO_INCREMENT,
                    tab_id   INT(10)  UNSIGNED NOT NULL,
                    store_id   SMALLINT (5)  UNSIGNED,
                    attribute_id    INT(11)  UNSIGNED NOT NULL,
                    product_id    INT(10)  UNSIGNED,
                    value   LONGTEXT NOT NULL,
                    FOREIGN KEY (`tab_id`)
                    REFERENCES {$setup->getTable('itoris_producttabs_tabs')} (`tab_id`) ON DELETE cascade ON UPDATE CASCADE,
                    FOREIGN KEY (`store_id`)
                    REFERENCES {$setup->getTable('store')} (`store_id`) ON DELETE cascade ON UPDATE CASCADE,
                    FOREIGN KEY (`product_id`)
                    REFERENCES {$setup->getTable('catalog_product_entity')} (`entity_id`) ON DELETE cascade ON UPDATE CASCADE,
                    FOREIGN KEY (attribute_id)
                    REFERENCES {$setup->getTable('itoris_producttabs_tabs_attributes')} (`attribute_id`) ON DELETE cascade ON UPDATE CASCADE,
                      PRIMARY KEY (value_id)
                    ) ENGINE=InnoDB;");
            $tab1='{{block class="Itoris\Producttabsslider\Block\Frontend\Description" template="Itoris_Producttabsslider::product/view/attribute.phtml}}';
            $tab2='{{block class="Magento\Catalog\Block\Product\View\Attributes"template="Magento_Catalog::product/view/attributes.phtml"}}';
            if($helperVersion->getMagentoVersion()>2.07)
            $tab3='{{block class="Itoris\Producttabsslider\Block\Frontend\Review" template="Itoris_Producttabsslider::product/review.phtml"}}';
            else
                $tab3='{{block class="Magento\Review\Block\Product\Review" template="Magento_Review::review.phtml"}} {{block class="Magento\Review\Block\Form"}}';

            $tab1 = str_replace('\\','\\\\',$tab1);
            $tab2 = str_replace('\\','\\\\',$tab2);
            $tab3 = str_replace('\\','\\\\',$tab3);
            $tab1 = str_replace(':','\:',$tab1);
            $tab2 = str_replace(':','\:',$tab2);
            $tab3 = str_replace(':','\:',$tab3);

            $conn->query("
            INSERT INTO ".$setup->getTable(self::ITTORIS_PRODUCT_TABS_VALUE_TEXT)." (value_id,tab_id,attribute_id,value)
            VALUES(1,1,4,'{$tab1}'),(2,2,4,'{$tab2}'),
            (3,3,4,'{$tab3}'),
            (4,1,6,'{$data[0]['group_concat']}'),(5,2,6,'{$data[0]['group_concat']}'),(6,3,6,'{$data[0]['group_concat']}')
           ");
        }
        $configNote = $this->_backendConfig->getValue(self::XML_PATH_MODULE_ENABLED);
        if(!isset($configNote)){
            $setup->run("
            INSERT INTO {$setup->getTable($this->magentoConfigTable)}
            (path, value)
            VALUES('".self::XML_PATH_MODULE_ENABLED."', '1')
            ");
        }
        $setup->endSetup();


    }
}