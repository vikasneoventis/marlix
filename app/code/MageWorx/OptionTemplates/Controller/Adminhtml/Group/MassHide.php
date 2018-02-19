<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionTemplates\Controller\Adminhtml\Group;

use MageWorx\OptionTemplates\Model\Group;

class MassHide extends MassAction
{
    /**
     * @var string
     */
    protected $successMessage = 'A total of %1 option templates have been hidden';

    /**
     * @var string
     */
    protected $errorMessage = 'An error occurred while hiding option templates';

    /**
     * @var bool
     */
    protected $isActive = false;

    /**
     * @param Group $group
     * @return $this
     */
    protected function doTheAction(Group $group)
    {
        $group->setIsActive($this->isActive);
        $group->save();

        return $this;
    }
}
