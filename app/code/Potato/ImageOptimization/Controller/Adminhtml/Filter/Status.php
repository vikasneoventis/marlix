<?php

namespace Potato\ImageOptimization\Controller\Adminhtml\Filter;

use Potato\ImageOptimization\Controller\Adminhtml\Filter;

/**
 * Class Status
 */
class Status extends Filter
{
    /**
     * @return $this
     */
    public function execute()
    {
        $currentBookmark = $this->getCurrentBookmark();
        $params = $this->getRequest()->getParams();
        $currentConfig = $currentBookmark->getConfig();
        $currentConfig['current']['filters']['applied'] = $params;
        $currentBookmark->setConfig($this->jsonEncode->encode($currentConfig));
        $this->bookmarkRepository->save($currentBookmark);
        return $this->resultRedirectFactory->create()->setPath('po_image/image/index');
    }
}