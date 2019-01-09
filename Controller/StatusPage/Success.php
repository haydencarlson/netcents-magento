<?php

namespace NetCents\Merchant\Controller\StatusPage;

class Success extends \Magento\Framework\App\Action\Action {
    protected $_checkoutSession;
    protected $_urlBuilder;
    protected $_storeManager;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->_urlBuilder = $urlBuilder;
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    public function execute()
    {

        $this->getResponse()->setRedirect(
            $this->_getUrl('checkout/onepage/success')
        );
    }

    protected function _getUrl($path, $secure = null)
    {
        $store = $this->_storeManager->getStore(null);

        return $this->_urlBuilder->getUrl(
            $path,
            ['_store' => $store, '_secure' => $secure === null ? $store->isCurrentlySecure() : $secure]
        );
    }
}
