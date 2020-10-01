<?php
/**
 * MailChimp Magento Component
 *
 * @category SqualoMail
 * @package MailChimp
 * @author Ebizmarts Team <info@ebizmarts.com>
 * @copyright Ebizmarts (http://ebizmarts.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @date: 5/8/17 12:00 PM
 * @file: Sqmmcjs.php
 */

namespace SqualoMail\SqmMcMagentoTwo\Block;

use Magento\Store\Model\ScopeInterface;

class Sqmmcjs extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \SqualoMail\SqmMcMagentoTwo\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Sqmmcjs constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \SqualoMail\SqmMcMagentoTwo\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \SqualoMail\SqmMcMagentoTwo\Helper\Data $helper,
        array $data
    ) {
        parent::__construct($context, $data);
        $this->_helper          = $helper;
        $this->_storeManager    = $context->getStoreManager();
    }

    public function getJsUrl()
    {
        $storeId = $this->_storeManager->getStore()->getId();

        $url = $this->_scopeConfig->getValue(
            \SqualoMail\SqmMcMagentoTwo\Helper\Data::XML_SQM_MC_JS_URL, ScopeInterface::SCOPE_STORES,
            $storeId
        );
        $active = $this->_scopeConfig->getValue(
            \SqualoMail\SqmMcMagentoTwo\Helper\Data::XML_PATH_ACTIVE, ScopeInterface::SCOPE_STORES,
            $storeId
        );

        // if we have URL cached or integration is disabled
        // then avoid initialization of Mailchimp Helper and all linked classes (~30 classes)
        if ($active && !$url) {
            $url = $this->_helper->getJsUrl($storeId);
        }

        return $url;
    }
}
