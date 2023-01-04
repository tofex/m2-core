<?php

namespace Tofex\Core\Model\Config\Source;

use Magento\Cms\Model\Block;
use Magento\Cms\Model\ResourceModel\Block\CollectionFactory;
use Magento\Framework\Data\Collection;
use Magento\Framework\Option\ArrayInterface;

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2022 Tofex UG (http://www.tofex.de)
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class CmsBlock
    implements ArrayInterface
{
    /** @var CollectionFactory */
    protected $collectionFactory;

    /**
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(CollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return array
     */
    public function getAllOptions(): array
    {
        return $this->toOptionArray();
    }

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        $cmsBlockCollection = $this->collectionFactory->create();

        $cmsBlockCollection->addOrder('title', Collection::SORT_ORDER_ASC);

        $options = [['value' => '', 'label' => __('--Please Select--')]];

        /** @var Block $cmsBlock */
        foreach ($cmsBlockCollection as $cmsBlock) {
            $options[] = [
                'value' => $cmsBlock->getId(),
                'label' => $cmsBlock->getTitle()
            ];
        }

        return $options;
    }

    /**
     * @return array
     */
    public function toOptions(): array
    {
        $cmsBlockCollection = $this->collectionFactory->create();

        $cmsBlockCollection->addOrder('title', Collection::SORT_ORDER_ASC);

        $options = [];

        /** @var Block $cmsBlock */
        foreach ($cmsBlockCollection as $cmsBlock) {
            $options[ $cmsBlock->getId() ] = $cmsBlock->getTitle();
        }

        return $options;
    }
}
