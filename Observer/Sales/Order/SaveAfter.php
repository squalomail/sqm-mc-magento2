<?php
/**
 * mc-magento2 Magento Component
 *
 * @category SqualoMail
 * @package mc-magento2
 * @author Ebizmarts Team <info@ebizmarts.com>
 * @copyright Ebizmarts (http://ebizmarts.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @date: 2/15/17 3:38 PM
 * @file: SaveAfter.php
 */

namespace SqualoMail\SqmMcMagentoTwo\Observer\Sales\Order;

use Magento\Framework\Event\Observer;

class SaveAfter implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \SqualoMail\SqmMcMagentoTwo\Helper\Data
     */
    protected $_helper;

    /**
     * SaveAfter constructor.
     * @param \SqualoMail\SqmMcMagentoTwo\Helper\Data $helper
     */
    public function __construct(
        \SqualoMail\SqmMcMagentoTwo\Helper\Data $helper
    ) {
    
        $this->_helper      = $helper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $sqmmcStoreId = $this->_helper->getConfigValue(
            \SqualoMail\SqmMcMagentoTwo\Helper\Data::XML_SQM_MC_STORE,
            $order->getStoreId()
        );
        $this->_helper->saveEcommerceData(
            $sqmmcStoreId,
            $order->getId(),
            \SqualoMail\SqmMcMagentoTwo\Helper\Data::IS_ORDER,
            null,
            null,
            1,
            null,
            null,
            \SqualoMail\SqmMcMagentoTwo\Helper\Data::NEEDTORESYNC
        );
    }
}
