<?php

namespace Tofex\Core\Helper;

use Exception;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Media\Config;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use Psr\Log\LoggerInterface;
use Tofex\Help\Arrays;

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2022 Tofex UG (http://www.tofex.de)
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Product
{
    /** @var Arrays */
    protected $arrayHelper;

    /** @var Attribute */
    protected $attributeHelper;

    /** @var Database */
    protected $databaseHelper;

    /** @var LoggerInterface */
    protected $logging;

    /** @var ProductFactory */
    protected $productFactory;

    /** @var \Magento\Catalog\Model\ResourceModel\ProductFactory */
    protected $productResourceFactory;

    /** @var CollectionFactory */
    protected $productCollectionFactory;

    /** @var Config */
    protected $productMediaConfig;

    /**
     * @param Arrays                                              $arrayHelper
     * @param Attribute                                           $attributeHelper
     * @param Database                                            $databaseHelper
     * @param LoggerInterface                                     $logging
     * @param ProductFactory                                      $productFactory
     * @param \Magento\Catalog\Model\ResourceModel\ProductFactory $productResourceFactory
     * @param CollectionFactory                                   $productCollectionFactory
     * @param Config                                              $productMediaConfig
     */
    public function __construct(
        Arrays $arrayHelper,
        Attribute $attributeHelper,
        Database $databaseHelper,
        LoggerInterface $logging,
        ProductFactory $productFactory,
        \Magento\Catalog\Model\ResourceModel\ProductFactory $productResourceFactory,
        CollectionFactory $productCollectionFactory,
        Config $productMediaConfig)
    {
        $this->arrayHelper = $arrayHelper;
        $this->attributeHelper = $attributeHelper;
        $this->databaseHelper = $databaseHelper;

        $this->logging = $logging;
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

    /**
     * @param AdapterInterface $dbAdapter
     * @param array            $parentIds
     * @param bool             $excludeInactive
     * @param bool             $excludeOutOfStock
     * @param bool             $maintainAssociation
     * @param bool             $useSuperLink
     * @param bool             $includeParents
     * @param int|null         $storeId
     *
     * @return array
     * @throws Exception
     */
    public function getChildIds(
        AdapterInterface $dbAdapter,
        array $parentIds,
        bool $excludeInactive = false,
        bool $excludeOutOfStock = false,
        bool $maintainAssociation = false,
        bool $useSuperLink = true,
        bool $includeParents = false,
        int $storeId = null): array
    {
        $this->logging->debug(sprintf('Searching child ids for parent id(s): %s', implode(', ', $parentIds)));

        $childIdQuery =
            $this->getChildIdQuery($dbAdapter, $parentIds, $excludeInactive, $useSuperLink, $includeParents, $storeId);

        if ($excludeOutOfStock) {
            $tableName = $this->databaseHelper->getTableName($useSuperLink ? 'catalog_product_super_link' :
                'catalog_product_relation');
            $childColumnName = $useSuperLink ? 'product_id' : 'child_id';

            $childIdQuery->join(['stock_item' => $this->databaseHelper->getTableName('cataloginventory_stock_item')],
                sprintf('%s = %s', $dbAdapter->quoteIdentifier('stock_item.product_id'),
                    $dbAdapter->quoteIdentifier(sprintf('%s.%s', $tableName, $childColumnName))), []);

            $childIdQuery->where($dbAdapter->prepareSqlCondition($dbAdapter->quoteIdentifier('stock_item.is_in_stock'),
                ['eq' => 1]), null, Select::TYPE_CONDITION);
        }

        $queryResult = $this->databaseHelper->fetchAssoc($childIdQuery, $dbAdapter);

        if ($maintainAssociation) {
            $childIds = [];

            foreach ($queryResult as $childId => $queryRow) {
                $childIds[ (int)$childId ] = (int)$this->arrayHelper->getValue($queryRow, 'parent_id');
            }
        } else {
            $childIds = array_keys($queryResult);

            $childIds = array_map('intval', $childIds);
        }

        $this->logging->debug(sprintf('Found %d child id(s)', count($childIds)));

        return $childIds;
    }

    /**
     * @param AdapterInterface $dbAdapter
     * @param array            $parentIds
     * @param bool             $excludeInactive
     * @param bool             $useSuperLink
     * @param bool             $includeParents
     * @param int|null         $storeId
     *
     * @return Select
     * @throws Exception
     */
    public function getChildIdQuery(
        AdapterInterface $dbAdapter,
        array $parentIds,
        bool $excludeInactive = false,
        bool $useSuperLink = true,
        bool $includeParents = false,
        int $storeId = null): Select
    {
        $tableName = $this->databaseHelper->getTableName($useSuperLink ? 'catalog_product_super_link' :
            'catalog_product_relation');
        $childColumnName = $useSuperLink ? 'product_id' : 'child_id';

        $childIdQuery = $dbAdapter->select()->from([$tableName], [
            $childColumnName,
            'parent_id'
        ]);

        $childIdQuery->where($dbAdapter->prepareSqlCondition($dbAdapter->quoteIdentifier(sprintf('%s.parent_id',
            $tableName)), ['in' => $parentIds]), null, Select::TYPE_CONDITION);

        if ($includeParents) {
            // in case child ids are included in the parent id list
            $childIdQuery->orWhere($dbAdapter->prepareSqlCondition(sprintf('%s.%s', $tableName, $childColumnName),
                ['in' => $parentIds]), null, Select::TYPE_CONDITION);
        }

        if ($excludeInactive) {
            $statusAttribute = $this->attributeHelper->getAttribute(\Magento\Catalog\Model\Product::ENTITY, 'status');

            if (empty($storeId)) {
                $childIdQuery->join(['status0' => $statusAttribute->getBackend()->getTable()],
                    $dbAdapter->quoteInto(sprintf('%s = %s AND %s = ? AND %s = 0',
                        $dbAdapter->quoteIdentifier('status0.entity_id'),
                        $dbAdapter->quoteIdentifier(sprintf('%s.%s', $tableName, $childColumnName)),
                        $dbAdapter->quoteIdentifier('status0.attribute_id'),
                        $dbAdapter->quoteIdentifier('status0.store_id')), $statusAttribute->getAttributeId()), []);

                $childIdQuery->where($dbAdapter->prepareSqlCondition($dbAdapter->quoteIdentifier('status0.value'),
                    ['eq' => Status::STATUS_ENABLED]), null, Select::TYPE_CONDITION);
            } else {
                $childIdQuery->joinLeft(['status0' => $statusAttribute->getBackend()->getTable()],
                    $dbAdapter->quoteInto(sprintf('%s = %s AND %s = ? AND %s = 0',
                        $dbAdapter->quoteIdentifier('status0.entity_id'),
                        $dbAdapter->quoteIdentifier(sprintf('%s.%s', $tableName, $childColumnName)),
                        $dbAdapter->quoteIdentifier('status0.attribute_id'),
                        $dbAdapter->quoteIdentifier('status0.store_id')), $statusAttribute->getAttributeId()), []);

                $tableAlias = sprintf('status_%d', $storeId);

                $childIdQuery->joinLeft([$tableAlias => $statusAttribute->getBackend()->getTable()],
                    sprintf('%s = %s AND %s = %d AND %s = %d',
                        $dbAdapter->quoteIdentifier(sprintf('%s.entity_id', $tableAlias)),
                        $dbAdapter->quoteIdentifier(sprintf('%s.%s', $tableName, $childColumnName)),
                        $dbAdapter->quoteIdentifier(sprintf('%s.attribute_id', $tableAlias)),
                        $statusAttribute->getAttributeId(),
                        $dbAdapter->quoteIdentifier(sprintf('%s.store_id', $tableAlias)), $storeId), []);

                $childIdQuery->where($dbAdapter->getIfNullSql($dbAdapter->quoteIdentifier(sprintf('%s.value',
                        $tableAlias)), $dbAdapter->quoteIdentifier('status0.value')) . ' = ?', Status::STATUS_ENABLED);
            }
        }

        return $childIdQuery;
    }

    /**
     * @param AdapterInterface $dbAdapter
     * @param array            $parentIds
     * @param bool             $excludeInactive
     * @param bool             $excludeOutOfStock
     * @param bool             $maintainAssociation
     * @param int|null         $storeId
     *
     * @return array
     * @throws Exception
     */
    public function getBundledIds(
        AdapterInterface $dbAdapter,
        array $parentIds,
        bool $excludeInactive = false,
        bool $excludeOutOfStock = false,
        bool $maintainAssociation = false,
        int $storeId = null): array
    {
        $this->logging->debug(sprintf('Searching bundled ids for parent id(s): %s', implode(', ', $parentIds)));

        $buildIdQuery = $this->getBundledIdQuery($dbAdapter, $parentIds, $excludeInactive, $storeId);

        if ($excludeOutOfStock) {
            $tableName = $this->databaseHelper->getTableName('catalog_product_bundle_selection');

            $buildIdQuery->join(['stock_item' => $this->databaseHelper->getTableName('cataloginventory_stock_item')],
                sprintf('%s = %s', $dbAdapter->quoteIdentifier('stock_item.product_id'),
                    $dbAdapter->quoteIdentifier(sprintf('%s.%s', $tableName, 'product_id'))), []);

            $buildIdQuery->where($dbAdapter->prepareSqlCondition($dbAdapter->quoteIdentifier('stock_item.is_in_stock'),
                ['eq' => 1]), null, Select::TYPE_CONDITION);
        }

        $queryResult = $this->databaseHelper->fetchAssoc($buildIdQuery, $dbAdapter);

        if ($maintainAssociation) {
            $childIds = [];

            foreach ($queryResult as $childId => $queryRow) {
                $childIds[ (int)$childId ] = (int)$this->arrayHelper->getValue($queryRow, 'parent_product_id');
            }
        } else {
            $childIds = array_keys($queryResult);

            $childIds = array_map('intval', $childIds);
        }

        $this->logging->debug(sprintf('Found %d bundled id(s)', count($childIds)));

        return $childIds;
    }

    /**
     * @param AdapterInterface $dbAdapter
     * @param array            $parentIds
     * @param bool             $excludeInactive
     * @param int|null         $storeId
     *
     * @return Select
     * @throws Exception
     */
    public function getBundledIdQuery(
        AdapterInterface $dbAdapter,
        array $parentIds,
        bool $excludeInactive = false,
        int $storeId = null): Select
    {
        $tableName = $this->databaseHelper->getTableName('catalog_product_bundle_selection');

        $bundleIdQuery = $dbAdapter->select()->from([$tableName], [
            'product_id',
            'parent_product_id'
        ]);

        $bundleIdQuery->where($dbAdapter->prepareSqlCondition($dbAdapter->quoteIdentifier(sprintf('%s.parent_product_id',
            $tableName)), ['in' => $parentIds]), null, Select::TYPE_CONDITION);

        if ($excludeInactive) {
            $statusAttribute = $this->attributeHelper->getAttribute(\Magento\Catalog\Model\Product::ENTITY, 'status');

            if (empty($storeId)) {
                $bundleIdQuery->join(['status_0' => $statusAttribute->getBackend()->getTable()],
                    $dbAdapter->quoteInto(sprintf('%s = %s AND %s = ? AND %s = 0',
                        $dbAdapter->quoteIdentifier('status_0.entity_id'),
                        $dbAdapter->quoteIdentifier(sprintf('%s.%s', $tableName, 'product_id')),
                        $dbAdapter->quoteIdentifier('status_0.attribute_id'),
                        $dbAdapter->quoteIdentifier('status_0.store_id')), $statusAttribute->getAttributeId()), []);

                $bundleIdQuery->where($dbAdapter->prepareSqlCondition($dbAdapter->quoteIdentifier('status_0.value'),
                    ['eq' => Status::STATUS_ENABLED]), null, Select::TYPE_CONDITION);
            } else {
                $bundleIdQuery->joinLeft(['status_0' => $statusAttribute->getBackend()->getTable()],
                    $dbAdapter->quoteInto(sprintf('%s = %s AND %s = ? AND %s = 0',
                        $dbAdapter->quoteIdentifier('status_0.entity_id'),
                        $dbAdapter->quoteIdentifier(sprintf('%s.%s', $tableName, 'product_id')),
                        $dbAdapter->quoteIdentifier('status_0.attribute_id'),
                        $dbAdapter->quoteIdentifier('status_0.store_id')), $statusAttribute->getAttributeId()), []);

                $tableAlias = sprintf('status_%d', $storeId);

                $bundleIdQuery->joinLeft([$tableAlias => $statusAttribute->getBackend()->getTable()],
                    sprintf('%s = %s AND %s = %d AND %s = %d',
                        $dbAdapter->quoteIdentifier(sprintf('%s.entity_id', $tableAlias)),
                        $dbAdapter->quoteIdentifier(sprintf('%s.%s', $tableName, 'product_id')),
                        $dbAdapter->quoteIdentifier(sprintf('%s.attribute_id', $tableAlias)),
                        $statusAttribute->getAttributeId(),
                        $dbAdapter->quoteIdentifier(sprintf('%s.store_id', $tableAlias)), $storeId), []);

                $bundleIdQuery->where($dbAdapter->getIfNullSql($dbAdapter->quoteIdentifier(sprintf('%s.value',
                        $tableAlias)), $dbAdapter->quoteIdentifier('status_0.value')) . ' = ?', Status::STATUS_ENABLED);
            }
        }

        return $bundleIdQuery;
    }

    /**
     * @param AdapterInterface $dbAdapter
     * @param array            $parentIds
     * @param bool             $excludeInactive
     * @param bool             $excludeOutOfStock
     * @param bool             $maintainAssociation
     * @param bool             $includeParents
     * @param int|null         $storeId
     *
     * @return array
     * @throws Exception
     */
    public function getGroupedIds(
        AdapterInterface $dbAdapter,
        array $parentIds,
        bool $excludeInactive = false,
        bool $excludeOutOfStock = false,
        bool $maintainAssociation = false,
        bool $includeParents = false,
        int $storeId = null): array
    {
        return $this->getChildIds($dbAdapter, $parentIds, $excludeInactive, $excludeOutOfStock, $maintainAssociation,
            false, $includeParents, $storeId);
    }
}
