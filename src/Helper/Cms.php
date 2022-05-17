<?php

namespace Tofex\Core\Helper;

use Exception;
use Magento\Cms\Model\Block;
use Magento\Cms\Model\BlockFactory;
use Magento\Cms\Model\Page;
use Magento\Cms\Model\PageFactory;
use Magento\Cms\Model\ResourceModel\Page\Collection;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory;
use Tofex\Help\Variables;

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2022 Tofex UG (http://www.tofex.de)
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Cms
{
    /** @var Variables */
    protected $variableHelper;

    /** @var PageFactory */
    protected $cmsPageFactory;

    /** @var \Magento\Cms\Model\ResourceModel\PageFactory */
    protected $cmsPageResourceFactory;

    /** @var CollectionFactory */
    protected $cmsPageCollectionFactory;

    /** @var BlockFactory */
    protected $cmsBlockFactory;

    /** @var \Magento\Cms\Model\ResourceModel\BlockFactory */
    protected $cmsBlockResourceFactory;

    /** @var \Magento\Cms\Model\ResourceModel\Block\CollectionFactory */
    protected $cmsBlockCollectionFactory;

    /**
     * @param Variables                                                $variableHelper
     * @param PageFactory                                              $cmsPageFactory
     * @param \Magento\Cms\Model\ResourceModel\PageFactory             $cmsPageResourceFactory
     * @param CollectionFactory                                        $cmsPageCollectionFactory
     * @param BlockFactory                                             $cmsBlockFactory
     * @param \Magento\Cms\Model\ResourceModel\BlockFactory            $cmsBlockResourceFactory
     * @param \Magento\Cms\Model\ResourceModel\Block\CollectionFactory $cmsBlockCollectionFactory
     */
    public function __construct(
        Variables $variableHelper,
        PageFactory $cmsPageFactory,
        \Magento\Cms\Model\ResourceModel\PageFactory $cmsPageResourceFactory,
        CollectionFactory $cmsPageCollectionFactory,
        BlockFactory $cmsBlockFactory,
        \Magento\Cms\Model\ResourceModel\BlockFactory $cmsBlockResourceFactory,
        \Magento\Cms\Model\ResourceModel\Block\CollectionFactory $cmsBlockCollectionFactory)
    {
        $this->variableHelper = $variableHelper;

        $this->cmsPageFactory = $cmsPageFactory;
        $this->cmsPageResourceFactory = $cmsPageResourceFactory;
        $this->cmsPageCollectionFactory = $cmsPageCollectionFactory;
        $this->cmsBlockFactory = $cmsBlockFactory;
        $this->cmsBlockResourceFactory = $cmsBlockResourceFactory;
        $this->cmsBlockCollectionFactory = $cmsBlockCollectionFactory;
    }

    /**
     * @return Page
     */
    public function newCmsPage(): Page
    {
        return $this->cmsPageFactory->create();
    }

    /**
     * @param int $cmsPageId
     *
     * @return Page
     */
    public function loadCmsPage(int $cmsPageId): Page
    {
        $cmsPage = $this->newCmsPage();

        $this->cmsPageResourceFactory->create()->load($cmsPage, $cmsPageId);

        return $cmsPage;
    }

    /**
     * @param string   $identifier
     * @param int|null $storeId
     *
     * @return Page
     */
    public function loadCmsPageByIdentifier(string $identifier, int $storeId = null): Page
    {
        $cmsPage = $this->newCmsPage();

        if ( ! $this->variableHelper->isEmpty($storeId)) {
            $cmsPage->setData('store_id', $storeId);
        }

        $cmsPage->load($identifier, 'identifier');

        return $cmsPage;
    }

    /**
     * @param Page $cmsPage
     *
     * @throws Exception
     */
    public function saveCmsPage(Page $cmsPage)
    {
        $this->cmsPageResourceFactory->create()->save($cmsPage);
    }

    /**
     * @return Collection
     */
    public function getCmsPageCollection(): Collection
    {
        return $this->cmsPageCollectionFactory->create();
    }

    /**
     * @return Block
     */
    public function newCmsBlock(): Block
    {
        return $this->cmsBlockFactory->create();
    }

    /**
     * @param int $cmsBlockId
     *
     * @return Block
     */
    public function loadCmsBlock(int $cmsBlockId): Block
    {
        $cmsBlock = $this->newCmsBlock();

        $this->cmsBlockResourceFactory->create()->load($cmsBlock, $cmsBlockId);

        return $cmsBlock;
    }

    /**
     * @param string   $identifier
     * @param int|null $storeId
     *
     * @return Block
     */
    public function loadCmsBlockByIdentifier(string $identifier, int $storeId = null): Block
    {
        $cmsBlock = $this->newCmsBlock();

        if ( ! $this->variableHelper->isEmpty($storeId)) {
            $cmsBlock->setData('store_id', $storeId);
        }

        $this->cmsBlockResourceFactory->create()->load($cmsBlock, $identifier, 'identifier');

        return $cmsBlock;
    }

    /**
     * @param Block $cmsBlock
     *
     * @throws Exception
     */
    public function saveCmsBlock(Block $cmsBlock)
    {
        $this->cmsBlockResourceFactory->create()->save($cmsBlock);
    }

    /**
     * @return \Magento\Cms\Model\ResourceModel\Block\Collection
     */
    public function getCmsBlockCollection(): \Magento\Cms\Model\ResourceModel\Block\Collection
    {
        return $this->cmsBlockCollectionFactory->create();
    }
}
