<?php
namespace NetCents\Merchant\Model;

use Braintree\Exception;
use NetCents\Merchant\Library\NCWidgetClient\NCPaymentData;
use NetCents\Merchant\Library\NCWidgetClient\NCWidgetClient;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Payment\Model\Method\Logger;
use Magento\Sales\Model\Order;
use Magento\Store\Model\StoreManagerInterface;


class Payment extends AbstractMethod {
    const COINGATE_MAGENTO_VERSION = '1.0.6';
    const CODE = 'netcents_merchant';
    protected $_scopeConfig;
    protected $_code = 'netcents_merchant';
    protected $_isInitializeNeeded = true;
    protected $urlBuilder;
    protected $storeManager;
    protected $scClient;
    protected $resolver;


    /**
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param Data $paymentData
     * @param ScopeConfigInterface $scopeConfig
     * @param Logger $logger
     * @param UrlInterface $urlBuilder
     * @param StoreManagerInterface $storeManager
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     * @internal param ModuleListInterface $moduleList
     * @internal param TimezoneInterface $localeDate
     * @internal param CountryFactory $countryFactory
     * @internal param Http $response
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        Data $paymentData,
        ScopeConfigInterface $scopeConfig,
        Logger $logger,
        UrlInterface $urlBuilder,
        StoreManagerInterface $storeManager,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = array()
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );
        $this->_scopeConfig = $scopeConfig;
        $this->urlBuilder = $urlBuilder;
        $this->storeManager = $storeManager;
    }


    /**
     * @param Order $order
     * @return array
     */
    public function getNetCentsResponse(Order $order) {

        $uriCallback = $this->urlBuilder->getUrl('netcents/statusPage/callback');
        $uriSuccess =  $this->urlBuilder->getUrl('netcents/statusPage/success');
        $total = number_format($order->getGrandTotal(), 2, '.', '');

        $description = array();
        foreach ($order->getAllItems() as $item) {
            $description[] = number_format($item->getQtyOrdered(), 0) . ' × ' . $item->getName();
        }

        $payment = new NCPaymentData(
            $this->_scopeConfig->getValue('payment/netcents_merchant/api_fields/widget_id'),
            $order->getId(),
            $total,
            $uriSuccess,
            $order->getBillingAddress()->getFirstName(),
            $order->getBillingAddress()->getLastName(),
            $order->getBillingAddress()->getEmail(),
            $uriCallback,
            $this->_scopeConfig->getValue('payment/netcents_merchant/api_fields/api_key'),
            $this->_scopeConfig->getValue('payment/netcents_merchant/api_fields/secret_key'),
            $this->_scopeConfig->getValue('payment/netcents_merchant/api_fields/api_url'),
            $order->getOrderCurrencyCode()
        );

        try {
            $client = new NCWidgetClient($payment);

            $response = $client->encryptData();

            if ($response->body->status == 200) {
                return [
                    'status' => 'ok',
                    'redirect_url' => $this->_scopeConfig->getValue('payment/netcents_merchant/api_fields/api_url') . '/merchant/widget?data=' . $response->body->token . '&widget_id=' . $payment->widgetId
                ];
            } else {
                return [
                    'status' => 'error',
                    'errorCode' => 1,
                    'errorMsg' => 'Cant use this payment method at this time'
                ];
            }

        }
        catch (Exception $e) {
            return [
                'status' => 'error',
                'errorCode' => 1,
                'errorMsg' => 'Error: '.$e->getMessage()
            ];
        }
    }

    /**
     * Returns order status from configuration
     * @param string $configOption
     * @param string $defaultValue
     * @return mixed|string
     */
    protected function getStatusDataOrDefault($configOption, $defaultValue = 'pending') {
        $data = $this->getConfigData($configOption);
        if (!$data) {
            $data = $defaultValue;
        }

        return $data;
    }

    /**
     * Returns order status mapped to spectrocoin status
     * @param string $spectrocoinStatus
     * @return mixed|string
     */
    protected function getOrderStatus($spectrocoinStatus) {
        switch($spectrocoinStatus) {
            case OrderStatusEnum::$New:
                $statusOption = $this->getStatusDataOrDefault(
                    'payment_settings/order_status_new',
                    'new'
                );
                break;

            case OrderStatusEnum::$Expired:
                $statusOption = $this->getStatusDataOrDefault(
                    'payment_settings/order_status_expired',
                    'canceled'
                );
                break;

            case OrderStatusEnum::$Failed:
                $statusOption = $this->getStatusDataOrDefault(
                    'payment_settings/order_status_failed',
                    'closed'
                );
                break;

            case OrderStatusEnum::$Paid:
                $statusOption = $this->getStatusDataOrDefault(
                    'payment_settings/order_status_paid',
                    'complete'
                );
                break;

            case OrderStatusEnum::$Pending:
                $statusOption = $this->getStatusDataOrDefault(
                    'payment_settings/order_status_pending',
                    'pending_payment'
                );
                break;

            case OrderStatusEnum::$Test:
                $statusOption = $this->getStatusDataOrDefault(
                    'payment_settings/order_status_test',
                    'payment_review'
                );
                break;

            default:
                $statusOption = $this->getStatusDataOrDefault(
                    'payment_settings/order_status_test',
                    'pending_payment'
                );
        }

        return $statusOption;
    }

    public function updateOrderStatus(Order $order) {
        try {
//            $orderState = $this->getOrderStatus($callback->getStatus());

//            $order
//                ->setState($orderState, true)
//                ->setStatus($order->getConfig()->getStateDefaultStatus($orderState))
//                ->save();
//            return true;
        }
        catch (\Exception $e) {
            exit('Error occurred: ' . $e);
        }
    }

}
