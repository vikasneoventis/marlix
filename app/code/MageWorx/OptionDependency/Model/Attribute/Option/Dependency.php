<?php
/**
 * Copyright © 2017 MageWorx. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageWorx\OptionDependency\Model\Attribute\Option;

use MageWorx\OptionDependency\Model\Attribute\Dependency as DefaultDependency;

class Dependency extends DefaultDependency
{
    /**
     * {@inheritdoc}
     */
    public function applyData($entity, $options)
    {
        $this->entity = $entity;
        $this->options = $options;
    }
}
