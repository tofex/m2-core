<?php

namespace Tofex\Core\Model\Config\Source\Attribute;

use Tofex\Core\Model\Config\Source\Attribute;

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2022 Tofex UG (http://www.tofex.de)
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Multiselect
    extends Attribute
{
    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        $this->setAddPleaseSelect(false);

        return parent::toOptionArray();
    }
}
