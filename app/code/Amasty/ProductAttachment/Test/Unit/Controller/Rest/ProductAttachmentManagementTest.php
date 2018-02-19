<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_ProductAttachment
 */


namespace Amasty\ProductAttachment\Test\Unit\Controller\Rest;

/**
 * Class ProductAttachmentManagementTest
 *
 * vendor/phpunit/phpunit/phpunit -c /var/www/sources/sumrak/dev21/dev/tests/api-functional/phpunit.xml app/code/Amasty/ProductAttachment/Test/Unit/Controller/Rest/ProductAttachmentManagementTest.php
 * @package Amasty\ProductAttachment\Test\Unit\Controller\Rest
 */
class ProductAttachmentManagementTest extends \Magento\TestFramework\TestCase\WebapiAbstract
{
    public function testGetById()
    {
        $itemId = 1;
        $item = $this->getById($itemId);
        $this->assertEquals('testFileName', $item['file_name'], "Item was retrieved unsuccessfully");
    }

    public function testSearch()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/productAttachment/search',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ]
        ];
        $requestData = ['searchCriteria'=>['product_id'=>2107]];
        $items = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertCount(1, $items, 'Items count not equals');
    }

    public function testAdd()
    {
        $item = $this->add(['product_id' => 2107, 'file_path'=>'filePath', 'file_name'=>'fileName1']);
        $this->assertGreaterThan(0,  $item['id']);

    }

    public function testSave()
    {
        $testFilePath = 'filePath2';
        $item = $this->add(['product_id' => 2107, 'file_path'=>'filePath1', 'file_name'=>'fileName1']);
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/productAttachment/'.$item['id'],
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::METHOD_PUT,
            ]
        ];
        $item['file_path'] = $testFilePath;
        $requestData = ['productAttachment'=> $item];
        $item = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertEquals($testFilePath, $item['file_path'], "Items don't equals");
    }

    public function testDelete()
    {
        $item = $this->add(['product_id' => 2107, 'file_path'=>'filePath', 'file_name'=>'fileName1']);
        $itemId = $item['id'];
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/productAttachment/'.$itemId,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::METHOD_DELETE,
            ]
        ];
        $requestData = ['attachmentId'=>$itemId];
        $result = $this->_webApiCall($serviceInfo, $requestData);
        $this->assertTrue($result, 'Error Deleting');

    }


    protected function getById($itemId)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/productAttachment/' . $itemId,
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_GET,
            ]
        ];
        $requestData = ['attachmentId' => $itemId];
        $item = $this->_webApiCall($serviceInfo, $requestData);
        return $item;
    }

    protected function add($data = ['product_id' => 2107, 'file_path'=>'filePath', 'file_name'=>'fileName1'])
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/productAttachment/',
                'httpMethod' => \Magento\Framework\Webapi\Rest\Request::HTTP_METHOD_POST,
            ]
        ];
        $requestData = ['productAttachment'=> $data];
        $item = $this->_webApiCall($serviceInfo, $requestData);

        return $item;
    }

}
