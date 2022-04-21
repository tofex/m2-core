<?php

namespace Tofex\Core\Helper;

use Exception;
use Tofex\Help\Json;

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2022 Tofex UG (http://www.tofex.de)
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Config
{
    /** @var Stores */
    protected $storeHelper;

    /** @var Json */
    protected $jsonHelper;

    /**
     * @param Stores $storeHelper
     * @param Json   $jsonHelper
     */
    public function __construct(Stores $storeHelper, Json $jsonHelper)
    {
        $this->storeHelper = $storeHelper;
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * @param string $fileName
     *
     * @throws Exception
     */
    public function importConfigJsonFile(string $fileName)
    {
        if (file_exists($fileName) && is_readable($fileName)) {
            $this->importConfigJsonString(file_get_contents($fileName));
        } else {
            throw new Exception(sprintf('Could not read file: %s', $fileName));
        }
    }

    /**
     * @param string $jsonString
     *
     * @throws Exception
     */
    public function importConfigJsonString(string $jsonString)
    {
        $config = $this->jsonHelper->decode($jsonString);

        if (is_array($config) && $this->isValidConfig($config)) {
            $this->importConfig($config);
        }
    }

    /**
     * @param array $config
     *
     * @return bool
     * @throws Exception
     */
    protected function isValidConfig(array $config): bool
    {
        foreach ($config as $scope => $scopesConfig) {
            if ($scope !== 'default' && $scope !== 'websites' && $scope !== 'stores') {
                throw new Exception(sprintf('Invalid configuration scope: %s', $scope));
            }

            foreach ($scopesConfig as $scopeId => $scopeConfig) {
                if ( ! ctype_digit(strval($scopeId))) {
                    throw new Exception(sprintf('Invalid scope id: %s', $scopeId));
                }

                if ( ! is_array($scopeConfig)) {
                    throw new Exception(sprintf('Invalid config for scope: %s with id: %s', $scope, $scopeId));
                }

                foreach ($scopeConfig as $section => $sectionConfig) {
                    if ( ! is_array($sectionConfig)) {
                        throw new Exception(sprintf('Invalid section config for scope: %s with id: %s and section: %s',
                            $scope, $scopeId, $section));
                    }

                    foreach ($sectionConfig as $group => $groupConfig) {
                        if ( ! is_array($groupConfig)) {
                            throw new Exception(sprintf('Invalid group config for scope: %s with id: %s and section: %s, group: %s',
                                $scope, $scopeId, $section, $group));
                        }
                    }
                }
            }
        }

        return true;
    }

    /**
     * [scope]
     *   [id]
     *     [section]
     *       [group]
     *         [field1]: [value1]
     *         [field2]: [field2]
     *
     * @param array $config
     */
    public function importConfig(array $config)
    {
        foreach ($config as $scope => $scopesConfig) {
            foreach ($scopesConfig as $scopeId => $scopeConfig) {
                $this->importScopeConfig($scope, $scopeId, $scopeConfig);
            }
        }
    }

    /**
     * @param string $scope
     * @param int    $scopeId
     * @param array  $scopeConfig
     */
    protected function importScopeConfig(string $scope, int $scopeId, array $scopeConfig)
    {
        foreach ($scopeConfig as $key => $value) {
            $this->importValue($scope, $scopeId, [], $key, $value);
        }
    }

    /**
     * @param string $scope
     * @param int    $scopeId
     * @param array  $parentPath
     * @param string $key
     * @param mixed  $value
     */
    protected function importValue(string $scope, int $scopeId, array $parentPath, string $key, $value)
    {
        $valuePath = $parentPath;
        $valuePath[] = $key;

        if (is_array($value)) {
            foreach ($value as $valueKey => $valueValue) {
                $this->importValue($scope, $scopeId, $valuePath, $valueKey, $valueValue);
            }
        } else {
            $path = implode('/', $valuePath);

            $this->storeHelper->insertConfigValue($path, $value, $scope, $scopeId);
        }
    }
}
