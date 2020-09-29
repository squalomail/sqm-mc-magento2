<?php
/**
 * SqualoMail_sqm-mc-magento22 Magento component
 *
 * @category    SqualoMail
 * @package     SqualoMail_sqm-mc-magento22
 * @author      Ebizmarts Team <info@ebizmarts.com>
 * @copyright   Ebizmarts (http://ebizmarts.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
namespace SqualoMail\SqmMcMagentoTwo\Model\Plugin;

use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Sales\Api\CreditmemoRepositoryInterface as SalesCreditmemoRepositoryInterface;

class Creditmemo
{
    /**
     * @var \SqualoMail\SqmMcMagentoTwo\Helper\Data
     */
    private $_helper;

    /**
     * Ship constructor.
     * @param \SqualoMail\SqmMcMagentoTwo\Helper\Data $helper
     */
    public function __construct(
        \SqualoMail\SqmMcMagentoTwo\Helper\Data $helper
    ) {
        $this->_helper  = $helper;
    }
    public function afterSave(
        SalesCreditmemoRepositoryInterface $subject,
        CreditmemoInterface $creditmemo
    ) {
        $mailchimpStoreId = $this->_helper->getConfigValue(
            \SqualoMail\SqmMcMagentoTwo\Helper\Data::XML_MAILCHIMP_STORE,
            $creditmemo->getStoreId()
        );
        $this->_helper->saveEcommerceData(
            $mailchimpStoreId,
            $creditmemo->getOrderId(),
            \SqualoMail\SqmMcMagentoTwo\Helper\Data::IS_ORDER,
            null,
            null,
            1,
            null,
            null,
            \SqualoMail\SqmMcMagentoTwo\Helper\Data::NEEDTORESYNC
        );

        return $creditmemo;
    }
}
