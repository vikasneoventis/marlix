<?php
namespace Netresearch\OPS\Test\Unit\Model\Payment;

    /*
     * To change this template, choose Tools | Templates
     * and open the template in the editor.
     */
/**
 * Description of KwixoCreditTest
 *
 * @author Sebastian Ertner
 */
class KwixoApresReceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
     */
    private $objectManager;

    /**
     * @var \Netresearch\OPS\Model\Payment\KwixoApresReception
     */
    private $model;

    /**
     * @var \Magento\Sales\Model\Order\Payment\Info
     */
    private $paymentInfo;

    /**
     * @var \Magento\Framework\App\Config
     */
    private $config;

    public function setUp()
    {
        parent::setUp();
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->paymentInfo   = $this->getMock('\Magento\Sales\Model\Order\Payment\Info', [], [], '', false, false);
        $this->config        = $this->getMock('\Magento\Framework\App\Config', [], [], '', false, false);
        $this->model         = $this->objectManager->getObject(
            '\Netresearch\OPS\Model\Payment\KwixoApresReception',
            ['scopeConfig' => $this->config]
        );
        $this->model->setInfoInstance($this->paymentInfo);
    }

    public function testGetOpsCode()
    {
        $this->paymentInfo->expects($this->any())
                          ->method('getAdditionalInformation')
                          ->will($this->returnValue('KWIXO_RNP'));
        $this->assertEquals('KWIXO_RNP', $this->model->getOpsCode());
    }

    public function testGetCode()
    {
        $this->assertEquals('ops_kwixoApresReception', $this->model->getCode());
    }

    public function testGetDeliveryDate()
    {
        $this->config->expects($this->any())->method('getValue')->will($this->onConsecutiveCalls(0, 5));
        $dateNow = date("Y-m-d");
        $this->assertEquals(
            $dateNow,
            $this->model->getEstimatedDeliveryDate('ops_kwixoApresReception')
        );
        $dateNowPlusFiveDays = strtotime($dateNow . "+ 5 days");
        $this->assertEquals(
            date("Y-m-d", $dateNowPlusFiveDays),
            $this->model->getEstimatedDeliveryDate('ops_kwixoApresReception')
        );
    }

    public function testGetFormBlockType()
    {
        $this->assertEquals('Netresearch\OPS\Block\Form\Kwixo\ApresReception', $this->model->getFormBlockType());
    }
}
