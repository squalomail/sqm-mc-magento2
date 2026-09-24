<?php
/**
 * SqmMc Magento Component
 *
 * @category SqualoMail
 * @package SqmMcMagentoTwo
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

        // Never call the API while rendering the storefront (it blocked uncached pages for up to
        // the API timeout whenever the URL was missing); Cron\Ecommerce fetches a missing URL.
        $load = $this->_scopeConfig->isSetFlag(
            \SqualoMail\SqmMcMagentoTwo\Helper\Data::XML_LOAD_SITE_SCRIPT, ScopeInterface::SCOPE_STORES,
            $storeId
        );
        return ($active && $load) ? (string)$url : '';
    }

    /**
     * Pop-up code copied from the Squalo app (Forms > Pop-ups > Websites).
     *
     * @return string
     */
    public function getPopupSnippet()
    {
        $storeId = $this->_storeManager->getStore()->getId();
        if (!$this->_scopeConfig->getValue(
            \SqualoMail\SqmMcMagentoTwo\Helper\Data::XML_PATH_ACTIVE, ScopeInterface::SCOPE_STORES,
            $storeId
        )) {
            return '';
        }
        return trim((string)$this->_scopeConfig->getValue(
            \SqualoMail\SqmMcMagentoTwo\Helper\Data::XML_POPUP_SNIPPET, ScopeInterface::SCOPE_STORES,
            $storeId
        ));
    }
}
