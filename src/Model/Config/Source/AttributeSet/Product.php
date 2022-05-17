<?php

namespace Tofex\Core\Model\Config\Source\AttributeSet;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory;
use Tofex\Core\Helper\EntityType;
use Tofex\Core\Model\Config\Source\AttributeSet;

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2022 Tofex UG (http://www.tofex.de)
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Product
    extends AttributeSet
{
    /**
     * @param EntityType        $entityTypeHelper
     * @param CollectionFactory $attributeSetCollectionFactory
     */
    public function __construct(EntityType $entityTypeHelper, CollectionFactory $attributeSetCollectionFactory)
    {
        parent::__construct($entityTypeHelper, $attributeSetCollectionFactory);

        $this->setProduct();
    }
}
