<?php

namespace Tofex\Core\Helper;

use Exception;
use Magento\Catalog\Api\CategoryAttributeRepositoryInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Eav\Model\Entity\Attribute\Set;
use Magento\Eav\Model\Entity\Attribute\SetFactory;
use Magento\Eav\Model\Entity\AttributeFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2022 Tofex UG (http://www.tofex.de)
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Attribute
{
    /** @var AttributeFactory */
    protected $attributeFactory;

    /** @var \Magento\Eav\Model\ResourceModel\Entity\AttributeFactory */
    protected $attributeResourceFactory;

    /** @var CollectionFactory */
    protected $attributeCollectionFactory;

    /** @var SetFactory */
    protected $attributeSetFactory;

    /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\SetFactory */
    protected $attributeSetResourceFactory;

    /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory */
    protected $attributeSetCollectionFactory;

    /** @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory */
    protected $attributeGroupCollectionFactory;

    /** @var ProductAttributeRepositoryInterface */
    protected $productAttributeRepository;

    /** @var CategoryAttributeRepositoryInterface */
    protected $categoryAttributeRepository;

    /** @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory */
    protected $productAttributeCollectionFactory;

    /**
     * @param AttributeFactory                                                          $attributeFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\AttributeFactory                  $attributeResourceFactory
     * @param CollectionFactory                                                         $attributeCollectionFactory
     * @param SetFactory                                                                $attributeSetFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\SetFactory              $attributeSetResourceFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory   $attributeSetCollectionFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory $attributeGroupCollectionFactory
     * @param ProductAttributeRepositoryInterface                                       $productAttributeRepository
     * @param CategoryAttributeRepositoryInterface                                      $categoryAttributeRepository
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory  $productAttributeCollectionFactory
     */
    public function __construct(
        AttributeFactory $attributeFactory,
        \Magento\Eav\Model\ResourceModel\Entity\AttributeFactory $attributeResourceFactory,
        CollectionFactory $attributeCollectionFactory,
        SetFactory $attributeSetFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\SetFactory $attributeSetResourceFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $attributeSetCollectionFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory $attributeGroupCollectionFactory,
        ProductAttributeRepositoryInterface $productAttributeRepository,
        CategoryAttributeRepositoryInterface $categoryAttributeRepository,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $productAttributeCollectionFactory)
    {
        $this->attributeFactory = $attributeFactory;
        $this->attributeResourceFactory = $attributeResourceFactory;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->attributeSetResourceFactory = $attributeSetResourceFactory;
        $this->attributeSetCollectionFactory = $attributeSetCollectionFactory;
        $this->attributeGroupCollectionFactory = $attributeGroupCollectionFactory;
        $this->productAttributeRepository = $productAttributeRepository;
        $this->categoryAttributeRepository = $categoryAttributeRepository;
        $this->productAttributeCollectionFactory = $productAttributeCollectionFactory;
    }

    /**
     * @return Collection
     */
    public function getAttributeCollection(): Collection
    {
        return $this->attributeCollectionFactory->create();
    }

    /**
     * @return \Magento\Eav\Model\Entity\Attribute
     */
    public function newAttribute(): \Magento\Eav\Model\Entity\Attribute
    {
        return $this->attributeFactory->create();
    }

    /**
     * @param int $attributeId
     *
     * @return \Magento\Eav\Model\Entity\Attribute
     */
    public function loadAttribute(int $attributeId): \Magento\Eav\Model\Entity\Attribute
    {
        $attribute = $this->newAttribute();

        $this->attributeResourceFactory->create()->load($attribute, $attributeId, 'attribute_id');

        return $attribute;
    }

    /**
     * @param \Magento\Eav\Model\Entity\Attribute $attribute
     *
     * @throws Exception
     */
    public function deleteAttribute(\Magento\Eav\Model\Entity\Attribute $attribute)
    {
        $this->attributeResourceFactory->create()->delete($attribute);
    }

    /**
     * @return \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection
     */
    public function getAttributeSetCollection(): \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\Collection
    {
        return $this->attributeSetCollectionFactory->create();
    }

    /**
     * @return Set
     */
    public function newAttributeSet(): Set
    {
        return $this->attributeSetFactory->create();
    }

    /**
     * @param int $attributeSetId
     *
     * @return Set
     */
    public function loadAttributeSet(int $attributeSetId): Set
    {
        $attributeSet = $this->newAttributeSet();

        $this->attributeSetResourceFactory->create()->load($attributeSet, $attributeSetId, 'attribute_set_id');

        return $attributeSet;
    }

    /**
     * @param Set $attributeSet
     *
     * @throws Exception
     */
    public function deleteAttributeSet(Set $attributeSet)
    {
        $this->attributeSetResourceFactory->create()->delete($attributeSet);
    }

    /**
     * @return \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\Collection
     */
    public function getAttributeGroupCollection(): \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\Collection
    {
        return $this->attributeGroupCollectionFactory->create();
    }

    /**
     * @param \Magento\Eav\Model\Entity\Attribute $attribute
     *
     * @return \Magento\Catalog\Model\ResourceModel\Eav\Attribute|null
     * @throws NoSuchEntityException
     */
    public function loadCatalogEavAttribute(
        \Magento\Eav\Model\Entity\Attribute $attribute): ?\Magento\Catalog\Model\ResourceModel\Eav\Attribute
    {
        $entityTypeCode = $attribute->getEntityType()->getEntityTypeCode();

        $catalogEavAttribute = null;

        if ($entityTypeCode == 'catalog_product') {
            /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $catalogEavAttribute */
            $catalogEavAttribute = $this->productAttributeRepository->get($attribute->getAttributeCode());
        } else if ($entityTypeCode == 'catalog_category') {
            /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $catalogEavAttribute */
            $catalogEavAttribute = $this->categoryAttributeRepository->get($attribute->getAttributeCode());
        }

        return $catalogEavAttribute;
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection
     */
    public function getProductAttributeCollection(): \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection
    {
        return $this->productAttributeCollectionFactory->create();
    }
}
