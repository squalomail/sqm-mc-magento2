<?php
/**
 * MailChimp Magento Component
 *
 * @category SqualoMail
 * @package SqmMcMagentoTwo
 * @author Ebizmarts Team <info@ebizmarts.com>
 * @copyright Ebizmarts (http://ebizmarts.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @date: 11/9/17 5:20 PM
 * @file: SaveBefore.php
 */
namespace SqualoMail\SqmMcMagentoTwo\Observer\Subscriber;

use Magento\Framework\Event\Observer;

class SaveBefore implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \SqualoMail\SqmMcMagentoTwo\Model\SqmMcSyncEcommerce
     */
    protected $_ecommerce;
    /**
     * @var \SqualoMail\SqmMcMagentoTwo\Helper\Data
     */
    protected $_helper;
    /**
     * @var \SqualoMail\SqmMcMagentoTwo\Model\Api\Subscriber
     */
    protected $_subscriberApi;

    /**
     * SaveBefore constructor.
     * @param \SqualoMail\SqmMcMagentoTwo\Model\SqmMcSyncEcommerce $ecommerce
     * @param \SqualoMail\SqmMcMagentoTwo\Helper\Data $helper
     * @param \SqualoMail\SqmMcMagentoTwo\Model\Api\Subscriber $subscriberApi
     */
    public function __construct(
        \SqualoMail\SqmMcMagentoTwo\Model\SqmMcSyncEcommerce $ecommerce,
        \SqualoMail\SqmMcMagentoTwo\Helper\Data $helper,
        \SqualoMail\SqmMcMagentoTwo\Model\Api\Subscriber $subscriberApi
    ) {

        $this->_ecommerce           = $ecommerce;
        $this->_helper              = $helper;
        $this->_subscriberApi       = $subscriberApi;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /**
         * @var $subscriber \Magento\Newsletter\Model\Subscriber
         */
        $subscriber = $observer->getSubscriber();

        $this->_subscriberApi->update($subscriber);
    }
}
