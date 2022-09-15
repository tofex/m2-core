<?php

namespace Tofex\Core\Helper;

use Exception;
use Magento\Config\Model\Config\Backend\Image\Logo;
use Magento\Config\Model\ResourceModel\ConfigFactory;
use Magento\Directory\Helper\Data;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Asset\Repository;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Website;
use Psr\Log\LoggerInterface;
use Tofex\Help\Arrays;
use Tofex\Help\Variables;
use Zend_Locale_Data;
use Zend_Locale_Exception;
use Zend_Locale_Format;

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2022 Tofex UG (http://www.tofex.de)
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Stores
    extends AbstractHelper
{
    /** @var Models */
    protected $variableHelper;

    /** @var Arrays */
    protected $arrayHelper;

    /** @var Database */
    protected $databaseHelper;

    /** @var Instances */
    protected $instanceHelper;

    /** @var LoggerInterface */
    protected $logging;

    /** @var StoreManagerInterface */
    protected $storeManager;

    /** @var ConfigFactory */
    protected $configFactory;

    /** @var Filesystem */
    protected $filesystem;

    /** @var Repository */
    protected $assetRepository;

    /** @var RequestInterface */
    protected $request;

    /** @var PriceCurrencyInterface */
    protected $priceCurrency;

    /** @var ResolverInterface */
    protected $localeResolver;

    /**
     * @param Context                $context
     * @param Variables              $variableHelper
     * @param Arrays                 $arrayHelper
     * @param Database               $databaseHelper
     * @param Instances              $instanceHelper
     * @param LoggerInterface        $logging
     * @param StoreManagerInterface  $storeManager
     * @param ConfigFactory          $configFactory
     * @param Filesystem             $filesystem
     * @param Repository             $assetRepository
     * @param RequestInterface       $request
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        Context $context,
        Variables $variableHelper,
        Arrays $arrayHelper,
        Database $databaseHelper,
        Instances $instanceHelper,
        LoggerInterface $logging,
        StoreManagerInterface $storeManager,
        ConfigFactory $configFactory,
        Filesystem $filesystem,
        Repository $assetRepository,
        RequestInterface $request,
        PriceCurrencyInterface $priceCurrency)
    {
        parent::__construct($context);

        $this->variableHelper = $variableHelper;
        $this->arrayHelper = $arrayHelper;
        $this->databaseHelper = $databaseHelper;
        $this->instanceHelper = $instanceHelper;

        $this->logging = $logging;
        $this->storeManager = $storeManager;
        $this->configFactory = $configFactory;
        $this->filesystem = $filesystem;
        $this->assetRepository = $assetRepository;
        $this->request = $request;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * @param string   $path
     * @param mixed    $defaultValue
     * @param bool     $isFlag
     * @param int|null $storeId
     *
     * @return mixed
     */
    public function getStoreConfig(string $path, $defaultValue = null, bool $isFlag = false, int $storeId = null)
    {
        try {
            $store = $this->getStore($storeId);

            $value = $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $store->getCode());

            if ($isFlag === true && ! is_null($value)) {
                $value = $this->scopeConfig->isSetFlag($path, ScopeInterface::SCOPE_STORE, $store->getCode());
            }

            if (is_null($value)) {
                $value = $defaultValue;
            }

            return $value;
        } catch (NoSuchEntityException $exception) {
            return $defaultValue;
        }
    }

    /**
     * @param string   $path
     * @param mixed    $defaultValue
     * @param bool     $isFlag
     * @param int|null $websiteId
     *
     * @return mixed
     */
    public function getWebsiteConfig(string $path, $defaultValue = null, bool $isFlag = false, int $websiteId = null)
    {
        try {
            $website = $this->getWebsite($websiteId);

            $value = $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_WEBSITE, $website->getCode());

            if ($isFlag === true && ! is_null($value)) {
                $value = $this->scopeConfig->isSetFlag($path, ScopeInterface::SCOPE_WEBSITE, $website->getCode());
            }

            if (is_null($value)) {
                $value = $defaultValue;
            }

            return $value;
        } catch (LocalizedException $exception) {
            return $defaultValue;
        }
    }

    /**
     * @param string   $path
     * @param bool     $defaultValue
     * @param int|null $storeId
     *
     * @return mixed
     */
    public function getStoreConfigFlag(string $path, bool $defaultValue = false, int $storeId = null)
    {
        return $this->getStoreConfig($path, $defaultValue, true, $storeId);
    }

    /**
     * @param string   $configPath
     * @param string   $delimiter
     * @param int|null $storeId
     *
     * @return array
     */
    public function getExplodedConfigValues(string $configPath, string $delimiter = ',', int $storeId = null): array
    {
        $valueString = $this->getStoreConfig($configPath, null, false, $storeId);

        if ( ! $this->variableHelper->isEmpty($valueString)) {
            if (strpos($valueString, $delimiter) !== false) {
                $values = explode($delimiter, $valueString);
                return array_map('trim', $values);
            } else if (preg_match('/\n/', $valueString)) {
                $values = explode("\n", $valueString);
                return array_map('trim', $values);
            } else {
                return [trim($valueString)];
            }
        }

        return [];
    }

    /**
     * @param string $oldPath
     * @param string $newPath
     */
    public function moveConfigValue(string $oldPath, string $newPath)
    {
        $writeAdapter = $this->databaseHelper->getDefaultConnection();

        $tableName = $this->databaseHelper->getTableName('core_config_data');

        $oldQuery = $writeAdapter->select()->from($tableName);

        $oldQuery->where('path = ?', $oldPath);

        $oldQueryResult = $writeAdapter->fetchAssoc($oldQuery);

        if ( ! $this->variableHelper->isEmpty($oldQueryResult)) {
            foreach ($oldQueryResult as $oldData) {
                $newQuery = $writeAdapter->select()->from($tableName);

                $newQuery->where('path = ?', $newPath);
                $newQuery->where('scope = ?', $this->arrayHelper->getValue($oldData, 'scope'));
                $newQuery->where('scope_id = ?', $this->arrayHelper->getValue($oldData, 'scope_id'));

                $newQueryResult = $writeAdapter->fetchAssoc($newQuery);

                $newData = reset($newQueryResult);

                if ( ! $this->variableHelper->isEmpty($newData)) {
                    $writeAdapter->update($tableName, [
                        'value' => $this->arrayHelper->getValue($oldData, 'value')
                    ], sprintf('config_id = %d', $this->arrayHelper->getValue($newData, 'config_id')));

                    $writeAdapter->delete($tableName,
                        sprintf('config_id = %d', $this->arrayHelper->getValue($oldData, 'config_id')));
                } else {
                    $writeAdapter->update($tableName, ['path' => $newPath],
                        sprintf('config_id = %d', $this->arrayHelper->getValue($oldData, 'config_id')));
                }
            }
        } // else no old data to move
    }

    /**
     * @param string $path
     * @param mixed  $value
     * @param string $scope
     * @param int    $scopeId
     */
    public function insertConfigValue(string $path, $value, string $scope = 'default', int $scopeId = 0)
    {
        $this->configFactory->create()
            ->saveConfig($path, is_array($value) ? implode(',', $value) : $value, $scope, $scopeId);
    }

    /**
     * @param string $path
     */
    public function removeConfigValue(string $path)
    {
        $this->databaseHelper->getDefaultConnection()
            ->delete($this->databaseHelper->getTableName('core_config_data'), sprintf('path = "%s"', $path));
    }

    /**
     * @param int|string|null $storeId
     *
     * @return Store
     * @throws NoSuchEntityException
     * @noinspection PhpDocRedundantThrowsInspection
     * @noinspection RedundantSuppression
     */
    public function getStore($storeId = null): Store
    {
        /** @var Store $store */
        $store = $this->storeManager->getStore($storeId);

        return $store;
    }

    /**
     * @param int|null $websiteId
     *
     * @return Store
     * @throws LocalizedException
     */
    public function getDefaultStore(int $websiteId = null): Store
    {
        $website = $this->getWebsite($websiteId);

        $group = $this->storeManager->getGroup($website->getDefaultGroupId());

        return $this->getStore($group->getDefaultStoreId());
    }

    /**
     * @param int|string|null $websiteId
     *
     * @return Website
     * @throws LocalizedException
     */
    public function getWebsite($websiteId = null): Website
    {
        /** @var Website $website */
        $website = $this->storeManager->getWebsite($websiteId);

        return $website;
    }

    /**
     * @param bool $withDefault
     * @param bool $codeKey
     *
     * @return Website[]
     */
    public function getWebsites(bool $withDefault = false, bool $codeKey = false): array
    {
        /** @var Website[] $websites */
        $websites = $this->storeManager->getWebsites($withDefault, $codeKey);

        return $websites;
    }

    /**
     * @param bool $withDefault
     * @param bool $codeKey
     *
     * @return Store[]
     */
    public function getStores(bool $withDefault = false, bool $codeKey = false): array
    {
        /** @var Store[] $stores */
        $stores = $this->storeManager->getStores($withDefault, $codeKey);

        return $stores;
    }

    /**
     * @return string
     */
    public function getWebUrl(): string
    {
        $store = null;

        try {
            $store = $this->getStore();
        } catch (NoSuchEntityException $exception) {
            try {
                $store = $this->getDefaultStore();
            } catch (LocalizedException $exception) {
            }
        }

        return $store !== null ? $store->getBaseUrl(UrlInterface::URL_TYPE_WEB) : '';
    }

    /**
     * @return string
     */
    public function getMediaUrl(): string
    {
        $store = null;

        try {
            $store = $this->getStore();
        } catch (NoSuchEntityException $exception) {
            try {
                $store = $this->getDefaultStore();
            } catch (LocalizedException $exception) {
            }
        }

        return $store !== null ? $store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) : '';
    }

    /**
     * @return string
     */
    public function getSiteLogo(): string
    {
        $folderName = Logo::UPLOAD_DIR;

        $storeLogoPath = $this->getStoreConfig('design/header/logo_src');

        $path = $folderName . '/' . $storeLogoPath;

        $logoUrl = $this->getMediaUrl() . $path;

        $mediaDirectory = $this->filesystem->getDirectoryRead(DirectoryList::MEDIA);

        return $storeLogoPath !== null && $mediaDirectory->isFile($path) ? $logoUrl :
            $this->assetRepository->getUrlWithParams('images/logo.svg', ['_secure' => $this->request->isSecure()]);
    }

    /**
     * @return bool
     */
    public function isSingleStoreMode(): bool
    {
        return $this->storeManager->isSingleStoreMode();
    }

    /**
     * @param Store $store
     * @param array $data
     * @param array $resetSections
     *
     * @throws Exception
     */
    public function import(Store $store, array $data, array $resetSections = [])
    {
        $isDefault = $store->getId() == 0;

        foreach ($data as $section => $sectionData) {
            if (in_array($section, $resetSections)) {
                $this->logging->info(sprintf('Resetting section: %s', $section));

                if ($isDefault) {
                    $this->databaseHelper->deleteTableData($this->databaseHelper->getDefaultConnection(),
                        $this->databaseHelper->getTableName('core_config_data'),
                        sprintf('path like "%s/%%"', $section));
                } else {
                    $this->databaseHelper->deleteTableData($this->databaseHelper->getDefaultConnection(),
                        $this->databaseHelper->getTableName('core_config_data'),
                        sprintf('path like "%s/%%" AND scope = "stores" AND scope_id = %d', $section, $store->getId()));
                }
            }

            $this->logging->info(sprintf('Importing section: %s', $section));

            foreach ($sectionData as $group => $groupData) {
                $this->logging->info(sprintf('Importing group: %s/%s', $section, $group));

                foreach ($groupData as $field => $value) {
                    $this->insertConfigValue(sprintf('%s/%s/%s', $section, $group, $field), $value,
                        $isDefault ? 'default' : 'stores', $isDefault ? 0 : $store->getId());
                }
            }
        }
    }

    /**
     * @param Store $store
     * @param array $sections
     *
     * @return array
     */
    public function export(Store $store, array $sections): array
    {
        $data = [];

        foreach ($sections as $section) {
            $data[ $section ] = $this->getStoreConfig($section, [], false, $store->getId());
        }

        return $data;
    }

    /**
     * @param string $value
     *
     * @return string
     * @throws Zend_Locale_Exception
     */
    public function getNumber(string $value)
    {
        $locale = $this->getStoreConfig(Data::XML_PATH_DEFAULT_LOCALE, 'en_US');

        try {
            return Zend_Locale_Format::getNumber($value, ['locale' => $locale]);
        } catch (Zend_Locale_Exception $exception) {
            if (is_numeric($value)) {
                return $value;
            }

            throw new $exception;
        }
    }

    /**
     * @param float $value
     * @param int   $precision
     *
     * @return string
     */
    public function formatNumber(float $value, int $precision = 2): string
    {
        $locale = $this->getStoreConfig(Data::XML_PATH_DEFAULT_LOCALE, 'en_US');

        try {
            $value = Zend_Locale_Format::toNumber(sprintf("%F", $value), [
                'locale'        => $locale,
                'number_format' => Zend_Locale_Data::getContent($locale, 'decimalnumber'),
                'precision'     => $precision
            ]);
        } catch (Zend_Locale_Exception $exception) {
            $this->logging->error($exception);
        }

        return $value;
    }

    /**
     * @param float $price
     * @param bool  $includeContainer
     *
     * @return float
     */
    public function formatPrice(float $price, bool $includeContainer = true): float
    {
        try {
            return $this->priceCurrency->format($price, $includeContainer, PriceCurrencyInterface::DEFAULT_PRECISION,
                $this->getStore());
        } catch (NoSuchEntityException $exception) {
            $this->logging->error($exception);
        }

        return $price;
    }

    /**
     * @param mixed $price
     *
     * @return double
     */
    public function roundPrice($price): float
    {
        return round($price, 2);
    }

    /**
     * @param float   $price
     * @param boolean $format
     *
     * @return float
     */
    public function convertPrice(float $price, bool $format = false): float
    {
        $value = $price;

        try {
            $store = $this->getStore();

            if ($store->getCurrentCurrency() && $store->getBaseCurrency()) {
                $value = $store->getBaseCurrency()->convert($price, $store->getCurrentCurrency());
            }

            if ($store->getCurrentCurrency() && $format) {
                $value = $this->formatPrice($value);
            }
        } catch (NoSuchEntityException | Exception $exception) {
            $this->logging->error($exception);
        }

        return $value;
    }

    /**
     * @return string
     */
    public function getLocale(): string
    {
        if ($this->localeResolver === null) {
            $this->localeResolver = $this->instanceHelper->getSingleton(ResolverInterface::class);
        }

        return $this->localeResolver->getLocale();
    }
}
