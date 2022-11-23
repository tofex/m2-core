<?php

namespace Tofex\Core\Helper;

use Exception;
use Magento\Cms\Model\Block;
use Magento\Cms\Model\BlockFactory;
use Magento\Cms\Model\Page;
use Magento\Cms\Model\PageFactory;
use Magento\Cms\Model\ResourceModel\Page\Collection;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory;
use Psr\Log\LoggerInterface;
use SimpleXMLElement;
use Tofex\Help\Arrays;
use Tofex\Help\Variables;
use Tofex\Xml\SimpleXml;

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2022 Tofex UG (http://www.tofex.de)
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Cms
{
    /** string for cms block identifier */
    const STRING_IDENTIFIER = 'identifier';

    /** @var Variables */
    protected $variableHelper;

    /** @var Arrays */
    protected $arrayHelper;

    /** @var Files */
    protected $filesHelper;

    /** @var LoggerInterface */
    protected $logging;

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

    /** @var SimpleXml */
    protected $simpleXml;

    /**
     * @param Variables                                                $variableHelper
     * @param Arrays                                                   $arrayHelper
     * @param Files                                                    $filesHelper
     * @param LoggerInterface                                          $logging
     * @param PageFactory                                              $cmsPageFactory
     * @param \Magento\Cms\Model\ResourceModel\PageFactory             $cmsPageResourceFactory
     * @param CollectionFactory                                        $cmsPageCollectionFactory
     * @param BlockFactory                                             $cmsBlockFactory
     * @param \Magento\Cms\Model\ResourceModel\BlockFactory            $cmsBlockResourceFactory
     * @param \Magento\Cms\Model\ResourceModel\Block\CollectionFactory $cmsBlockCollectionFactory
     * @param SimpleXml                                                $simpleXml
     */
    public function __construct(
        Variables $variableHelper,
        Arrays $arrayHelper,
        Files $filesHelper,
        LoggerInterface $logging,
        PageFactory $cmsPageFactory,
        \Magento\Cms\Model\ResourceModel\PageFactory $cmsPageResourceFactory,
        CollectionFactory $cmsPageCollectionFactory,
        BlockFactory $cmsBlockFactory,
        \Magento\Cms\Model\ResourceModel\BlockFactory $cmsBlockResourceFactory,
        \Magento\Cms\Model\ResourceModel\Block\CollectionFactory $cmsBlockCollectionFactory,
        SimpleXml $simpleXml)
    {
        $this->variableHelper = $variableHelper;
        $this->arrayHelper = $arrayHelper;
        $this->filesHelper = $filesHelper;

        $this->logging = $logging;
        $this->cmsPageFactory = $cmsPageFactory;
        $this->cmsPageResourceFactory = $cmsPageResourceFactory;
        $this->cmsPageCollectionFactory = $cmsPageCollectionFactory;
        $this->cmsBlockFactory = $cmsBlockFactory;
        $this->cmsBlockResourceFactory = $cmsBlockResourceFactory;
        $this->cmsBlockCollectionFactory = $cmsBlockCollectionFactory;
        $this->simpleXml = $simpleXml;
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

    /**
     * @param string $xmlFileName
     * @param bool   $overwriteExisting
     *
     * @return string[]
     * @throws Exception
     */
    public function importPagesFromXmlFile(string $xmlFileName, bool $overwriteExisting = false): array
    {
        $fileName = $this->filesHelper->determineFilePath($xmlFileName);

        $xmlElement = $this->simpleXml->simpleXmlLoadFile($fileName);

        return $this->importPagesFromXml($xmlElement, $overwriteExisting);
    }

    /**
     * @param SimpleXMLElement $xml
     * @param bool             $overwriteExisting
     *
     * @return string[]
     */
    public function importPagesFromXml(SimpleXMLElement $xml, bool $overwriteExisting = false): array
    {
        $notImported = [];

        // <root><pages><cms_block/><cms_block/></pages></root>
        foreach ($xml->children()->children() as $pageXml) {
            $imported = $this->importPageFromXml($pageXml, $overwriteExisting);

            if ( ! $imported) {
                $notImported[] = $pageXml->{static::STRING_IDENTIFIER};
            }
        }

        return $notImported;
    }

    /**
     * @param SimpleXMLElement $pageXml
     * @param bool             $overwriteExisting
     *
     * @return bool
     */
    public function importPageFromXml(SimpleXMLElement $pageXml, bool $overwriteExisting): bool
    {
        $pageData = $this->simpleXml->xmlToArray($pageXml);

        return $this->importPage($pageData, $overwriteExisting);
    }

    /**
     * @param array $pageData
     * @param bool  $overwriteExisting
     *
     * @return bool
     */
    public function importPage(array $pageData, bool $overwriteExisting = false): bool
    {
        $pageIdentifier = $this->arrayHelper->getValue($pageData, static::STRING_IDENTIFIER);

        $stores = $this->arrayHelper->getValue($pageData, 'stores:item', []);

        if ($this->variableHelper->isEmpty($stores)) {
            $storeIds = [0];
        } else {
            $storeIds = is_array($stores) ? $stores : [$stores];
        }

        $oldPages = $this->cmsPageCollectionFactory->create();

        $oldPages->addFieldToFilter(static::STRING_IDENTIFIER, $pageIdentifier);
        $oldPages->addStoreFilter($storeIds);

        $oldPages->load();

        if (count($oldPages) > 0 && ! $overwriteExisting) {
            // page already exists and we are not allowed to overwrite
            $this->logging->info(sprintf('Skipping existing pages with identifier: %s', $pageIdentifier));

            return false;
        }

        /** @var Page $oldPage */
        foreach ($oldPages as $oldPage) {
            try {
                $this->cmsPageResourceFactory->create()->delete($oldPage);

                $this->logging->info(sprintf('Removing pages(s) with identifier: %s and title: %s for stores: %s',
                    $oldPage->getIdentifier(), $oldPage->getTitle(), implode(', ', $storeIds)));
            } catch (Exception $exception) {
                $this->logging->error($exception);
            }
        }

        $layoutUpdateXml = $this->arrayHelper->getValue($pageData, 'layout_update_xml');

        if ($this->variableHelper->isEmpty($layoutUpdateXml)) {
            $layoutUpdateXml = null;
        }

        $pageModelData = [
            'title'             => (string)$this->arrayHelper->getValue($pageData, 'title'),
            'identifier'        => $pageIdentifier,
            'content'           => (string)$this->arrayHelper->getValue($pageData, 'content'),
            'is_active'         => (int)$this->arrayHelper->getValue($pageData, 'is_active', true),
            'stores'            => $storeIds,
            'page_layout'       => (string)$this->arrayHelper->getValue($pageData, 'page_layout'),
            'layout_update_xml' => $layoutUpdateXml
        ];

        $newPage = $this->cmsPageFactory->create();
        $newPage->setData($pageModelData);

        try {
            $this->logging->info(sprintf('Saving page with identifier: %s and title: %s for stores: %s',
                $newPage->getIdentifier(), $newPage->getTitle(), implode(', ', $storeIds)));

            $this->cmsPageResourceFactory->create()->save($newPage);

            return true;
        } catch (Exception $exception) {
            $this->logging->error(sprintf('Could not save page with identifier: %s because: %s', $pageIdentifier,
                $exception->getMessage()));
            $this->logging->error($exception);

            return false;
        }
    }

    /**
     * @param string $xmlFileName
     * @param bool   $overwriteExisting
     *
     * @return string[]
     * @throws Exception
     */
    public function importBlocksFromXmlFile(string $xmlFileName,bool $overwriteExisting = false): array
    {
        $fileName = $this->filesHelper->determineFilePath($xmlFileName);

        $xmlElement = $this->simpleXml->simpleXmlLoadFile($fileName);

        return $this->importBlocksFromXml($xmlElement, $overwriteExisting);
    }

    /**
     * @param SimpleXMLElement $xml
     * @param bool             $overwriteExisting
     *
     * @return string[]
     */
    public function importBlocksFromXml(SimpleXMLElement $xml, bool $overwriteExisting = false): array
    {
        $notImported = [];

        // <root><blocks><cms_block/><cms_block/></blocks></root>
        foreach ($xml->children()->children() as $blockXml) {
            $imported = $this->importBlockFromXml($blockXml, $overwriteExisting);

            if ( ! $imported) {
                $notImported[] = $blockXml->{static::STRING_IDENTIFIER};
            }
        }

        return $notImported;
    }

    /**
     * @param SimpleXMLElement $blockXml
     * @param bool             $overwriteExisting
     *
     * @return bool
     */
    public function importBlockFromXml(SimpleXMLElement $blockXml, bool $overwriteExisting): bool
    {
        $blockData = $this->simpleXml->xmlToArray($blockXml);

        return $this->importBlock($blockData, $overwriteExisting);
    }

    /**
     * @param array $blockData
     * @param bool  $overwriteExisting
     *
     * @return bool
     */
    public function importBlock(array $blockData, bool $overwriteExisting = false): bool
    {
        $blockIdentifier = $this->arrayHelper->getValue($blockData, static::STRING_IDENTIFIER);

        $stores = $this->arrayHelper->getValue($blockData, 'stores:item', []);

        if ($this->variableHelper->isEmpty($stores)) {
            $storeIds = [0];
        } else {
            $storeIds = is_array($stores) ? $stores : [$stores];
        }

        $oldBlocks = $this->cmsBlockCollectionFactory->create();

        $oldBlocks->addFieldToFilter(static::STRING_IDENTIFIER, $blockIdentifier);
        $oldBlocks->addStoreFilter($storeIds);

        $oldBlocks->load();

        if (count($oldBlocks) > 0 && ! $overwriteExisting) {
            // block already exists and we are not allowed to overwrite
            $this->logging->info(sprintf('Skipping existing block with identifier: %s', $blockIdentifier));

            return false;
        }

        /** @var Block $oldBlock */
        foreach ($oldBlocks as $oldBlock) {
            try {
                $this->cmsBlockResourceFactory->create()->delete($oldBlock);

                $this->logging->info(sprintf('Removing block(s) with identifier: %s and title: %s for stores: %s',
                    $oldBlock->getIdentifier(), $oldBlock->getTitle(), implode(', ', $storeIds)));
            } catch (Exception $exception) {
                $this->logging->error($exception);
            }
        }

        $blockModelData = [
            'title'      => (string)$this->arrayHelper->getValue($blockData, 'title'),
            'identifier' => $blockIdentifier,
            'content'    => (string)$this->arrayHelper->getValue($blockData, 'content'),
            'is_active'  => (int)$this->arrayHelper->getValue($blockData, 'is_active', true),
            'stores'     => $storeIds
        ];

        $newBlock = $this->cmsBlockFactory->create();
        $newBlock->setData($blockModelData);

        try {
            $this->logging->info(sprintf('Saving block with identifier: %s and title: %s for stores: %s',
                $newBlock->getIdentifier(), $newBlock->getTitle(), implode(', ', $storeIds)));

            $this->cmsBlockResourceFactory->create()->save($newBlock);

            return true;
        } catch (Exception $exception) {
            $this->logging->error(sprintf('Could not save block with identifier: %s because: %s', $blockIdentifier,
                $exception->getMessage()));
            $this->logging->error($exception);

            return false;
        }
    }
}
