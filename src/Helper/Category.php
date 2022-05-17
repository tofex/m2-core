<?php

namespace Tofex\Core\Helper;

use Exception;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2022 Tofex UG (http://www.tofex.de)
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Category
{
    /** @var CategoryFactory */
    protected $categoryFactory;

    /** @var \Magento\Catalog\Model\ResourceModel\CategoryFactory */
    protected $categoryResourceFactory;

    /** @var CollectionFactory */
    protected $categoryCollectionFactory;

    /**
     * @param CategoryFactory                                                 $categoryFactory
     * @param \Magento\Catalog\Model\ResourceModel\CategoryFactory            $categoryResourceFactory
     * @param CollectionFactory $categoryCollectionFactory
     */
    public function __construct(
        CategoryFactory $categoryFactory,
        \Magento\Catalog\Model\ResourceModel\CategoryFactory $categoryResourceFactory,
        CollectionFactory $categoryCollectionFactory)
    {
        $this->categoryFactory = $categoryFactory;
        $this->categoryResourceFactory = $categoryResourceFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
    }

    /**
     * @return \Magento\Catalog\Model\Category
     */
    public function newCategory(): \Magento\Catalog\Model\Category
    {
        return $this->categoryFactory->create();
    }

    /**
     * @param int      $categoryId
     * @param int|null $storeId
     *
     * @return \Magento\Catalog\Model\Category
     */
    public function loadCategory(int $categoryId, int $storeId = null): \Magento\Catalog\Model\Category
    {
        $category = $this->newCategory();

        if ( ! empty($storeId)) {
            $category->setStoreId($storeId);
        }

        $this->categoryResourceFactory->create()->load($category, $categoryId);

        return $category;
    }

    /**
     * @param \Magento\Catalog\Model\Category $category
     *
     * @throws Exception
     */
    public function saveCategory(\Magento\Catalog\Model\Category $category)
    {
        $this->categoryResourceFactory->create()->save($category);
    }

    /**
     * @return Collection
     */
    public function getCategoryCollection(): Collection
    {
        return $this->categoryCollectionFactory->create();
    }
}
