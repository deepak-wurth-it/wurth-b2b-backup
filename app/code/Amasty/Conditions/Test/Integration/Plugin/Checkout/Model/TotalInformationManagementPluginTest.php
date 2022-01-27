<?php

namespace Amasty\Conditions\Test\Integration\Plugin\Checkout\Model;

use Amasty\Conditions\Api\Data\AddressInterface as ConditionsAddressInterface;
use Amasty\Conditions\Model\ResourceModel\Quote as QuoteResource;
use Amasty\Conditions\Model\QuoteFactory;
use Amasty\Conditions\Plugin\Checkout\Model\TotalInformationManagementPlugin;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Product;
use Magento\Checkout\Api\Data\TotalsInformationInterface;
use Magento\Checkout\Api\TotalsInformationManagementInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\OfflinePayments\Model\Cashondelivery;
use Magento\OfflinePayments\Model\Checkmo;
use Magento\Quote\Api\CartItemRepositoryInterface;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\AddressInterface;
use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Api\Data\PaymentExtension;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Quote\Api\PaymentMethodManagementInterface;
use Magento\Quote\Model\ShippingAddressManagementInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class TotalInformationManagementPluginTest extends TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var CartManagementInterface
     */
    private $cartManagement;

    /**
     * @var CartItemRepositoryInterface
     */
    private $cartItemRepository;

    /**
     * @var PaymentMethodManagementInterface
     */
    private $paymentMethodManagement;

    /**
     * @var ShippingAddressManagementInterface
     */
    private $shippingAddressManagement;

    /**
     * @var TotalsInformationManagementInterface
     */
    private $totalsInformationManagement;

    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    public function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->cartRepository = $this->objectManager->create(CartRepositoryInterface::class);
        $this->cartManagement = $this->objectManager->create(CartManagementInterface::class);
        $this->cartItemRepository = $this->objectManager->create(CartItemRepositoryInterface::class);
        $this->paymentMethodManagement = $this->objectManager->create(PaymentMethodManagementInterface::class);
        $this->totalsInformationManagement = $this->objectManager->create(TotalsInformationManagementInterface::class);
        $this->shippingAddressManagement = $this->objectManager->create(ShippingAddressManagementInterface::class);
        $this->quoteFactory = $this->objectManager->create(QuoteFactory::class);
    }

    /**
     * @covers TotalInformationManagementPlugin::afterCalculate
     * @dataProvider afterCalculateDataProvider
     * @magentoConfigFixture default_store payment/checkmo/active 1
     * @magentoConfigFixture default_store payment/cashondelivery/active 1
     * @magentoConfigFixture default_store carriers/flatrate/active 1
     * @magentoConfigFixture default_store carriers/tablerate/active 1
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @param string $paymentCode
     * @param string $shippingCarrierCode
     * @param string $shippingMethodCode
     */
    public function testAfterCalculate($paymentCode, $shippingCarrierCode, $shippingMethodCode)
    {
        /** @var QuoteResource $quoteResource */
        $quoteResource = $this->objectManager->get(QuoteResource::class);
        $quoteResource->getConnection()->delete($quoteResource->getMainTable());

        $product = $this->getProduct();
        $payment = $this->getPayment($paymentCode);
        $shippingAddress = $this->getShippingAddress();

        $this->setConditionsToAddress($shippingAddress, $payment->getMethod());

        //Create cart and add product to it
        $cartId = $this->cartManagement->createEmptyCart();
        $this->addProductToCart($product, $cartId);

        //Assign shipping address
        $this->shippingAddressManagement->assign($cartId, $shippingAddress);
        $shippingAddress = $this->shippingAddressManagement->get($cartId);

        //Calculate totals
        $totals = $this->getTotals($shippingAddress, $shippingCarrierCode, $shippingMethodCode);
        $this->totalsInformationManagement->calculate($cartId, $totals);

        $quote = $this->quoteFactory->create();
        $quoteResource->load($quote, $cartId, 'quote_id');

        $this->assertEquals($paymentCode, $quote->getPaymentCode());
        $this->assertEquals($cartId, $quote->getQuoteId());
    }

    /**
     * @return ProductInterface
     */
    private function getProduct()
    {
        /** @var ProductRepositoryInterface $productRepository */
        $productRepository = $this->objectManager->create(ProductRepositoryInterface::class);
        $product = $productRepository->get('simple');
        $catalogProduct = $this->objectManager->get(Product::class);
        $catalogProduct->setSkipSaleableCheck(true);
        $product->setStoreId(1);
        $product->setOptions(null);
        $productRepository->save($product);

        return $product;
    }

    /**
     * @param string $paymentMethod
     * @return PaymentInterface
     */
    private function getPayment($paymentMethod)
    {
        $payment = $this->objectManager->create(PaymentInterface::class);
        $payment->setMethod($paymentMethod);
        $payment->setExtensionAttributes($this->objectManager->get(PaymentExtension::class));

        return $payment;
    }

    /**
     * @return AddressInterface
     */
    private function getShippingAddress()
    {
        $shippingAddress = $this->objectManager->create(AddressInterface::class);
        $shippingAddress->setFirstname('First');
        $shippingAddress->setLastname('Last');
        $shippingAddress->setEmail(null);
        $shippingAddress->setStreet('Street');
        $shippingAddress->setCity('City');
        $shippingAddress->setTelephone('1234567890');
        $shippingAddress->setPostcode('12345');
        $shippingAddress->setRegionId(12);
        $shippingAddress->setCountryId('US');
        $shippingAddress->setSameAsBilling(true);

        return $shippingAddress;
    }

    /**
     * @param AddressInterface $shippingAddress
     * @param string $paymentMethod
     */
    private function setConditionsToAddress(AddressInterface $shippingAddress, $paymentMethod)
    {
        $addressExtensionAttributes = $shippingAddress->getExtensionAttributes();
        $conditionsData = $this->objectManager->create(ConditionsAddressInterface::class);
        $conditionsData->setPaymentMethod($paymentMethod);
        $addressExtensionAttributes->setAdvancedConditions($conditionsData);
    }

    /**
     * @param ProductInterface $product
     * @param string $cartId
     */
    private function addProductToCart(ProductInterface $product, $cartId)
    {
        /** @var CartItemInterface $quoteItem */
        $quoteItem = $this->objectManager->create(CartItemInterface::class);
        $quoteItem->setQuoteId($cartId);
        $quoteItem->setProduct($product);
        $quoteItem->setQty(1);
        $this->cartItemRepository->save($quoteItem);
    }

    /**
     * @param AddressInterface $shippingAddress
     * @param string $carrierCode
     * @param string $methodCode
     * @return TotalsInformationInterface
     */
    private function getTotals(AddressInterface $shippingAddress, $carrierCode, $methodCode)
    {
        /** @var TotalsInformationInterface $totals */
        $totals = $this->objectManager->create(TotalsInformationInterface::class);
        $totals->setAddress($shippingAddress);
        $totals->setShippingCarrierCode($carrierCode);
        $totals->setShippingMethodCode($methodCode);

        return $totals;
    }

    /**
     * @return array
     */
    public function afterCalculateDataProvider()
    {
        return [
            [
                Checkmo::PAYMENT_METHOD_CHECKMO_CODE,
                'flatrate',
                'flatrate'
            ],
            [
                Cashondelivery::PAYMENT_METHOD_CASHONDELIVERY_CODE,
                'tablerate',
                'bestway'
            ]
        ];
    }
}
