<?php
/**
 * mc-magento2 Magento Component
 *
 * @category SqualoMail
 * @package mc-magento2
 * @author Ebizmarts Team <info@ebizmarts.com>
 * @copyright Ebizmarts (http://ebizmarts.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @date: 5/26/17 11:33 AM
 * @file: SqmMcWebhookRequest.php
 */
namespace SqualoMail\SqmMcMagentoTwo\Model\ResourceModel;

use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class SqmMcWebhookRequest extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('sqmmc_webhook_request', 'id');
    }
}
