<?php
/**
 * mc-magento2 Magento Component
 *
 * @category SqualoMail
 * @package mc-magento2
 * @author Ebizmarts Team <info@ebizmarts.com>
 * @copyright Ebizmarts (http://ebizmarts.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @date: 10/17/16 4:07 PM
 * @file: SqmMcSyncBatches.php
 */
namespace SqualoMail\SqmMcMagentoTwo\Model;

class SqmMcSyncBatches extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init(\SqualoMail\SqmMcMagentoTwo\Model\ResourceModel\SqmMcSyncBatches::class);
    }
}
