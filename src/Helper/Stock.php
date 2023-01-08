<?php /** @noinspection PhpDeprecationInspection */

namespace Tofex\Core\Helper;

use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Item\Collection;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Item\CollectionFactory;
use Magento\CatalogInventory\Model\ResourceModel\Stock\StatusFactory;
use Magento\CatalogInventory\Model\Stock\Item;
use Magento\CatalogInventory\Model\Stock\ItemFactory;
use Magento\CatalogInventory\Model\Stock\StockItemRepository;
use Magento\Framework\DB\QueryBuilderFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Tofex\Help\Variables;

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2022 Tofex UG (http://www.tofex.de)
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Stock
{
    /** @var Variables */
    protected $variableHelper;

    /** @var Stores */
    protected $storeHelper;

    /** @var ItemFactory */
    protected $stockItemFactory;

    /** @var \Magento\CatalogInventory\Model\ResourceModel\Stock\ItemFactory */
    protected $stockItemResourceFactory;

    /** @var CollectionFactory */
    protected $stockItemCollectionFactory;

    /** @var StockItemRepository */
    protected $stockItemRepository;

    /** @var StockItemCriteriaInterfaceFactory */
    protected $stockItemCriteriaInterfaceFactory;

    /** @var QueryBuilderFactory */
    protected $queryBuilderFactory;

    /** @var StatusFactory */
    protected $stockStatusFactory;

    /**
     * @param Variables                                                       $variableHelper
     * @param Stores                                                          $storeHelper
     * @param ItemFactory                                                     $stockItemFactory
     * @param \Magento\CatalogInventory\Model\ResourceModel\Stock\ItemFactory $stockItemResourceFactory
     * @param CollectionFactory                                               $stockItemCollectionFactory
     * @param StockItemRepository                                             $stockItemRepository
     * @param StockItemCriteriaInterfaceFactory                               $stockItemCriteriaInterfaceFactory
     * @param QueryBuilderFactory                                             $queryBuilderFactory
     * @param StatusFactory                                                   $stockStatusFactory
     */
    public function __construct(
        Variables $variableHelper,
        Stores $storeHelper,
        ItemFactory $stockItemFactory,
        \Magento\CatalogInventory\Model\ResourceModel\Stock\ItemFactory $stockItemResourceFactory,
        CollectionFactory $stockItemCollectionFactory,
        StockItemRepository $stockItemRepository,
        StockItemCriteriaInterfaceFactory $stockItemCriteriaInterfaceFactory,
        QueryBuilderFactory $queryBuilderFactory,
        StatusFactory $stockStatusFactory)
    {
        $this->variableHelper = $variableHelper;
        $this->storeHelper = $storeHelper;

        $this->stockItemFactory = $stockItemFactory;
        $this->stockItemResourceFactory = $stockItemResourceFactory;
        $this->stockItemCollectionFactory = $stockItemCollectionFactory;
        $this->stockItemRepository = $stockItemRepository;
        $this->stockItemCriteriaInterfaceFactory = $stockItemCriteriaInterfaceFactory;
        $this->queryBuilderFactory = $queryBuilderFactory;
        $this->stockStatusFactory = $stockStatusFactory;
    }

    /**
     * @return Item
     */
    public function newStockItem(): Item
    {
        return $this->stockItemFactory->create();
    }

    /**
     * @param int $stockItemId
     *
     * @return StockItemInterface
     * @throws NoSuchEntityException
     */
    public function loadStockItem(int $stockItemId): StockItemInterface
    {
        return $this->stockItemRepository->get($stockItemId);
    }

    /**
     * @param int $productId
     * @param int $stockId
     *
     * @return Item
     */
    public function loadStockItemByProduct(int $productId, int $stockId): Item
    {
        $stockItem = $this->newStockItem();

        $this->stockItemResourceFactory->create()->loadByProductId($stockItem, $productId, $stockId);

        return $stockItem;
    }

    /**
     * @param Item $stockItem
     *
     * @throws CouldNotSaveException
     */
    public function saveStockItem(Item $stockItem)
    {
        $this->stockItemRepository->save($stockItem);
    }

    /**
     * @param array $productIds
     * @param null  $storeId
     *
     * @return Collection
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getStockItemCollection(
        array $productIds = [],
        $storeId = null): Collection
    {
        if ( ! $this->variableHelper->isEmpty($productIds) || ! $this->variableHelper->isEmpty($storeId)) {
            $criteria = $this->stockItemCriteriaInterfaceFactory->create();

            if ( ! $this->variableHelper->isEmpty($productIds)) {
                $criteria->setProductsFilter($productIds);
            }

            if ( ! $this->variableHelper->isEmpty($storeId)) {
                $criteria->setScopeFilter($this->storeHelper->getStore($storeId)->getWebsiteId());
            }

            $queryBuilder = $this->queryBuilderFactory->create();

            $queryBuilder->setCriteria($criteria);
            $queryBuilder->setResource($this->stockItemResourceFactory->create());

            $query = $queryBuilder->create();

            return $this->stockItemCollectionFactory->create(['query' => $query]);
        } else {
            return $this->stockItemCollectionFactory->create();
        }
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection
     */
    public function addInStock(\Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection)
    {
        $stockStatusResource = $this->stockStatusFactory->create();

        $stockStatusResource->addStockDataToCollection($productCollection, true);

        $productCollection->setFlag('has_stock_status_filter', true);
    }
}
