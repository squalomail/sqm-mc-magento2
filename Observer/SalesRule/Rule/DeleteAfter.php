<?php
/**
 * mc-magento2 Magento Component
 *
 * @category Ebizmarts
 * @package mc-magento2
 * @author Ebizmarts Team <info@ebizmarts.com>
 * @copyright Ebizmarts (http://ebizmarts.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @date: 10/19/17 5:26 PM
 * @file: DeleteAfter.php
 */
namespace SqualoMail\SqmMcMagentoTwo\Observer\SalesRule\Rule;

use Magento\Framework\Event\Observer;

class DeleteAfter implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \SqualoMail\SqmMcMagentoTwo\Model\MailChimpSyncEcommerce
     */
    protected $_ecommerce;
    /**
     * @var \SqualoMail\SqmMcMagentoTwo\Helper\Data
     */
    protected $_helper;

    /**
     * SaveAfter constructor.
     * @param \SqualoMail\SqmMcMagentoTwo\Model\MailChimpSyncEcommerce $ecommerce
     * @param \SqualoMail\SqmMcMagentoTwo\Helper\Data $helper
     */
    public function __construct(
        \SqualoMail\SqmMcMagentoTwo\Model\MailChimpSyncEcommerce $ecommerce,
        \SqualoMail\SqmMcMagentoTwo\Helper\Data $helper
    ) {

        $this->_ecommerce   = $ecommerce;
        $this->_helper      = $helper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $rule = $observer->getEvent()->getRule();
        $ruleId = $rule->getRuleId();
        $this->_helper->markEcommerceAsDeleted($ruleId, \SqualoMail\SqmMcMagentoTwo\Helper\Data::IS_PROMO_RULE);
    }
}
