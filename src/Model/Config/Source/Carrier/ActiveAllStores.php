<?php

namespace Tofex\Core\Model\Config\Source\Carrier;

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2022 Tofex UG (http://www.tofex.de)
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class ActiveAllStores
    extends Active
{
    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        $this->setAllStores(true);
        $this->setWithDefault(true);

        return parent::toOptionArray();
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $this->setAllStores(true);
        $this->setWithDefault(true);

        return parent::toArray();
    }
}
