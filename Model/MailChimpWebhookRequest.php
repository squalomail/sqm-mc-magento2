<?php
/**
 * mc-magento2 Magento Component
 *
 * @category SqualoMail
 * @package mc-magento2
 * @author Ebizmarts Team <info@ebizmarts.com>
 * @copyright Ebizmarts (http://ebizmarts.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @date: 5/26/17 11:32 AM
 * @file: MailChimpWebhookRequest.php
 */

namespace SqualoMail\SqmMcMagentoTwo\Model;

class MailChimpWebhookRequest extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init(\SqualoMail\SqmMcMagentoTwo\Model\ResourceModel\MailChimpWebhookRequest::class);
    }
}
