<?php
/**
 * SqualoMail_SqmMcMagentoTwo Magento JS component
 *
 * @category    SqualoMail
 * @package     SqualoMail_SqmMcMagentoTwo
 * @author      Ebizmarts Team <info@ebizmarts.com>
 * @copyright   Ebizmarts (http://ebizmarts.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace SqualoMail\SqmMcMagentoTwo\Model\Plugin;

class Subscriber
{
    /**
     * @var \SqualoMail\SqmMcMagentoTwo\Helper\Data
     */
    protected $_helper;
    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $_customer;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @param \SqualoMail\SqmMcMagentoTwo\Helper\Data $helper
     * @param \Magento\Customer\Model\ResourceModel\CustomerRepository $customer
     * @param \Magento\Customer\Model\Session $customerSession
     */
    protected $_api = null;

    /**
     * Subscriber constructor.
     * @param \SqualoMail\SqmMcMagentoTwo\Helper\Data $helper
     * @param \Magento\Customer\Model\ResourceModel\CustomerRepository $customer
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \SqualoMail\SqmMcMagentoTwo\Helper\Data $helper,
        \Magento\Customer\Model\ResourceModel\CustomerRepository $customer,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
    
        $this->_helper          = $helper;
        $this->_customer        = $customer;
        $this->_customerSession = $customerSession;
        $this->_storeManager    = $storeManager;
    }

    /**
     * @param \Magento\Newsletter\Model\Subscriber $subscriber
     * @param $customerId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeUnsubscribeCustomerById(
        \Magento\Newsletter\Model\Subscriber $subscriber,
        $customerId
    ) {
        $storeId = $this->getStoreIdFromSubscriber($subscriber);
        if ($this->_helper->isSqmMcEnabled($storeId)) {
            if (!$this->_helper->getConfigValue(\SqualoMail\SqmMcMagentoTwo\Helper\Data::XML_MAGENTO_MAIL, $storeId)) {
                $subscriber->setImportMode(true);
            }
            $subscriber->loadByCustomerId($customerId);
            if ($subscriber->isSubscribed()) {
                $api = $this->_helper->getApi($storeId);
                try {
                    $md5HashEmail = hash('md5', strtolower($subscriber->getSubscriberEmail()));
                    $api->lists->members->update(
                        $this->_helper->getDefaultList($storeId),
                        $md5HashEmail,
                        null,
                        'unsubscribed'
                    );
                } catch (\SqualoMailMc_Error $e) {
                    $this->_helper->log($e->getFriendlyMessage());
                }
            }
        }
        return [$customerId];
    }

    /**
     * @param \Magento\Newsletter\Model\Subscriber $subscriber
     * @param $customerId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function beforeSubscribeCustomerById(
        \Magento\Newsletter\Model\Subscriber $subscriber,
        $customerId
    ) {

        $storeId = $this->getStoreIdFromSubscriber($subscriber);
        if ($this->_helper->isSqmMcEnabled($storeId)) {
            $subscriber->loadByCustomerId($customerId);
            if (!$subscriber->isSubscribed()) {
                if (!$this->_helper->getConfigValue(\SqualoMail\SqmMcMagentoTwo\Helper\Data::XML_MAGENTO_MAIL, $storeId)) {
                    $subscriber->setImportMode(true);
                }
                if ($this->_helper->isSqmMcEnabled($storeId)) {
                    $customer = $this->_customer->getById($customerId);
                    $email = $customer->getEmail();
                    $mergeVars = $this->_helper->getMergeVarsBySubscriber($subscriber, $email);
                    $api = $this->_helper->getApi($storeId);
                    if ($this->_helper->isDoubleOptInEnabled($storeId)) {
                        $status = 'pending';
                    } else {
                        $status = 'subscribed';
                    }
                    try {
                        $emailHash = hash('md5', strtolower($customer->getEmail()));
                        $api->lists->members->addOrUpdate(
                            $this->_helper->getDefaultList($storeId),
                            $emailHash,
                            null,
                            $status,
                            $mergeVars,
                            null,
                            null,
                            null,
                            null,
                            $email,
                            $status
                        );
                    } catch (\SqualoMailMc_Error $e) {
                        $this->_helper->log($e->getFriendlyMessage());
                    }
                }
            }
        }
        return [$customerId];
    }

    /**
     * @param \Magento\Newsletter\Model\Subscriber $subscriber
     * @param $email
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function beforeSubscribe(
        \Magento\Newsletter\Model\Subscriber $subscriber,
        $email
    ) {

        $storeId = $this->getStoreIdFromSubscriber($subscriber);
        if ($this->_helper->isSqmMcEnabled($storeId)) {
            if (!$this->_helper->getConfigValue(\SqualoMail\SqmMcMagentoTwo\Helper\Data::XML_MAGENTO_MAIL, $storeId)) {
                $subscriber->setImportMode(true);
            }
            $storeId = $this->_storeManager->getStore()->getId();

            if ($this->_helper->isSqmMcEnabled($storeId)) {
                $api = $this->_helper->getApi($storeId);
                if ($this->_helper->isDoubleOptInEnabled($storeId)) {
                    $status = 'pending';
                } else {
                    $status = 'subscribed';
                }
                $mergeVars = $this->_helper->getMergeVarsBySubscriber($subscriber, $email);
                try {
                    $md5HashEmail = hash('md5', strtolower($email));
                    $return = $api->lists->members->addOrUpdate(
                        $this->_helper->getDefaultList($storeId),
                        $md5HashEmail,
                        null,
                        $status,
                        $mergeVars,
                        null,
                        null,
                        null,
                        null,
                        $email,
                        $status
                    );
                } catch (\SqualoMailMc_Error $e) {
                    $this->_helper->log($e->getFriendlyMessage());
                }
            }
        }
        return [$email];
    }

    /**
     * @param \Magento\Newsletter\Model\Subscriber $subscriber
     * @return null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeUnsubscribe(
        \Magento\Newsletter\Model\Subscriber $subscriber
    ) {
        $storeId = $this->getStoreIdFromSubscriber($subscriber);
        if ($this->_helper->isSqmMcEnabled($storeId)) {
            if (!$this->_helper->getConfigValue(\SqualoMail\SqmMcMagentoTwo\Helper\Data::XML_MAGENTO_MAIL, $storeId)) {
                $subscriber->setImportMode(true);
            } 
            $api = $this->_helper->getApi($storeId);
            try {
                $md5HashEmail = hash('md5', strtolower($subscriber->getSubscriberEmail()));
                $api->lists->members->update(
                    $this->_helper->getDefaultList($storeId),
                    $md5HashEmail,
                    null,
                    'unsubscribed'
                );
            } catch (\SqualoMailMc_Error $e) {
                $this->_helper->log($e->getFriendlyMessage());
            }
        }
    }

    /**
     * @param \Magento\Newsletter\Model\Subscriber $subscriber
     * @return null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterDelete(
        \Magento\Newsletter\Model\Subscriber $subscriber
    ) {

        $storeId = $this->getStoreIdFromSubscriber($subscriber);
        if ($this->_helper->isSqmMcEnabled($storeId)) {
            $api = $this->_helper->getApi($storeId);
            if ($subscriber->isSubscribed()) {
                try {
                    $md5HashEmail = hash('md5', strtolower($subscriber->getSubscriberEmail()));
                    if ($subscriber->getCustomerId()) {
                        $api->lists->members->update(
                            $this->_helper->getDefaultList($storeId),
                            $md5HashEmail,
                            null,
                            'unsubscribed'
                        );
                    } else {
                        $api->lists->members->delete($this->_helper->getDefaultList($storeId), $md5HashEmail);
                    }
                } catch (\SqualoMailMc_Error $e) {
                    $this->_helper->log($e->getFriendlyMessage());
                }
            }
        }
        return null;
    }

    /**
     * @param \Magento\Newsletter\Model\Subscriber $subscriber
     * @return int
     */
    protected function getStoreIdFromSubscriber(\Magento\Newsletter\Model\Subscriber $subscriber)
    {
        return $subscriber->getStoreId();
    }

    /**
     * @param \Magento\Newsletter\Model\Subscriber $subscriber
     * @param $email
     * @param $websiteId
     * @return \Magento\Newsletter\Model\Subscriber
     */
    public function afterLoadBySubscriberEmail(\Magento\Newsletter\Model\Subscriber $subscriber, $email, $websiteId)
    {
        if ($this->_helper->isSqmMcEnabled($subscriber->getStoreId())) {
            try {
                if (!$this->_helper->getConfigValue(
                    \SqualoMail\SqmMcMagentoTwo\Helper\Data::XML_MAGENTO_MAIL,
                    $subscriber->getStoreId()
                )) {
                    $subscriber->setImportMode(true);
                }
            } catch (\Exception $exception) {
                $this->_helper->log($exception->getMessage());
            }
        }

        return $subscriber;
    }
    
    /**
     * @param \Magento\Newsletter\Model\Subscriber $subscriber
     * @param $customerId
     * @param $websiteId
     * @return \Magento\Newsletter\Model\Subscriber
     */
    public function afterLoadByCustomer(\Magento\Newsletter\Model\Subscriber $subscriber, $customerId, $websiteId)
    {
        try {
            if (!$this->_helper->getConfigValue(
                \SqualoMail\SqmMcMagentoTwo\Helper\Data::XML_MAGENTO_MAIL,
                $subscriber->getStoreId()
            )) {
                $subscriber->setImportMode(true);
            }
        } catch (\Exception $exception) {
            $this->_helper->log($exception->getMessage());
        }

        return $subscriber;
    }
}
