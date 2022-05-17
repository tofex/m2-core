<?php

namespace Tofex\Core\Helper;

use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Model\Order\Shipment\Track;
use Magento\Sales\Model\Order\Shipment\TrackFactory;
use Magento\Sales\Model\ResourceModel\Order\Shipment\Collection;
use Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\ShipmentFactory;

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2022 Tofex UG (http://www.tofex.de)
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Shipment
{
    /** @var ShipmentRepositoryInterface */
    protected $shipmentRepository;

    /** @var ShipmentFactory */
    protected $shipmentResourceFactory;

    /** @var CollectionFactory */
    protected $shipmentCollectionFactory;

    /** @var TrackFactory */
    protected $shipmentTrackFactory;

    /** @var \Magento\Sales\Model\ResourceModel\Order\Shipment\TrackFactory */
    protected $shipmentTrackResourceFactory;

    /** @var \Magento\Sales\Model\ResourceModel\Order\Shipment\Track\CollectionFactory */
    protected $shipmentTrackCollectionFactory;

    /**
     * @param ShipmentRepositoryInterface                                               $shipmentRepository
     * @param ShipmentFactory                                                           $shipmentResourceFactory
     * @param CollectionFactory                                                         $shipmentCollectionFactory
     * @param TrackFactory                                                              $shipmentTrackFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Shipment\TrackFactory            $shipmentTrackResourceFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Shipment\Track\CollectionFactory $shipmentTrackCollectionFactory
     */
    public function __construct(
        ShipmentRepositoryInterface $shipmentRepository,
        ShipmentFactory $shipmentResourceFactory,
        CollectionFactory $shipmentCollectionFactory,
        TrackFactory $shipmentTrackFactory,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\TrackFactory $shipmentTrackResourceFactory,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\Track\CollectionFactory $shipmentTrackCollectionFactory)
    {
        $this->shipmentRepository = $shipmentRepository;
        $this->shipmentResourceFactory = $shipmentResourceFactory;
        $this->shipmentCollectionFactory = $shipmentCollectionFactory;
        $this->shipmentTrackFactory = $shipmentTrackFactory;
        $this->shipmentTrackResourceFactory = $shipmentTrackResourceFactory;
        $this->shipmentTrackCollectionFactory = $shipmentTrackCollectionFactory;
    }

    /**
     * @return \Magento\Sales\Model\Order\Shipment
     */
    public function newShipment(): \Magento\Sales\Model\Order\Shipment
    {
        return $this->shipmentRepository->create();
    }

    /**
     * @param int $shipmentId
     *
     * @return \Magento\Sales\Model\Order\Shipment
     */
    public function loadShipment(int $shipmentId): \Magento\Sales\Model\Order\Shipment
    {
        $shipment = $this->newShipment();

        $this->shipmentResourceFactory->create()->load($shipment, $shipmentId);

        return $shipment;
    }

    /**
     * @param \Magento\Sales\Model\Order\Shipment $shipment
     *
     * @throws AlreadyExistsException
     */
    public function saveShipment(\Magento\Sales\Model\Order\Shipment $shipment)
    {
        $this->shipmentResourceFactory->create()->save($shipment);
    }

    /**
     * @return Collection
     */
    public function getShipmentCollection(): Collection
    {
        return $this->shipmentCollectionFactory->create();
    }

    /**
     * @return Track
     */
    public function newShipmentTrack(): Track
    {
        return $this->shipmentTrackFactory->create();
    }
}
