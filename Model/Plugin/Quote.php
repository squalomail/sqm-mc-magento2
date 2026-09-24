<?php
/**
 * mc-magento2 Magento Component
 *
 * @category SqualoMail
 * @package mc-magento2
 * @author Ebizmarts Team <info@ebizmarts.com>
 * @copyright Ebizmarts (http://ebizmarts.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @date: 4/25/17 7:40 PM
 * @file: Quote.php
 */
namespace SqualoMail\SqmMcMagentoTwo\Model\Plugin;

class Quote
{
    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface
     */
    protected $_cookieManager;
    /**
     * @var \SqualoMail\SqmMcMagentoTwo\Helper\Data
     */
    protected $_helper;

    /**
     * Quote constructor.
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \SqualoMail\SqmMcMagentoTwo\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \SqualoMail\SqmMcMagentoTwo\Helper\Data $helper
    ) {
    
        $this->_cookieManager = $cookieManager;
        $this->_helper  = $helper;
    }

    public function beforeBeforeSave(\Magento\Quote\Model\Quote $quote)
    {
        // Cookies are client-controlled: only accept values that fit the quote columns,
        // otherwise a strict SQL mode makes every cart save fail.
        $sqmmc_campaign_id = $this->_cookieManager->getCookie('sqmmc_campaign_id');
        if (is_string($sqmmc_campaign_id) && preg_match('/^[A-Za-z0-9_-]{1,16}$/', $sqmmc_campaign_id)) {
            $quote->setData('sqmmc_campaign_id', $sqmmc_campaign_id);
        }
        $sqmmc_landing_page = $this->_cookieManager->getCookie('sqmmc_landing_page');
        if (is_string($sqmmc_landing_page) && $sqmmc_landing_page !== '') {
            $quote->setData('sqmmc_landing_page', mb_substr($sqmmc_landing_page, 0, 512));
        }
    }
}
