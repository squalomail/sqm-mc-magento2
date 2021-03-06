<?php
/**
 * SqmMc Magento Component
 *
 * @category SqualoMail
 * @package SqmMcMagentoTwo
 * @author Ebizmarts Team <info@ebizmarts.com>
 * @copyright Ebizmarts (http://ebizmarts.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @date: 11/8/17 5:07 PM
 * @file: SafeAfter.php
 */
namespace SqualoMail\SqmMcMagentoTwo\Observer\Customer;

use Magento\Framework\Event\Observer;

class SaveBefore implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \SqualoMail\SqmMcMagentoTwo\Helper\Data
     */
    protected $_helper;
    /**
     * @var \Magento\Newsletter\Model\SubscriberFactory
     */
    protected $subscriberFactory;

    /**
     * SaveBefore constructor.
     * @param \SqualoMail\SqmMcMagentoTwo\Helper\Data $helper
     * @param \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
     */
    public function __construct(
        \SqualoMail\SqmMcMagentoTwo\Helper\Data $helper,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
    ) {

        $this->_helper              = $helper;
        $this->subscriberFactory    = $subscriberFactory;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /**
         * @var $customer \Magento\Customer\Model\Customer
         */
        $customer = $observer->getCustomer();
        $storeId  = $customer->getStoreId();
        if ($this->_helper->getConfigValue(\SqualoMail\SqmMcMagentoTwo\Helper\Data::XML_PATH_ACTIVE)) {
            if ($this->_helper->getConfigValue(\SqualoMail\SqmMcMagentoTwo\Helper\Data::XML_PATH_ECOMMERCE_ACTIVE)) {
                $sqmmcStoreId = $this->_helper->getConfigValue(
                    \SqualoMail\SqmMcMagentoTwo\Helper\Data::XML_SQM_MC_STORE,
                    $storeId
                );
                $this->_helper->saveEcommerceData(
                    $sqmmcStoreId,
                    $customer->getId(),
                    \SqualoMail\SqmMcMagentoTwo\Helper\Data::IS_CUSTOMER,
                    null,
                    null,
                    1
                );
            }
            $subscriber = $this->subscriberFactory->create();
            $subscriber->loadByEmail($customer->getEmail());
            if ($subscriber->getEmail() == $customer->getEmail()) {
                $this->_helper->markRegisterAsModified(
                    $subscriber->getId(),
                    \SqualoMail\SqmMcMagentoTwo\Helper\Data::IS_SUBSCRIBER
                );
            }
        }
    }
}
