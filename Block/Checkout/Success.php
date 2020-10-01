<?php
/**
 * MailChimp Magento Component
 *
 * @category SqualoMail
 * @package SqmMcMagentoTwo
 * @author Ebizmarts Team <info@ebizmarts.com>
 * @copyright Ebizmarts (http://ebizmarts.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @date: 11/13/17 4:41 PM
 * @file: Success.php
 */
namespace SqualoMail\SqmMcMagentoTwo\Block\Checkout;

class Success extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;
    /**
     * @var \SqualoMail\SqmMcMagentoTwo\Helper\Data
     */
    protected $_helper;
    /**
     * @var \Magento\Newsletter\Model\SubscriberFactory
     */
    protected $_subscriberFactory;
    /**
     * @var \SqualoMail\SqmMcMagentoTwo\Model\SqmMcInterestGroupFactory
     */
    protected $_interestGroupFactory;
    /**
     * @var \Magento\Framework\View\Element\Template\Context
     */
    protected $_context;

    /**
     * Success constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \SqualoMail\SqmMcMagentoTwo\Helper\Data $helper
     * @param \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
     * @param \SqualoMail\SqmMcMagentoTwo\Model\SqmMcInterestGroupFactory $interestGroupFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \SqualoMail\SqmMcMagentoTwo\Helper\Data $helper,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        \SqualoMail\SqmMcMagentoTwo\Model\SqmMcInterestGroupFactory $interestGroupFactory,
        array $data
    ) {
    
        parent::__construct($context, $data);
        $this->_checkoutSession     = $checkoutSession;
        $this->_helper              = $helper;
        $this->_subscriberFactory   = $subscriberFactory;
        $this->_interestGroupFactory= $interestGroupFactory;
        $this->_context             = $context;
    }

    public function getInterest()
    {
        $order = $this->_checkoutSession->getLastRealOrder();
        /**
         * @var $subscriber \Magento\Newsletter\Model\Subscriber
         */
        $subscriber = $this->_subscriberFactory->create();
        $subscriber->loadByEmail($order->getCustomerEmail());

        return $this->_helper->getSubscriberInterest($subscriber->getSubscriberId(), $subscriber->getStoreId());
    }
    protected function getValues($category)
    {
        $rc =[];
        foreach ($category as $c) {
            $rc[] = ['value'=>$c['id'],'label'=>$c['name']];
        }
        return $rc;
    }
    public function getMessageBefore()
    {
        return $this->_helper->getConfigValue(\SqualoMail\SqmMcMagentoTwo\Helper\Data::XML_INTEREST_SUCCESS_HTML_BEFORE);
    }
    public function getMessageAfter()
    {
        return $this->_helper->getConfigValue(\SqualoMail\SqmMcMagentoTwo\Helper\Data::XML_INTEREST_SUCCESS_HTML_AFTER);
    }
    public function getFormUrl()
    {
        $order = $this->_checkoutSession->getLastRealOrder();
        return $this->_helper->getSuccessInterestUrl($order->getStoreId());
    }
    public function _toHtml()
    {
        if (!$this->_helper->isSqmMcEnabled($this->_context->getStoreManager()->getStore()->getId())) {
            return "";
        }
        return parent::_toHtml();
    }
}
