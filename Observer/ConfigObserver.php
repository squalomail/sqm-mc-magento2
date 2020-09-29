<?php
/**
 * mc-magento2 Magento Component
 *
 * @category SqualoMail
 * @package mc-magento2
 * @author Ebizmarts Team <info@ebizmarts.com>
 * @copyright Ebizmarts (http://ebizmarts.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @date: 5/23/17 12:22 PM
 * @file: ConfigObserver.php
 */

namespace SqualoMail\SqmMcMagentoTwo\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer as EventObserver;

class ConfigObserver implements ObserverInterface
{
    /**
     * @var \Magento\Store\Model\StoreManager
     */
    protected $_storeManager;
    /**
     * @var \SqualoMail\SqmMcMagentoTwo\Helper\Data
     */
    protected $_helper;
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * ConfigObserver constructor.
     * @param \Magento\Store\Model\StoreManager $storeManager
     * @param \Magento\Framework\Registry $registry
     * @param \SqualoMail\SqmMcMagentoTwo\Helper\Data $helper
     */
    public function __construct(
        \Magento\Store\Model\StoreManager $storeManager,
        \Magento\Framework\Registry $registry,
        \SqualoMail\SqmMcMagentoTwo\Helper\Data $helper
    ) {
    
        $this->_helper          = $helper;
        $this->_storeManager    = $storeManager;
        $this->_registry        = $registry;
    }

    public function execute(EventObserver $observer)
    {
        $oldListId  = $this->_registry->registry('oldListId');
        $apiKey     = $this->_registry->registry('apiKey');
        $mustDelete = true;

        foreach ($this->_storeManager->getStores() as $storeId => $val) {
            $listId = $this->_helper->getConfigValue(\SqualoMail\SqmMcMagentoTwo\Helper\Data::XML_PATH_LIST, $storeId);
            if ($listId == $oldListId) {
                $mustDelete = false;
            }
        }
        if ($mustDelete) {
            $this->_helper->deleteWebHook($apiKey, $oldListId);
        }

        $this->_registry->unregister('oldListId');
        $this->_registry->unregister('apiKey');
    }
}
