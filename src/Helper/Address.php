<?php

namespace Tofex\Core\Helper;

use Exception;
use Magento\Directory\Model\Country;
use Magento\Directory\Model\CountryFactory;
use Magento\Directory\Model\ResourceModel\Country\Collection;
use Magento\Directory\Model\ResourceModel\Country\CollectionFactory;

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2022 Tofex UG (http://www.tofex.de)
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Address
{
    /** @var CountryFactory */
    protected $countryFactory;

    /** @var \Magento\Directory\Model\ResourceModel\CountryFactory */
    protected $countryResourceFactory;

    /** @var CollectionFactory */
    protected $countryCollectionFactory;

    /**
     * @param CountryFactory                                        $countryFactory
     * @param \Magento\Directory\Model\ResourceModel\CountryFactory $countryResourceFactory
     * @param CollectionFactory                                     $countryCollectionFactory
     */
    public function __construct(
        CountryFactory $countryFactory,
        \Magento\Directory\Model\ResourceModel\CountryFactory $countryResourceFactory,
        CollectionFactory $countryCollectionFactory)
    {
        $this->countryFactory = $countryFactory;
        $this->countryResourceFactory = $countryResourceFactory;
        $this->countryCollectionFactory = $countryCollectionFactory;
    }

    /**
     * @return Country
     */
    public function newCountry(): Country
    {
        return $this->countryFactory->create();
    }

    /**
     * @param int $countryId
     *
     * @return Country
     */
    public function loadCountry(int $countryId): Country
    {
        $country = $this->newCountry();

        $this->countryResourceFactory->create()->load($country, $countryId);

        return $country;
    }

    /**
     * @param Country $country
     *
     * @throws Exception
     */
    public function saveProduct(Country $country)
    {
        $this->countryResourceFactory->create()->save($country);
    }

    /**
     * @return Collection
     */
    public function getProductCollection(): Collection
    {
        return $this->countryCollectionFactory->create();
    }
}
