<?php

namespace Tofex\Core\Model\Config\Source\Attribute;

use Magento\Eav\Model\Entity\Attribute;

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2022 Tofex UG (http://www.tofex.de)
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class AddressAttributeCode
    extends Address
{
    /**
     * @param Attribute $customerAttribute
     *
     * @return string
     */
    protected function getAttributeValue(Attribute $customerAttribute): string
    {
        return $customerAttribute->getAttributeCode();
    }
}
