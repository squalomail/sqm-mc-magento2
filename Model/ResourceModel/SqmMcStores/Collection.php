<?php
/**
 * SqmMc Magento Component
 *
 * @category SqualoMail
 * @package SqmMcMagentoTwo
 * @author Ebizmarts Team <info@ebizmarts.com>
 * @copyright Ebizmarts (http://ebizmarts.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @date: 3/27/17 1:22 PM
 * @file: Collection.php
 */
namespace SqualoMail\SqmMcMagentoTwo\Model\ResourceModel\SqmMcStores;

class Collection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    protected function _construct()
    {
        $this->_init(
            \SqualoMail\SqmMcMagentoTwo\Model\SqmMcStores::class,
            \SqualoMail\SqmMcMagentoTwo\Model\ResourceModel\SqmMcStores::class
        );
    }
}
