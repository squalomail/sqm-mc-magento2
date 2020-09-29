<?php
/**
 * Created by PhpStorm.
 * User: gonzalo
 * Date: 10/31/18
 * Time: 5:58 PM
 */

namespace SqualoMail\SqmMcMagentoTwo\Model\ResourceModel\SqmMcInterestGroup;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            \SqualoMail\SqmMcMagentoTwo\Model\SqmMcInterestGroup::class,
            \SqualoMail\SqmMcMagentoTwo\Model\ResourceModel\SqmMcInterestGroup::class
        );
    }
}
