<?php
namespace NetCents\Merchant\Controller\StatusPage;

use Magento\Setup\Exception;
use NetCents\Merchant\Model\Payment as PaymentModel;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Sales\Model\Order;
use Magento\Framework\App\Request\Http;

class Callback extends Action {
    protected $order;
    protected $paymentModel;
    protected $client;
    protected $httpRequest;

    /**
     * @param Context $context
     * @param Order $order
     * @param PaymentModel $paymentModel
     * @internal param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param Http $request
     */
    public function __construct(
        Context $context,
        Order $order,
        PaymentModel $paymentModel,
        Http $request
    ) {
        parent::__construct($context);
        $this->order = $order;
        $this->paymentModel = $paymentModel;
        $this->httpRequest = $request;
    }

    public function execute() {
        $data = json_decode(base64_decode($this->getRequest()->getPost('data')));
        if (!is_null($data)) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $order = $objectManager->create('\Magento\Sales\Model\Order') ->load($data->external_id);
            if ($order) {
                $status = $data->transaction_status;
                if ($status == 'paid') {
                    $order->setStatus('complete')->setState('complete')->save();
                }
                if ($status == 'overpaid' || $status == 'underpaid') {
                    $order->setStatus('payment_review')->setState('payment_review')->save();
                }
                $order->save();
                $this->getResponse()->setBody('*ok*')->sendResponse();
            }
            else {
                $this->getResponse()->setBody('*error*')->sendResponse();
            }
        }
        else {
            $this->getResponse()->setBody('*error*')->sendResponse();
        }
    }
}
