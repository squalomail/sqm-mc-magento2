<?php
/**
 * mc-magento2 Magento Component
 *
 * @category SqualoMail
 * @package mc-magento2
 * @author Ebizmarts Team <info@ebizmarts.com>
 * @copyright Ebizmarts (http://ebizmarts.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @date: 10/17/17 2:38 PM
 * @file: PromoRules.php
 */

namespace SqualoMail\SqmMcMagentoTwo\Model\Api;

use Magento\Cms\Test\Unit\Controller\Adminhtml\Page\MassEnableTest;

class PromoRules
{
    const TYPE_FIXED = 'fixed';
    const TYPE_PERCENTAGE = 'percentage';
    const TARGET_PER_ITEM = 'per_item';
    const TARGET_TOTAL = 'total';
    const TARGET_SHIPPING = 'shipping';
    const FREESHIPPING_NO = 0;
    const FREESHIPPING_FOR_MATHINGI_TEMS_ONLY = 1;
    const FREESHIPPING_FOR_SHIPMENT_WITH_MATCHING_ITEMS = 3;
    const MAX = 100;

    private $_batchId;
    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory
     */
    private $_collection;
    /**
     * @var \SqualoMail\SqmMcMagentoTwo\Helper\Data
     */
    private $_helper;
    /**
     * @var \SqualoMail\SqmMcMagentoTwo\Model\SqmMcSyncEcommerceFactory
     */
    private $_chimpSyncEcommerce;
    /**
     * @var \Magento\SalesRule\Model\RuleRepository
     */
    private $_ruleRepo;
    /**
     * @var \SqualoMail\SqmMcMagentoTwo\Model\ResourceModel\SqmMcSyncEcommerce\CollectionFactory
     */
    protected $_syncCollection;

    /**
     * PromoRules constructor.
     * @param \SqualoMail\SqmMcMagentoTwo\Helper\Data $helper
     * @param \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $collection
     * @param \Magento\SalesRule\Model\RuleRepository $ruleRepo
     * @param \SqualoMail\SqmMcMagentoTwo\Model\SqmMcSyncEcommerceFactory $chimpSyncEcommerce
     * @param \SqualoMail\SqmMcMagentoTwo\Model\ResourceModel\SqmMcSyncEcommerce\CollectionFactory $syncCollection
     */
    public function __construct(
        \SqualoMail\SqmMcMagentoTwo\Helper\Data $helper,
        \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $collection,
        \Magento\SalesRule\Model\RuleRepository $ruleRepo,
        \SqualoMail\SqmMcMagentoTwo\Model\SqmMcSyncEcommerceFactory $chimpSyncEcommerce,
        \SqualoMail\SqmMcMagentoTwo\Model\ResourceModel\SqmMcSyncEcommerce\CollectionFactory $syncCollection
    ) {
    
        $this->_helper              = $helper;
        $this->_collection          = $collection;
        $this->_chimpSyncEcommerce  = $chimpSyncEcommerce;
        $this->_ruleRepo             = $ruleRepo;
        $this->_batchId             = \SqualoMail\SqmMcMagentoTwo\Helper\Data::IS_PROMO_RULE. '_' .
            $this->_helper->getGmtTimeStamp();
        $this->_syncCollection      = $syncCollection;
    }
    public function sendRules($magentoStoreId)
    {
        $sqmmcStoreId = $this->_helper->getConfigValue(
            \SqualoMail\SqmMcMagentoTwo\Helper\Data::XML_SQM_MC_STORE,
            $magentoStoreId
        );
        $batchArray = [];

        $batchArray = array_merge($batchArray, $this->_getDeletedPromoRules($sqmmcStoreId, $magentoStoreId));
        $batchArray = array_merge($batchArray, $this->_getModifiedPromoRules($sqmmcStoreId, $magentoStoreId));
        return $batchArray;
    }
    protected function _getDeletedPromoRules($sqmmcStoreId, $magentoStoreId)
    {
        $batchArray = [];
        $collection = $this->_syncCollection->create();
        $collection->addFieldToFilter('sqmmc_store_id', ['eq'=>$sqmmcStoreId])
            ->addFieldToFilter('type', ['eq'=>\SqualoMail\SqmMcMagentoTwo\Helper\Data::IS_PROMO_RULE])
            ->addFieldToFilter('sqmmc_sync_deleted', ['eq'=>1]);
        $collection->getSelect()->limit(self::MAX);
        $count = 0;
        $api = $this->_helper->getApi($magentoStoreId);
        /**
         * @var $rule \SqualoMail\SqmMcMagentoTwo\Model\SqmMcSyncEcommerce
         */
        foreach ($collection as $rule) {
            $ruleId = $rule->getData('related_id');
            try {
                $sqmmcRule = $api->ecommerce->promoCodes->getAll($sqmmcStoreId, $ruleId);
                foreach ($sqmmcRule['promo_codes'] as $promoCode) {
                    $this->_helper->ecommerceDeleteAllByIdType(
                        $promoCode['id'],
                        \SqualoMail\SqmMcMagentoTwo\Helper\Data::IS_PROMO_CODE,
                        $sqmmcStoreId
                    );
                }
                $batchArray[$count]['method'] = 'DELETE';
                $batchArray[$count]['path'] = "/ecommerce/stores/$sqmmcStoreId/promo-rules/$ruleId";
                $batchArray[$count]['operation_id'] = $this->_batchId . '_' . $ruleId;
                $count++;
            } catch (\SqualoMailMc_Error $e) {
                $this->_helper->log($e->getFriendlyMessage());
            }
            $this->_helper->ecommerceDeleteAllByIdType(
                $ruleId,
                \SqualoMail\SqmMcMagentoTwo\Helper\Data::IS_PROMO_RULE,
                $sqmmcStoreId
            );
        }
        return $batchArray;
    }
    protected function _getModifiedPromoRules($sqmmcStoreId, $magentoStoreId)
    {
        $batchArray = [];
        $websiteId = $this->_helper->getWebsiteId($magentoStoreId);
        /**
         * @var $collection \Magento\SalesRule\Model\ResourceModel\Rule\Collection
         */
        $collection = $this->_collection->create();
        $collection->addWebsiteFilter($websiteId);
        $collection->getSelect()->joinLeft(
            ['m4m' => $this->_helper->getTableName('sqmmc_sync_ecommerce')],
            "m4m.related_id = main_table.rule_id and m4m.type = '".
            \SqualoMail\SqmMcMagentoTwo\Helper\Data::IS_PROMO_RULE.
            "' and m4m.sqmmc_store_id = '".$sqmmcStoreId."'",
            ['m4m.*']
        );
        $collection->getSelect()->where("m4m.sqmmc_sync_modified = 1");
        $collection->getSelect()->limit(self::MAX);
        $count = 0;
        /**
         * @var $rule \Magento\SalesRule\Model\Rule
         */
        $api = $this->_helper->getApi($magentoStoreId);
        foreach ($collection as $rule) {
            $ruleId = $rule->getRuleId();
            try {
                $sqmmcRule = $api->ecommerce->promoCodes->getAll($sqmmcStoreId, $ruleId);
                foreach ($sqmmcRule['promo_codes'] as $promoCode) {
                    $this->_helper->ecommerceDeleteAllByIdType(
                        $promoCode['id'],
                        \SqualoMail\SqmMcMagentoTwo\Helper\Data::IS_PROMO_CODE,
                        $sqmmcStoreId
                    );
                }
            } catch (\SqualoMailMc_Error $e) {
                $this->_helper->log($e->getFriendlyMessage());
            }
            $this->_helper->ecommerceDeleteAllByIdType(
                $rule->getRuleId(),
                \SqualoMail\SqmMcMagentoTwo\Helper\Data::IS_PROMO_RULE,
                $sqmmcStoreId
            );
            $batchArray[$count]['method'] = 'DELETE';
            $batchArray[$count]['path'] = "/ecommerce/stores/$sqmmcStoreId/promo-rules/$ruleId";
            $batchArray[$count]['operation_id'] = $this->_batchId. '_' . $rule->getRuleId();
            $count++;
        }
        return $batchArray;
    }
    public function getNewPromoRule($ruleId, $sqmmcStoreId, $magentoStoreId)
    {
        $data = [];
        /**
         * @var $rule \Magento\SalesRule\Model\Rule
         */
        try {
            $rule = $this->_ruleRepo->getById($ruleId);
            $promoRules = $this->_generateRuleData($rule);
            if (!empty($promoRules)) {
                $promoRulesJson = json_encode($promoRules);
                if ($promoRulesJson !== false) {
                    if (!empty($promoRulesJson)) {
                        $data['method'] = 'POST';
                        $data['path'] = '/ecommerce/stores/' . $sqmmcStoreId . '/promo-rules';
                        $data['operation_id'] = $this->_batchId . '_' . $ruleId;
                        $data['body'] = $promoRulesJson;
                        $this->_updateSyncData($sqmmcStoreId, $ruleId);
                    } else {
                        $error = __('Something went wrong when retrieving the information.');
                        $this->_updateSyncData(
                            $sqmmcStoreId,
                            $ruleId,
                            $this->_helper->getGmtDate(),
                            $error,
                            0
                        );
                    }
                } else {
                    $error = json_last_error_msg();
                    $this->_updateSyncData(
                        $sqmmcStoreId,
                        $ruleId,
                        $this->_helper->getGmtDate(),
                        $error,
                        0
                    );
                }
            } else {
                $error = __('Something went wrong when retrieving the information.');
                $this->_updateSyncData(
                    $sqmmcStoreId,
                    $ruleId,
                    $this->_helper->getGmtDate(),
                    $error,
                    0
                );
            }
        } catch (\Exception $e) {
            $this->_helper->log($e->getMessage());
        }
        return $data;
    }

    private function _generateRuleData($rule)
    {
        $data = [];
        $data['id'] = $rule->getRuleId();
        $data['title'] = $rule->getName();
        $data['description'] = $rule->getDescription() ? $rule->getDescription() : $rule->getName();
        $fromDate = $rule->getFromDate();
        if ($fromDate) {
            $data['starts_at'] = $fromDate;
        }
        $toDate = $rule->getToDate();
        if ($toDate) {
            $data['ends_at'] = $toDate;
        }
        $promoAction = $rule->getSimpleAction();
        $shipping = $rule->getSimpleFreeShipping();
        $data['type'] = $this->_getSqmMcType($promoAction, $shipping);
        $data['target'] = $this->_getSqmMcTarget($promoAction, $shipping);
        switch ($data['type']) {
            case self::TYPE_PERCENTAGE:
                $data['amount'] = $rule->getDiscountAmount()/100;
                break;
            case self::TYPE_FIXED:
                if ($data['target']!=self::TARGET_SHIPPING) {
                    $data['amount'] = $rule->getDiscountAmount();
                } else {
                    $data['amount'] = 0;
                }
                break;
        }
        $data['enabled'] = (bool)$rule->getIsActive();
        if (!$data['target'] || !$data['type']) {
            return [];
        }

        return $data;
    }

    /**
     * @param $action
     * @return null|string
     */
    private function _getSqmMcType($action, $shipping)
    {
        $sqmMcType = null;
        if ($shipping==self::FREESHIPPING_NO) {
            switch ($action) {
                case \Magento\SalesRule\Model\Rule::BY_PERCENT_ACTION:
                    $sqmMcType = self::TYPE_PERCENTAGE;
                    break;
                case \Magento\SalesRule\Model\Rule::BY_FIXED_ACTION:
                case \Magento\SalesRule\Model\Rule::CART_FIXED_ACTION:
                    $sqmMcType = self::TYPE_FIXED;
                    break;
            }
        } else {
            $sqmMcType = self::TYPE_FIXED;
        }
        return $sqmMcType;
    }

    /**
     * @param $action
     * @return null|string
     */
    private function _getSqmMcTarget($action, $shipping)
    {
        $sqmMcTarget = null;
        if ($shipping==self::FREESHIPPING_NO) {
            switch ($action) {
                case \Magento\SalesRule\Model\Rule::CART_FIXED_ACTION:
                case \Magento\SalesRule\Model\Rule::BY_PERCENT_ACTION:
                    $sqmMcTarget = self::TARGET_TOTAL;
                    break;
                case \Magento\SalesRule\Model\Rule::BY_FIXED_ACTION:
                    $sqmMcTarget = self::TARGET_PER_ITEM;
                    break;
            }
        } else {
            $sqmMcTarget = self::TARGET_SHIPPING;
        }
        return $sqmMcTarget;
    }

    /**
     * @param $storeId
     * @param $entityId
     * @param $sync_delta
     * @param string $sync_error
     * @param int $sync_modified
     */
    protected function _updateSyncData($storeId, $entityId, $sync_delta = null, $sync_error = '', $sync_modified = 0)
    {
        $this->_helper->saveEcommerceData(
            $storeId,
            $entityId,
            \SqualoMail\SqmMcMagentoTwo\Helper\Data::IS_PROMO_RULE,
            $sync_delta,
            $sync_error,
            $sync_modified
        );
    }
}
