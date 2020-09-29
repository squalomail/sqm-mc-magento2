<?php
/**
 * mc-magento2 Magento Component
 *
 * @category Ebizmarts
 * @package mc-magento2
 * @author Ebizmarts Team <info@ebizmarts.com>
 * @copyright Ebizmarts (http://ebizmarts.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @date: 10/30/17 10:27 AM
 * @file: VarsMap.php
 */

namespace SqualoMail\SqmMcMagentoTwo\Model\Config\Backend;

class VarsMap extends \Magento\Framework\App\Config\Value
{
    /**
     * @var \SqualoMail\SqmMcMagentoTwo\Helper\VarsMap
     */
    private $_varsHelper;
    /**
     * @var \SqualoMail\SqmMcMagentoTwo\Helper\Data
     */
    private $_helper;

    /**
     * VarsMap constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \SqualoMail\SqmMcMagentoTwo\Helper\VarsMap $varsMap
     * @param \SqualoMail\SqmMcMagentoTwo\Helper\Data $helper
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \SqualoMail\SqmMcMagentoTwo\Helper\VarsMap $varsMap,
        \SqualoMail\SqmMcMagentoTwo\Helper\Data $helper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
    
        $this->_varsHelper = $varsMap;
        $this->_helper      = $helper;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }
    protected function _afterLoad()
    {
        $value = $this->getValue();
        $value = $this->_varsHelper->makeArrayFieldValue($value);
        $this->setValue($value);
    }

    /**
     * Prepare data before save
     *
     * @return void
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        if (is_array($value)) {
            $value = $this->_varsHelper->makeStorableArrayFieldValue($value);
        }
        $this->setValue($value);
    }
}
