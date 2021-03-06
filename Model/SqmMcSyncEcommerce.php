<?php
/**
 * mc-magento2 Magento Component
 *
 * @category SqualoMail
 * @package mc-magento2
 * @author Ebizmarts Team <info@ebizmarts.com>
 * @copyright Ebizmarts (http://ebizmarts.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @date: 10/21/16 4:55 PM
 * @file: SqmMcSyncEcommerce.php
 */

namespace SqualoMail\SqmMcMagentoTwo\Model;

class SqmMcSyncEcommerce extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init(\SqualoMail\SqmMcMagentoTwo\Model\ResourceModel\SqmMcSyncEcommerce::class);
    }

    public function getByStoreIdType($storeId, $id, $type)
    {
        $this->getResource()->getByStoreIdType($this, $storeId, $id, $type);
        return $this;
    }
    public function markAllAsDeleted($id, $type, $relatedDeletedId)
    {
        $this->getResource()->markAllAsDeleted($this, $id, $type, $relatedDeletedId);
        return $this;
    }
    public function markAllAsModified($id, $type)
    {
        $this->getResource()->markAllAsModified($this, $id, $type);
        return $this;
    }
    public function deleteAllByIdType($id, $type, $sqmmcStoreId)
    {
        $this->getResource()->deleteAllByIdType($this, $id, $type, $sqmmcStoreId);
        return $this;
    }
    public function deleteAllByBatchid($batchId)
    {
        $this->getResource()->deleteAllByBatchid($this, $batchId);
    }
}
