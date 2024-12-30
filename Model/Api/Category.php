<?php
/**
 * mc-magento2 Magento Component
 *
 * @category SqualoMail
 * @package mc-magento2
 * @author Ebizmarts Team <info@ebizmarts.com>
 * @copyright Ebizmarts (http://ebizmarts.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @date: 10/12/24 12:00 PM
 * @file: Category.php
 */

namespace SqualoMail\SqmMcMagentoTwo\Model\Api;

class Category
{
    const MAX = 100;

    /**
     * @var \SqualoMail\SqmMcMagentoTwo\Helper\Data
     */
    protected $_helper;
    /**
     * @var \Magento\Catalog\Model\CategoryRepository
     */
    protected $_categoryRepository;
    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    protected $_categoryFactory;
    /**
     * @var string
     */
    protected $_batchId;
    /**
     * @var \SqualoMail\SqmMcMagentoTwo\Model\SqmMcSyncEcommerceFactory
     */
    protected $_chimpSyncEcommerce;
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $_categoryCollection;
    /**
     * @var \SqualoMail\SqmMcMagentoTwo\Model\ResourceModel\SqmMcSyncEcommerce\CollectionFactory
     */
    private $_syncCollection;
    /**
     * Categories constructor.
     * @param \Magento\Store\Model\StoreManager $storeManager
     * @param \SqualoMail\SqmMcMagentoTwo\Helper\Data $helper
     * @param \Magento\Catalog\Model\CategoryRepository $categoryRepository
     * @param \SqualoMail\SqmMcMagentoTwo\Model\SqmMcSyncEcommerceFactory $chimpSyncEcommerce
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollection
     * @param \SqualoMail\SqmMcMagentoTwo\Model\ResourceModel\SqmMcSyncEcommerce\CollectionFactory $syncCollection
     * @param \Magento\Catalog\Helper\Data $taxHelper
     */
    public function __construct(
        \SqualoMail\SqmMcMagentoTwo\Helper\Data $helper,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \SqualoMail\SqmMcMagentoTwo\Model\SqmMcSyncEcommerceFactory $chimpSyncEcommerce,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollection,
        \SqualoMail\SqmMcMagentoTwo\Model\ResourceModel\SqmMcSyncEcommerce\CollectionFactory $syncCollection
    ) {
        $this->_helper              = $helper;
        $this->_categoryRepository  = $categoryRepository;
        $this->_categoryFactory     = $categoryFactory;
        $this->_chimpSyncEcommerce  = $chimpSyncEcommerce;
        $this->_categoryCollection  = $categoryCollection;
        $this->_syncCollection      = $syncCollection;
        $this->_batchId             = \SqualoMail\SqmMcMagentoTwo\Helper\Data::IS_CATEGORY. '_' . $this->_helper->getGmtTimeStamp();
    }

    public function sendCategories($magentoStoreId)
    {
        $categoryArray = [];
        $counter = 0;
        $sqmmcStoreId = $this->_helper->getConfigValue(
            \SqualoMail\SqmMcMagentoTwo\Helper\Data::XML_SQM_MC_STORE,
            $magentoStoreId
        );
        $categoryArray  = array_merge($categoryArray, $this->_sendDeletedCategories($sqmmcStoreId, $magentoStoreId));
        $collection = $this->_getCollection();
        $collection->addAttributeToSelect('name', 'url_key', 'is_active');
        // Sync only enabled categories
        $collection->addAttributeToFilter('is_active', ['eq'=>'1']);
        //$collection->setStoreId($magentoStoreId);
        $collection->setStore($magentoStoreId);
        // Include only necessary fields in the queries
        $collection->getSelect()->reset(\Magento\Framework\DB\Select::COLUMNS)->columns(['entity_id']);
        $collection->getSelect()->joinLeft(
            ['m4m' => $this->_helper->getTableName('sqmmc_sync_ecommerce')],
            "m4m.related_id = e.entity_id and m4m.type = '".\SqualoMail\SqmMcMagentoTwo\Helper\Data::IS_CATEGORY.
            "' and m4m.sqmmc_store_id = '".$sqmmcStoreId."'",
            ['m4m.*']
        );
        $collection->getSelect()->where("m4m.sqmmc_sync_delta IS null OR (m4m.sqmmc_sync_delta > '".
            $this->_helper->getMCMinSyncDateFlag().
            "' and m4m.sqmmc_sync_modified = 1)");
        $batchLimit = self::MAX;
        if ($this->_helper->getConfigValue(\SqualoMail\SqmMcMagentoTwo\Helper\Data::XML_INCREASE_BATCH, $magentoStoreId)) {
            $batchLimit = $this->_helper->getSizeLeftBatchCount(\SqualoMail\SqmMcMagentoTwo\Helper\Data::IS_CATEGORY);
        }
        $collection->getSelect()->limit($batchLimit);
    
        foreach ($collection as $item) {
            $category = $this->_categoryFactory->create();
            $category->getResource()->load($category, $item->getId());

            $data = $this->_buildCategoryData($category);
            $categoryJson = '';
            $categoryJson = json_encode($data);
            
            $categorySqmmcId = $category->getId();
 
            if ($categoryJson !== false) {
                if (!empty($categoryJson)) {
                    if ($item->getSqmmcSyncModified() == 1) {
                        $this->_helper->modifyCounter(\SqualoMail\SqmMcMagentoTwo\Helper\Data::CAT_MOD);

                        // Update Category
                        $categoryArray[$counter]['method'] = "PUT";
                        $categoryArray[$counter]['path'] = "/ecommerce/stores/" . $sqmmcStoreId . "/categories/" . $categorySqmmcId;
                        $categoryArray[$counter]['operation_id'] = $this->_batchId . '_' . $categorySqmmcId;
                        $categoryArray[$counter]['body'] = $categoryJson;
                        $counter++;
                    } else {
                        $this->_helper->modifyCounter(\SqualoMail\SqmMcMagentoTwo\Helper\Data::CAT_NEW);

                        // Create Category
                        $categoryArray[$counter]['method'] = "POST";
                        $categoryArray[$counter]['path'] = "/ecommerce/stores/" . $sqmmcStoreId . "/categories/";
                        $categoryArray[$counter]['operation_id'] = $this->_batchId . '_' . $categorySqmmcId;
                        $categoryArray[$counter]['body'] = $categoryJson;
                        $counter++;
                    }

                    //update category delta
                    $this->_updateCategory($sqmmcStoreId, $category->getId());
                } else {
                    $this->_updateCategory(
                        $sqmmcStoreId,
                        $category->getId(),
                        $this->_helper->getGmtDate(),
                        'Category with no data',
                        0
                    );
                }
            } else {
                $this->_updateCategory(
                    $sqmmcStoreId,
                    $category->getId(),
                    $this->_helper->getGmtDate(),
                    json_last_error_msg(),
                    0
                );
            }
        }
        
        $this->_helper->addBatchCount(count($categoryArray));
        return $categoryArray;
    }

    /**
     * @param \Magento\Catalog\Model\Category $category
     * @return array
     */
    protected function _buildCategoryData(\Magento\Catalog\Model\Category $category)
    {
        $data = [];
        $data["id"] = $category->getId();
        $data["handle"] = $category->getUrlKey() ? $category->getUrlKey() : '';
        $data["title"] = $category->getName() ? $category->getName() : '';
        $data["product_ids"] = $this->_getProductCollectionData($category);

        return $data;
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected function _getCollection()
    {
        return $this->_categoryCollection->create();
    }

    protected function _getProductCollectionData($category)
    {
        $data = [];
        $categoryProductCollection = $category->getProductCollection()->addAttributeToSelect('id');
        foreach ($categoryProductCollection as $item) {
            $data[] = $item->getId();
        }
        return $data;
    }

    /**
     * @param $storeId
     * @param $entityId
     * @param $sync_delta
     * @param $sync_error
     * @param $sync_modified
     */
    protected function _updateCategory(
        $storeId,
        $entityId,
        $sync_delta = null,
        $sync_error = null,
        $sync_modified = null
    ) {
        $this->_helper->saveEcommerceData(
            $storeId,
            $entityId,
            \SqualoMail\SqmMcMagentoTwo\Helper\Data::IS_CATEGORY,
            $sync_delta,
            $sync_error,
            $sync_modified
        );
    }

    protected function _sendDeletedCategories($sqmmcStoreId, $magentoStoreId)
    {
        $batchArray = [];
        $collection = $this->_syncCollection->create();
        $collection->addFieldToFilter('sqmmc_store_id', ['eq' => $sqmmcStoreId])
            ->addFieldToFilter('type', ['eq' => \SqualoMail\SqmMcMagentoTwo\Helper\Data::IS_CATEGORY])
            ->addFieldToFilter('sqmmc_sync_deleted', ['eq' => 1]);
        $collection->getSelect()->limit(self::MAX);
        $counter = 0;
        /**
         * @var $syncCategory \SqualoMail\SqmMcMagentoTwo\Model\SqmMcSyncEcommerce
         */
        foreach ($collection as $category) {
            $categoryId = $category->getRelatedId();
            $batchArray[$counter]['method'] = 'DELETE';
            $batchArray[$counter]['operation_id'] = $this->_batchId . '_' . $categoryId;
            $batchArray[$counter]['path'] = "/ecommerce/stores/$sqmmcStoreId/categories/$categoryId";
            $counter++;
            $syncCategory = $this->_helper->getChimpSyncEcommerce(
                $sqmmcStoreId,
                $categoryId,
                \SqualoMail\SqmMcMagentoTwo\Helper\Data::IS_CATEGORY
            );
            $syncCategory->getResource()->delete($syncCategory);
        }
        return $batchArray;
    }
}