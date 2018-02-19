<?php
/**
 * Created by mr.vjcspy@gmail.com - khoild@smartosc.com.
 * Date: 08/03/2017
 * Time: 11:04
 */

namespace SM\Performance\Helper;


use Magento\Framework\ObjectManagerInterface;

/**
 * Class RealtimeManager
 *
 * @package SM\Performance\Helper
 */
class RealtimeManager {

    static $CAN_SEND_REAL_TIME = true;
    static $USE_ASYNC          = true;

    const ORDER_ENTITY    = "orders";
    const PRODUCT_ENTITY  = "products";
    const CATEGORY_ENTITY = "category";
    const CUSTOMER_ENTITY = "customers";
    const CUSTOMER_GROUP  = "customerGroup";
    const SETTING_ENTITY  = "settings";
    const TAX_ENTITY      = "taxes";

    const TYPE_CHANGE_NEW    = 'new';
    const TYPE_CHANGE_UPDATE = 'update';
    const TYPE_CHANGE_REMOVE = 'remove';

    /**
     * @var \SM\Performance\Gateway\Sender
     */
    protected static $senderInstance;

    /**
     * @var bool
     */
    protected static $_useBatch = false;

    /**
     * @var array
     */
    protected static $_batchData = [];
    /**
     * @var \SM\XRetail\Model\Shell\Process
     */
    private $process;

    /**
     * RealtimeManager constructor.
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager,
        \SM\XRetail\Model\Shell\Process $process
    ) {
        $this->objectManager = $objectManager;
        $this->process       = $process;
    }

    /**
     * @param $entity
     * @param $entityId
     * @param $typeChange
     */
    public function trigger($entity, $entityId, $typeChange) {
        if (!RealtimeManager::$CAN_SEND_REAL_TIME) {
            return;
        }

        if (is_null(RealtimeManager::$senderInstance)) {
            RealtimeManager::$senderInstance = $this->objectManager->create('SM\Performance\Gateway\Sender');
        }
        if (!RealtimeManager::$_useBatch) {
            if (!self::$USE_ASYNC) {
                RealtimeManager::$senderInstance->sendMessages(
                    [
                        [
                            'entity'      => $entity,
                            'entity_id'   => $entityId,
                            'type_change' => $typeChange
                        ]
                    ]);
            }
            else {
                $this->process
                    ->setCommand(
                        "bin/magento retail:sendrealtime " . "'" . json_encode(
                            [
                                [
                                    'entity'      => $entity,
                                    'entity_id'   => $entityId,
                                    'type_change' => $typeChange
                                ]
                            ]) . "'")
                    ->start();
            }
        }
        else {
            $this->pushToBatch($entity, $entityId, $typeChange);
        }
    }

    /**
     * @return \SM\Performance\Gateway\Sender
     */
    public function getSenderInstance() {
        if (is_null(RealtimeManager::$senderInstance)) {
            RealtimeManager::$senderInstance = $this->objectManager->create('SM\Performance\Gateway\Sender');
        }

        return RealtimeManager::$senderInstance;
    }

    /**
     * @param $entity
     * @param $entityId
     * @param $typeChange
     *
     * @return $this
     */
    protected function pushToBatch($entity, $entityId, $typeChange) {
        RealtimeManager::$_batchData[] = [
            'entity'      => $entity,
            'entity_id'   => $entityId,
            'type_change' => $typeChange
        ];

        return $this;
    }

    /**
     * @return array
     */
    public function getBatchData() {
        return RealtimeManager::$_batchData;
    }

    /**
     * @return $this
     */
    public function processBatchData() {
        if (RealtimeManager::$_useBatch === true && !is_null(RealtimeManager::$senderInstance)) {
            RealtimeManager::$senderInstance->sendMessages($this->getBatchData());
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function useBatchData() {
        if (RealtimeManager::$_useBatch !== true) {
            RealtimeManager::$_useBatch  = true;
            RealtimeManager::$_batchData = [];
        }

        return $this;
    }

}