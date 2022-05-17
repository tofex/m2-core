<?php

namespace Tofex\Core\Model\Config\Source\Payment;

use Magento\Framework\Exception\LocalizedException;

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2022 Tofex UG (http://www.tofex.de)
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class ActiveMethodsAllStores
    extends ActiveMethods
{
    /**
     * @return array
     * @throws LocalizedException
     */
    public function toOptionArray(): array
    {
        $this->setAllStores(true);
        $this->setWithDefault(true);

        return parent::toOptionArray();
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    public function toOptions(): array
    {
        $this->setAllStores(true);
        $this->setWithDefault(true);

        return parent::toOptions();
    }
}
