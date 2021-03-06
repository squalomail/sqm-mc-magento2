<?php
/**
 * mc-magento2 Magento Component
 *
 * @category SqualoMail
 * @package mc-magento2
 * @author Ebizmarts Team <info@ebizmarts.com>
 * @copyright Ebizmarts (http://ebizmarts.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @date: 12/1/16 2:33 PM
 * @file: SqmMcSyncEcommerce.php
 */

namespace SqualoMail\SqmMcMagentoTwo\Model\ResourceModel;

use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class SqmMcSyncEcommerce extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('sqmmc_sync_ecommerce', 'id');
    }
    public function getByStoreIdType(\SqualoMail\SqmMcMagentoTwo\Model\SqmMcSyncEcommerce $chimp, $storeId, $id, $type)
    {
        $connection = $this->getConnection();
        $bind = ['store_id' => $storeId, 'type' => $type, 'related_id' => $id];
        $select = $connection->select()->from(
            $this->getTable('sqmmc_sync_ecommerce')
        )->where(
            'sqmmc_store_id = :store_id AND type = :type AND related_id = :related_id'
        );
        $data = $connection->fetchRow($select, $bind);
        if ($data) {
            $chimp->setData($data);
        }
        return $chimp;
    }
    public function markAllAsDeleted(
        \SqualoMail\SqmMcMagentoTwo\Model\SqmMcSyncEcommerce $chimp,
        $id,
        $type,
        $relatedDeletedId
    ) {
        $connection = $this->getConnection();
        $connection->update(
            $this->getTable('sqmmc_sync_ecommerce'),
            ['sqmmc_sync_deleted'=>1, 'deleted_related_id'=> $relatedDeletedId],
            ['related_id = ?'=> $id,'type = ?'=>$type]
        );
        return $this;
    }
    public function markAllAsModified(\SqualoMail\SqmMcMagentoTwo\Model\SqmMcSyncEcommerce $chimp, $id, $type)
    {
        $connection = $this->getConnection();
        $connection->update(
            $this->getTable('sqmmc_sync_ecommerce'),
            ['sqmmc_sync_modified'=>1],
            ['related_id = ?'=> $id, 'type = ?'=>$type]
        );
        return $this;
    }
    public function deleteAllByIdType(
        \SqualoMail\SqmMcMagentoTwo\Model\SqmMcSyncEcommerce $chimp,
        $id,
        $type,
        $sqmmcStoreId
    ) {
        $connection = $this->getConnection();
        $connection->delete(
            $this->getTable('sqmmc_sync_ecommerce'),
            ['related_id = ?'=> $id, 'type = ?'=>$type, 'sqmmc_store_id = ?' => $sqmmcStoreId]
        );
        return $this;
    }
    public function deleteAllByBatchId(\SqualoMail\SqmMcMagentoTwo\Model\SqmMcSyncEcommerce $chimp, $batchId)
    {
        $connection = $this->getConnection();
        $connection->delete($this->getTable('sqmmc_sync_ecommerce'), ['batch_id = ?' => $batchId]);
        return $this;
    }
}
