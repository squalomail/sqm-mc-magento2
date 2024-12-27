<?php
/**
 * mc-magento2 Magento Component
 *
 * @category SqualoMail
 * @package mc-magento2
 * @author Ebizmarts Team <info@ebizmarts.com>
 * @copyright Ebizmarts (http://ebizmarts.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @date: 1/18/18 12:30 PM
 * @file: SaveAfter.php
 */
namespace SqualoMail\SqmMcMagentoTwo\Observer\Adminhtml\Category;

class DeleteAfter implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \SqualoMail\SqmMcMagentoTwo\Helper\Data
     */
    protected $helper;

    /**
     * SaveAfter constructor.
     * @param \SqualoMail\SqmMcMagentoTwo\Helper\Data $helper
     */
    public function __construct(
        \SqualoMail\SqmMcMagentoTwo\Helper\Data $helper
    ) {
        $this->helper               = $helper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $category = $observer->getEvent()->getCategory();
        $storeId = $category->getStoreId();
        if ($this->helper->getConfigValue(\SqualoMail\SqmMcMagentoTwo\Helper\Data::XML_SYNC_CATEGORIES, $storeId)) {
            /**
             * @var \Magento\Catalog\Model\Category $category
             */
            $this->helper->markEcommerceAsDeleted($category->getId(), \SqualoMail\SqmMcMagentoTwo\Helper\Data::IS_CATEGORY);
        }
    }
}
