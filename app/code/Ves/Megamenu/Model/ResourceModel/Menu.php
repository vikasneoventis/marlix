<?php
/**
 * Venustheme
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Venustheme.com license that is
 * available through the world-wide-web at this URL:
 * http://www.venustheme.com/license-agreement.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Venustheme
 * @package    Ves_Megamenu
 * @copyright  Copyright (c) 2017 Venustheme (http://www.venustheme.com/)
 * @license    http://www.venustheme.com/LICENSE-1.0.html
 */

namespace Ves\Megamenu\Model\ResourceModel;
use Magento\Framework\App\Filesystem\DirectoryList;

class Menu extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Store model
     *
     * @var null|\Magento\Store\Model\Store
     */
    protected $_store = null;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    /**
     * @var array
     */
    protected $_data;

    /**
     * @var \Ves\Megamenu\Helper\Editor
     */
    protected $editor;

    /**
     * @var \Ves\Megamenu\Helper\Data
     */
    protected $_vesData;

    /**
     * @var array
     */
    protected $_items = [];

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context        
     * @param \Magento\Store\Model\StoreManagerInterface        $storeManager   
     * @param \Magento\Framework\Filesystem                     $filesystem     
     * @param \Ves\Megamenu\Helper\Editor                       $editor         
     * @param null                                            $connectionName 
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Filesystem $filesystem,
        \Ves\Megamenu\Helper\Editor $editor,
        \Ves\Megamenu\Helper\Data $vesData,
        $connectionName = null
        ) {
        parent::__construct($context, $connectionName);
        $this->_storeManager    = $storeManager;
        $this->_filesystem      = $filesystem;
        $this->editor           = $editor;
        $this->_vesData         = $vesData;
    }

    protected function _construct()
    {
        $this->_init('ves_megamenu_menu', 'menu_id');
    }

    public function extractItem($items){
        if(is_array($items)){
            foreach ($items as $item) {
                if(isset($item['children']) && is_array($item['children'])){
                    $this->extractItem($item['children']);
                }
                unset($item['children']);
                $this->_data[] = $item;
            }
        }
    }

    public function getAllItems($items) {
        if (is_array($items)) {
            foreach ($items as $item) {
                if (isset($item['children']) && is_array($item['children'])) {
                    $this->getAllItems($item['children']);
                }
                unset($item['children']);
                if (isset($item['id'])) {
                    $this->_items[] = $item['id'];
                }
            }
        }
    }

    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if (!$object->getData('is_preview')) {
            $data = $object->getData();

            if (!isset($data['mass'])) {
                $structure = json_decode($object->getStructure(), true);
                $this->getAllItems($structure);

                $where = '';
                $i     = 0;

                $where[] = 'menu_id = ' . (int)$object->getId();
                if (!empty($this->_items)) {
                    foreach ($this->_items as $id) {
                        $where[] = 'item_id != "' . $id . '"';
                    }
                }
                $this->getConnection()->delete($this->getTable('ves_megamenu_item'), $where);

                $oldStores = $this->lookupStoreIds($object->getId());
                $newStores = (array)$object->getStores();
                if (empty($newStores)) {
                    $newStores = (array)$object->getStoreId();
                }
                $table = $this->getTable('ves_megamenu_menu_store');
                $insert = array_diff($newStores, $oldStores);
                $delete = array_diff($oldStores, $newStores);
                if ($delete) {
                    $where = ['menu_id = ?' => (int)$object->getId(), 'store_id IN (?)' => $delete];

                    $this->getConnection()->delete($table, $where);
                }

                if ($insert) {
                    $data = [];
                    foreach ($insert as $storeId) {
                        $data[] = ['menu_id' => (int)$object->getId(), 'store_id' => (int)$storeId];
                    }
                    $this->getConnection()->insertMultiple($table, $data);
                }

                // CUSTOMER GROUP
                $oldCustomerGroups = $this->lookupCustomerGroupIds($object->getId());
                $newCustomerGroups = (array)$object->getCustomerGroupIds();
                $table = $this->getTable('ves_megamenu_menu_customergroup');
                $insert = array_diff($newCustomerGroups, $oldCustomerGroups);
                $delete = array_diff($oldCustomerGroups, $newCustomerGroups);
                if ($delete) {
                    $where = ['menu_id = ?' => (int)$object->getId(), 'customer_group_id IN (?)' => $delete];
                    $this->getConnection()->delete($table, $where);
                }
                if ($insert) {
                    $data = [];
                    foreach ($insert as $storeId) {
                        $data[] = ['menu_id' => (int)$object->getId(), 'customer_group_id' => (int)$storeId];
                    }
                    $this->getConnection()->insertMultiple($table, $data);
                }
            }
        }
        return parent::_afterSave($object);
    }

    public function load(\Magento\Framework\Model\AbstractModel $object, $value, $field = null)
    {
        if (!is_numeric($value) && is_null($field)) {
            $field = 'alias';
        }
        return parent::load($object, $value, $field);
    }

    protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object)
    {
        $menuId = $object->getId();
        if ($menuId && ( $object->getStructure() != '' || $object->getStructure() != '[]') ) {
            $select = $this->getConnection()->select()->from($this->getTable('ves_megamenu_item'))
            ->where(
                'menu_id = ?',
                intval($menuId)
                );
            $data      = $this->getConnection()->fetchAll($select);
            $fields    = $this->editor->getFields();
            $mediaUrl  = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            $menuItems = [];
            if(!empty($data)){
                foreach ($data as $k => $v) {
                    if(is_array($v)){
                        foreach ($v as $x => $y) {
                            if(isset($fields[$x]) && ($fields[$x]['type']=='image' || $fields[$x]['type']=='file') && $y!='' && (strpos($y, 'http') === false || strpos($y, 'https') === false) && $y) {
                                $v[$x] = $mediaUrl . $y;
                            }
                        }
                    }
                    $v['htmlId'] = 'vesitem-' . $v['id'] . time() . rand();
                    $menuItems[$v['item_id']] = $v;
                }
            }
            $object->setData('menuItems', $menuItems);
        }

        if ($object->getId()) {
            $stores = $this->lookupStoreIds($object->getId());
            $object->setData('store_id', $stores);
        }

        if ($object->getId()) {
            $customerGroupIds = $this->lookupCustomerGroupIds($object->getId());
            $object->setData('customer_group_ids', $customerGroupIds);
        }

        if ($object->getId()) {
            $versionIds = $this->lookupVersionIds($object->getId());
            $object->setData('version_id', $versionIds);
        }

        return parent::_afterLoad($object);
    }

    /**
     * Get store ids to which specified item is assigned
     *
     * @param int $id
     * @return array
     */
    public function lookupCustomerGroupIds($id)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getTable('ves_megamenu_menu_customergroup'),
            'customer_group_id'
            )->where(
            'menu_id = :menu_id'
            );
            $binds = [':menu_id' => (int)$id];
            return $connection->fetchCol($select, $binds);
        }

        public function lookupStoreIds($menuId)
        {
            $connection = $this->getConnection();

            $select = $connection->select()->from(
                $this->getTable('ves_megamenu_menu_store'),
                'store_id'
                )
            ->where(
                'menu_id = ?',
                intval($menuId)
                );
            return $connection->fetchCol($select);
        }

        public function lookupVersionIds($menuId)
        {
            $connection = $this->getConnection();

            $select = $connection->select()->from(
                $this->getTable('ves_megamenu_menu_log'),
                'version'
                )
            ->where(
                'menu_id = ?',
                intval($menuId)
                );
            return $connection->fetchCol($select);
        }

        protected function _beforeDelete(\Magento\Framework\Model\AbstractModel $object)
        {
            $condition = ['menu_id = ?' => intval($object->getId())];
            $this->getConnection()->delete($this->getTable('ves_megamenu_item'), $condition);
            $this->getConnection()->delete($this->getTable('ves_megamenu_menu_store'), $condition);
            $this->getConnection()->delete($this->getTable('ves_megamenu_menu_customergroup'), $condition);
            return parent::_beforeDelete($object);
        }

    /**
     * Set store model
     *
     * @param \Magento\Store\Model\Store $store
     * @return $this
     */
    public function setStore($store)
    {
        $this->_store = $store;
        return $this;
    }

    /**
     * Retrieve store model
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        if($this->_store){
            return $this->_store;
        }else{
            return false;
        }
    }

    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if ($object->getData('is_preview')) {
            return parent::_beforeSave($object);
        }

        $data = $object->getData();

        if($this->_vesData->getConfig('general_settings/enable_backup') && !isset($data['duplicate'])) {
            $form_key = '';
            if (isset($data['form_key'])) {
                $form_key = $data['form_key'];
            }
            unset($data['form_key']);
            if ((!isset($data['revert_previous']) && !isset($data['revert_next'])) && !empty($data) && isset($data['structure'])) {
                try{
                    $menuId = $object->getId();
                    if(!$menuId){
                        $table = $this->getTable('ves_megamenu_menu');
                        $connection = $this->getConnection();
                        $select = $connection->select()->from(
                            $this->getTable('ves_megamenu_menu_log')
                            )->order('log_id DESC');
                        $result =  $connection->fetchAll($select);
                        if(empty($result)){
                            $menuId = 1;
                        } else {
                            $menuId = $result[0]['menu_id'];
                        }
                    }

                    $structure = base64_encode(serialize($data['structure']));
                    unset($data['structure']);
                    $table = $this->getTable('ves_megamenu_menu_log');
                    $connection = $this->getConnection();
                    $select = $connection->select()->from(
                        $this->getTable('ves_megamenu_menu_log')
                        )
                    ->where(
                        'menu_id = ?',
                        intval($object->getId())
                        )
                    ->order('version DESC');
                    $result =  $connection->fetchAll($select);

                    usort($result, function($a, $b) {
                        return (int)$b['version'] - (int)$a['version'];
                    });

                    $backupVersion = (int)$this->_vesData->getConfig('general_settings/backup_version');
                    $count = count($result);
                    if($count > $backupVersion-1){
                        $deleteIds = [];
                        foreach ($result as $k => $v) {
                            if($k>=($backupVersion-1)){
                                $deleteIds[] = $v['log_id'];
                                $table1      = $this->getTable('ves_megamenu_menu_log');
                                $where      = ['log_id = (?)' => $v['log_id'] ];
                                $this->getConnection()->delete($table1, $where);
                            }
                        }
                    }

                    $version = 1;
                    if(!empty($result)){
                        $version = (int)$result[0]['version'] + 1;
                    }

                    $select = $this->getConnection()->select()->from($this->getTable('ves_megamenu_item'))
                    ->where(
                        'menu_id = ?',
                        intval($menuId)
                        );
                    $menuItems = $this->getConnection()->fetchAll($select);
                    foreach ($menuItems as &$_item) {
                        unset($_item['id']);
                    }
                    $data['menu_items'] = base64_encode(serialize($menuItems));

                    $menuData = [
                    'menu_id'        => $menuId,
                    'version'        => $version,
                    'menu_data'      => base64_encode(serialize($data)),
                    'menu_structure' => $structure,
                    'note'           => 'Note',
                    'update_time'    => time()
                    ];
                    
                    $this->getConnection()->insert($table, $menuData);
                    $object->setData('current_version', $version);
                } catch (\Exception $e) {
                }
            }

            if(isset($data['revert_previous']) || isset($data['revert_next'])){
                $connection = $this->getConnection();
                $select = $connection->select()->from(
                    $this->getTable('ves_megamenu_menu_log')
                    )->where(
                    'menu_id = :menu_id'
                    )->where(
                    'version = :version'
                    );

                    if(isset($data['revert_previous'])){
                        $version = $data['revert_previous'];
                    }
                    if(isset($data['revert_next'])){
                        $version = $data['revert_next'];
                    }

                    $binds = [
                    ':menu_id' => (int)$object->getId(),
                    ':version' => $version
                    ];
                    $menu = $connection->fetchRow($select, $binds);
                    $menuData = unserialize(base64_decode($menu['menu_data']));
                    if(!empty($menuData)) {
                        $menuData['structure'] = unserialize(base64_decode($menu['menu_structure']));
                        $menuData['form_key'] = $form_key;
                        $itemsTable = $this->getTable('ves_megamenu_item');
                        $where = ['menu_id = ?' => (int)$object->getId()];
                        $this->getConnection()->delete($this->getTable('ves_megamenu_item'), $where);
                        if (isset($menuData['menu_items'])) {
                            $menuItems = unserialize(base64_decode($menuData['menu_items']));
                            $this->getConnection()->insertMultiple($itemsTable, $menuItems);
                            unset($menuData['menu_items']);
                        }
                        $menuData['current_version'] = $version;
                        $object->setData($menuData);
                    }
                }
            }

            if (!$this->getIsUniqueBlockToStores($object) && $object->getStatus()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('A menu alias with the same properties already exists in the selected store.')
                    );
            }
            return $this;
        }

        public function getIsUniqueBlockToStores(\Magento\Framework\Model\AbstractModel $object)
        {
            if ($this->_storeManager->hasSingleStore()) {
                $stores = [\Magento\Store\Model\Store::DEFAULT_STORE_ID];
            } else {
                $stores = (array)$object->getData('stores');
            }

            $select = $this->getConnection()->select()->from(
                ['cb' => $this->getMainTable()]
                )->join(
                ['cbs' => $this->getTable('ves_megamenu_menu_store')],
                'cb.menu_id = cbs.menu_id',
                []
                )->where(
                'cb.alias = ?',
                $object->getData('alias')
                )->where(
                'cbs.store_id IN (?)',
                $stores
                );

                if ($object->getId()) {
                    $select->where('cb.menu_id <> ?', intval($object->getId()));
                }

                if ($this->getConnection()->fetchRow($select)) {
                    return false;
                }

                return true;
            }


            protected function _getLoadSelect($field, $value, $object)
            {
                $mainTable = $this->getMainTable();
                $field = $this->getConnection()->quoteIdentifier(sprintf('%s.%s', $this->getMainTable(), $field));
                $select = $this->getConnection()
                ->select();
                if($this->getStore() && $storeId=$this->getStore()->getId()){
                    $select->join(
                        ["cbs" => $this->getTable("ves_megamenu_menu_store")],
                        "{$mainTable}.menu_id = cbs.menu_id",
                        []
                        )
                    ->where(
                        "cbs.store_id IN (0,?)",
                        $storeId
                        );
                }
                $select->from($this->getMainTable())->where($field . '=?', $value);
                return $select;
            }
        }