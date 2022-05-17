<?php

namespace Tofex\Core\Helper;

use Exception;
use Magento\Catalog\Model\Product\Media\Config;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2022 Tofex UG (http://www.tofex.de)
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Product
{
    /** @var ProductFactory */
    protected $productFactory;

    /** @var \Magento\Catalog\Model\ResourceModel\ProductFactory */
    protected $productResourceFactory;

    /** @var CollectionFactory */
    protected $productCollectionFactory;

    /** @var Config */
    protected $productMediaConfig;

    /**
     * @param ProductFactory                                      $productFactory
     * @param \Magento\Catalog\Model\ResourceModel\ProductFactory $productResourceFactory
     * @param CollectionFactory                                   $productCollectionFactory
     * @param Config                                              $productMediaConfig
     */
    public function __construct(
        ProductFactory $productFactory,
        \Magento\Catalog\Model\ResourceModel\ProductFactory $productResourceFactory,
        CollectionFactory $productCollectionFactory,
        Config $productMediaConfig)
    {
        $this->productFactory = $productFactory;
        $this->productResourceFactory = $productResourceFactory;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productMediaConfig = $productMediaConfig;
    }

    /**
     * @return \Magento\Catalog\Model\Product
     */
    public function newProduct(): \Magento\Catalog\Model\Product
    {
        return $this->productFactory->create();
    }

    /**
     * @param int      $productId
     * @param int|null $storeId
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function loadProduct(int $productId, int $storeId = null): \Magento\Catalog\Model\Product
    {
        $product = $this->newProduct();

        if ( ! empty($storeId)) {
            $product->setStoreId($storeId);
        }

        $this->productResourceFactory->create()->load($product, $productId);

        return $product;
    }

    /**
     * @param string   $productSku
     * @param int|null $storeId
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function loadProductBySku(string $productSku, int $storeId = null): \Magento\Catalog\Model\Product
    {
        $product = $this->newProduct();

        $productId = $product->getIdBySku($productSku);

        if ( ! empty($storeId)) {
            $product->setDataUsingMethod('store_id', $storeId);
        }

        $this->productResourceFactory->create()->load($product, $productId);

        return $product;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     *
     * @throws Exception
     */
    public function saveProduct(\Magento\Catalog\Model\Product $product)
    {
        $this->productResourceFactory->create()->save($product);
    }

    /**
     * @return Collection
     */
    public function getProductCollection(): Collection
    {
        return $this->productCollectionFactory->create();
    }

    /**
     * @return Config
     */
    public function getProductMediaConfig(): Config
    {
        return $this->productMediaConfig;
    }
}
