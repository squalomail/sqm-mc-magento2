<?php
/**
 * MailChimp Magento Component
 *
 * @category SqualoMail
 * @package MailChimp
 * @author Ebizmarts Team <info@ebizmarts.com>
 * @copyright Ebizmarts (http://ebizmarts.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @date: 11/20/17 5:06 PM
 * @file: Success.php
 */

namespace SqualoMail\SqmMcMagentoTwo\Controller\Checkout;

class Success extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \SqualoMail\SqmMcMagentoTwo\Helper\Data
     */
    protected $_helper;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_pageFactory;
    /**
     * @var \Magento\Newsletter\Model\SubscriberFactory
     */
    protected $_subscriberFactory;
    /**
     * @var \SqualoMail\SqmMcMagentoTwo\Model\MailChimpInterestGroupFactory
     */
    protected $_interestGroupFactory;

    /**
     * Success constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $pageFactory
     * @param \SqualoMail\SqmMcMagentoTwo\Helper\Data $helper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
     * @param \SqualoMail\SqmMcMagentoTwo\Model\MailChimpInterestGroupFactory $interestGroupFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \SqualoMail\SqmMcMagentoTwo\Helper\Data $helper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        \SqualoMail\SqmMcMagentoTwo\Model\MailChimpInterestGroupFactory $interestGroupFactory
    ) {
    
        $this->_pageFactory         =$pageFactory;
        $this->_helper              = $helper;
        $this->_checkoutSession     = $checkoutSession;
        $this->_subscriberFactory   = $subscriberFactory;
        $this->_interestGroupFactory= $interestGroupFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $params     = $this->getRequest()->getParams();
        $order = $this->_checkoutSession->getLastRealOrder();
        /**
         * @var $subscriber \Magento\Newsletter\Model\Subscriber
         * @var $interestGroup \SqualoMail\SqmMcMagentoTwo\Model\MailChimpInterestGroup
         */
        $subscriber = $this->_subscriberFactory->create();
        $interestGroup = $this->_interestGroupFactory->create();
        try {
            $subscriber->loadByEmail($order->getCustomerEmail());
            if ($subscriber->getEmail()==$order->getCustomerEmail()) {
                if ($subscriber->getStatus()==\Magento\Newsletter\Model\Subscriber::STATUS_UNSUBSCRIBED) {
                    $subscriber->subscribe($subscriber->getEmail());
                }
                $interestGroup->getBySubscriberIdStoreId($subscriber->getSubscriberId(), $subscriber->getStoreId());
                $interestGroup->setGroupdata($this->_helper->serialize($params));
                $interestGroup->setSubscriberId($subscriber->getSubscriberId());
                $interestGroup->setStoreId($subscriber->getStoreId());
                $interestGroup->setUpdatedAt($this->_helper->getGmtDate());
                $interestGroup->getResource()->save($interestGroup);
                $listId = $this->_helper->getGeneralList($order->getStoreId());
                $this->_updateSubscriber($listId, $subscriber->getId(), $this->_helper->getGmtDate(), '', 1);
            } else {
                $this->_subscriberFactory->create()->subscribe($order->getCustomerEmail());
                $subscriber->loadByEmail($order->getCustomerEmail());
                $interestGroup->getBySubscriberIdStoreId($subscriber->getSubscriberId(), $subscriber->getStoreId());
                $interestGroup->setGroupdata($this->_helper->serialize($params));
                $interestGroup->setSubscriberId($subscriber->getSubscriberId());
                $interestGroup->setStoreId($subscriber->getStoreId());
                $interestGroup->setUpdatedAt($this->_helper->getGmtDate());
                $interestGroup->getResource()->save($interestGroup);
            }
        } catch (\Exception $e) {
            $this->_helper->log($e->getMessage());
        }
        $this->messageManager->addSuccessMessage(__('Thanks for sharing your interest with us'));
        $this->_redirect($this->_helper->getBaserUrl(
            $order->getStoreId(),
            \Magento\Framework\UrlInterface::URL_TYPE_WEB
        ));
    }
    protected function _updateSubscriber(
        $listId,
        $entityId,
        $sync_delta = null,
        $sync_error = null,
        $sync_modified = null
    ) {
        $this->_helper->saveEcommerceData(
            $listId,
            $entityId,
            \SqualoMail\SqmMcMagentoTwo\Helper\Data::IS_SUBSCRIBER,
            $sync_delta,
            $sync_error,
            $sync_modified
        );
    }
}