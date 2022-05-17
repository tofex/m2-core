<?php

namespace Tofex\Core\Helper;

use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Sales\Model\Order\PaymentFactory;
use Magento\Sales\Model\ResourceModel\Order\Payment\Collection;
use Magento\Sales\Model\ResourceModel\Order\Payment\CollectionFactory;

/**
 * @author      Andreas Knollmann
 * @copyright   Copyright (c) 2014-2022 Tofex UG (http://www.tofex.de)
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 */
class Payment
{
    /** @var PaymentFactory */
    protected $paymentFactory;

    /** @var \Magento\Sales\Model\ResourceModel\Order\PaymentFactory */
    protected $paymentResourceFactory;

    /** @var CollectionFactory */
    protected $paymentCollectionFactory;

    /**
     * @param PaymentFactory                                          $paymentFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\PaymentFactory $paymentResourceFactory
     * @param CollectionFactory                                       $paymentCollectionFactory
     */
    public function __construct(
        PaymentFactory $paymentFactory,
        \Magento\Sales\Model\ResourceModel\Order\PaymentFactory $paymentResourceFactory,
        CollectionFactory $paymentCollectionFactory)
    {
        $this->paymentFactory = $paymentFactory;
        $this->paymentResourceFactory = $paymentResourceFactory;
        $this->paymentCollectionFactory = $paymentCollectionFactory;
    }

    /**
     * @return \Magento\Sales\Model\Order\Payment
     */
    public function newPayment(): \Magento\Sales\Model\Order\Payment
    {
        return $this->paymentFactory->create();
    }

    /**
     * @param int $paymentId
     *
     * @return \Magento\Sales\Model\Order\Payment
     */
    public function loadPayment(int $paymentId): \Magento\Sales\Model\Order\Payment
    {
        $payment = $this->newPayment();

        $this->paymentResourceFactory->create()->load($payment, $paymentId);

        return $payment;
    }

    /**
     * @param \Magento\Sales\Model\Order\Payment $payment
     *
     * @throws AlreadyExistsException
     */
    public function savePayment(\Magento\Sales\Model\Order\Payment $payment)
    {
        $this->paymentResourceFactory->create()->save($payment);
    }

    /**
     * @return Collection
     */
    public function getPaymentCollection(): Collection
    {
        return $this->paymentCollectionFactory->create();
    }
}
