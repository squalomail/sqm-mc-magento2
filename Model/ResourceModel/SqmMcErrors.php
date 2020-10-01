<?php
/**
 * mc-magento2 Magento Component
 *
 * @category SqualoMail
 * @package mc-magento2
 * @author Ebizmarts Team <info@ebizmarts.com>
 * @copyright Ebizmarts (http://ebizmarts.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @date: 10/17/16 1:57 PM
 * @file: SqmMcErrors.php
 */
namespace SqualoMail\SqmMcMagentoTwo\Model\ResourceModel;

use Magento\Framework\DB\Select;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class SqmMcErrors extends AbstractDb
{
    protected function _construct()
    {
        $this->_init('sqmmc_errors', 'id');
    }
    public function getByStoreIdType(\SqualoMail\SqmMcMagentoTwo\Model\SqmMcErrors $errors, $storeId, $id, $type)
    {
        $connection = $this->getConnection();
        $bind = ['store_id' => $storeId, 'regtype' => $type, 'original_id' => $id];
        $select = $connection->select()->from(
            $this->getTable('sqmmc_errors')
        )->where(
            'store_id = :store_id AND regtype = :regtype AND original_id = :original_id'
        );
        $data = $connection->fetchRow($select, $bind);
        if ($data) {
            $errors->setData($data);
        }
        return $errors;
    }
}
