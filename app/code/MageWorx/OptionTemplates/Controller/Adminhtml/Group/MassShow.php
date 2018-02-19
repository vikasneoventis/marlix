<?php
/**
 * Copyright © 2016 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionTemplates\Controller\Adminhtml\Group;

class MassShow extends MassHide
{
    /**
     * @var string
     */
    protected $successMessage = 'A total of %1 option templates have been showed';

    /**
     * @var string
     */
    protected $errorMessage = 'An error occurred while showing option templates.';

    /**
     * @var bool
     */
    protected $isActive = true;
}
