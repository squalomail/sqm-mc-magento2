<?php
/**
 * MailChimp Magento Component
 *
 * @category SqualoMail
 * @package MailChimp
 * @author Ebizmarts Team <info@ebizmarts.com>
 * @copyright Ebizmarts (http://ebizmarts.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @date: 11/29/17 2:49 PM
 * @file: Customer.php
 */

namespace SqualoMail\SqmMcMagentoTwo\Block\Adminhtml\Customer\Edit\Tabs\View;

class Customer extends \Magento\Backend\Block\Template
{
    /**
     * @var \SqualoMail\SqmMcMagentoTwo\Helper\Data
     */
    protected $helper;
    /**
     * @var \Magento\Newsletter\Model\SubscriberFactory
     */
    protected $subscriberFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * Customer constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \SqualoMail\SqmMcMagentoTwo\Helper\Data $helper
     * @param \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \SqualoMail\SqmMcMagentoTwo\Helper\Data $helper,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        \Magento\Framework\Registry $registry,
        array $data
    ) {
    
        parent::__construct($context, $data);
        $this->helper               = $helper;
        $this->subscriberFactory    = $subscriberFactory;
        $this->registry             = $registry;
    }

    public function getInterest()
    {
        $subscriber = $this->subscriberFactory->create();
        $customerId = $this->registry->registry(\Magento\Customer\Controller\RegistryConstants::CURRENT_CUSTOMER_ID);
        $subscriber->loadByCustomerId($customerId);
        return $this->helper->getSubscriberInterest($subscriber->getSubscriberId(), $subscriber->getStoreId());
    }
}
