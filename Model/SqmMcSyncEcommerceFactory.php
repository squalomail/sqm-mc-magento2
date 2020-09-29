<?php
/**
 * mc-magento2 Magento Component
 *
 * @category SqualoMail
 * @package mc-magento2
 * @author Ebizmarts Team <info@ebizmarts.com>
 * @copyright Ebizmarts (http://ebizmarts.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @date: 2/28/17 7:24 PM
 * @file: SqmMcSyncEcommerceFactory.php
 */

namespace SqualoMail\SqmMcMagentoTwo\Model;

class SqmMcSyncEcommerceFactory
{

    protected $_objectManager;
    protected $_instanceName;

    /**
     * SqmMcSyncEcommerceFactory constructor.
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $instanceName = \SqualoMail\SqmMcMagentoTwo\Model\SqmMcSyncEcommerce::class
    ) {
    
        $this->_objectManager   = $objectManager;
        $this->_instanceName    = $instanceName;
    }

    /**
     * @param array $data
     * @return \SqualoMail\SqmMcMagentoTwo\Model\SqmMcSyncEcommerce
     */
    public function create(array $data = [])
    {
        return $this->_objectManager->create($this->_instanceName, $data);
    }
}
