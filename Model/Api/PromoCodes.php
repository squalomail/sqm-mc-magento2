<?php
/**
 * mc-magento2 Magento Component
 *
 * @category SqualoMail
 * @package mc-magento2
 * @author Ebizmarts Team <info@ebizmarts.com>
 * @copyright Ebizmarts (http://ebizmarts.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @date: 10/6/17 1:15 PM
 * @file: Coupon.php
 */
namespace SqualoMail\SqmMcMagentoTwo\Model\Api;

use Magento\TestFramework\Inspection\Exception;

class PromoCodes
{
    const MAX = 100;
    protected $_batchId;
    protected $_token;
    /**
     * @var \SqualoMail\SqmMcMagentoTwo\Helper\Data
     */
    private $_helper;
    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Coupon\CollectionFactory
     */
    protected $_couponCollection;
    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory
     */
    protected $_ruleCollection;
    /**
     * @var \SqualoMail\SqmMcMagentoTwo\Model\SqmMcSyncEcommerceFactory
     */
    private $_chimpSyncEcommerce;
    /**
     * @var PromoRules
     */
    private $_promoRules;
    /**
     * @var \SqualoMail\SqmMcMagentoTwo\Model\ResourceModel\SqmMcSyncEcommerce\CollectionFactory
     */
    private $_syncCollection;

    /**
     * PromoCodes constructor.
     * @param \SqualoMail\SqmMcMagentoTwo\Helper\Data $helper
     * @param \Magento\SalesRule\Model\ResourceModel\Coupon\CollectionFactory $couponCollection
     * @param \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $ruleCollection
     * @param \SqualoMail\SqmMcMagentoTwo\Model\SqmMcSyncEcommerceFactory $chimpSyncEcommerce
     * @param PromoRules $promoRules
     * @param \SqualoMail\SqmMcMagentoTwo\Model\ResourceModel\SqmMcSyncEcommerce\CollectionFactory $syncCollection
     */
    public function __construct(
        \SqualoMail\SqmMcMagentoTwo\Helper\Data $helper,
        \Magento\SalesRule\Model\ResourceModel\Coupon\CollectionFactory $couponCollection,
        \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $ruleCollection,
        \SqualoMail\SqmMcMagentoTwo\Model\SqmMcSyncEcommerceFactory $chimpSyncEcommerce,
        \SqualoMail\SqmMcMagentoTwo\Model\Api\PromoRules $promoRules,
        \SqualoMail\SqmMcMagentoTwo\Model\ResourceModel\SqmMcSyncEcommerce\CollectionFactory $syncCollection
    ) {
    
        $this->_helper              = $helper;
        $this->_couponCollection    = $couponCollection;
        $this->_ruleCollection      = $ruleCollection;
        $this->_chimpSyncEcommerce  = $chimpSyncEcommerce;
        $this->_batchId             = \SqualoMail\SqmMcMagentoTwo\Helper\Data::IS_PROMO_CODE. '_' .
            $this->_helper->getGmtTimeStamp();
        $this->_promoRules          = $promoRules;
        $this->_syncCollection      = $syncCollection;
    }
    public function sendCoupons($magentoStoreId)
    {
        $sqmmcStoreId = $this->_helper->getConfigValue(
            \SqualoMail\SqmMcMagentoTwo\Helper\Data::XML_SQM_MC_STORE,
            $magentoStoreId
        );
        $batchArray = [];
        $batchArray = array_merge($batchArray, $this->_sendDeletedCoupons($sqmmcStoreId, $magentoStoreId));
//        $batchArray = array_merge($batchArray, $this->_sendModifiedCoupons($sqmmcStoreId, $magentoStoreId));
        $batchArray = array_merge($batchArray, $this->_sendNewCoupons($sqmmcStoreId, $magentoStoreId));

        return $batchArray;
    }
    protected function _sendDeletedCoupons($sqmmcStoreId, $magentoStoreId)
    {
        $batchArray = [];
        $websiteId = $this->_helper->getWebsiteId($magentoStoreId);
        $collection = $this->_syncCollection->create();
        $collection->addFieldToFilter('sqmmc_store_id', ['eq'=>$sqmmcStoreId])
            ->addFieldToFilter('type', ['eq'=>\SqualoMail\SqmMcMagentoTwo\Helper\Data::IS_PROMO_CODE])
            ->addFieldToFilter('sqmmc_sync_deleted', ['eq'=>1]);
        $collection->getSelect()->limit(self::MAX);
        $counter = 0;
        /**
         * @var $syncCoupon \SqualoMail\SqmMcMagentoTwo\Model\SqmMcSyncEcommerce
         */
        foreach ($collection as $coupon) {
            $couponId = $coupon->getRelatedId();
            $ruleId = $coupon->getDeletedRelatedId();
            $batchArray[$counter]['method'] = 'DELETE';
            $batchArray[$counter]['operation_id'] = $this->_batchId . '_' . $couponId;
            $batchArray[$counter]['path'] =
                "/ecommerce/stores/$sqmmcStoreId/promo-rules/$ruleId/promo-codes/$couponId";
            $counter++;
            $syncCoupon =$this->_helper->getChimpSyncEcommerce(
                $sqmmcStoreId,
                $couponId,
                \SqualoMail\SqmMcMagentoTwo\Helper\Data::IS_PROMO_CODE
            );
            $syncCoupon->getResource()->delete($syncCoupon);
        }
        return $batchArray;
    }
    protected function _sendNewCoupons($sqmmcStoreId, $magentoStoreId)
    {
        $batchArray = [];
        $websiteId = $this->_helper->getWebsiteId($magentoStoreId);
        /**
         * @var $ruleCollection \Magento\SalesRule\Model\ResourceModel\Rule\Collection
         */
        $ruleCollection = $this->_ruleCollection->create();
        $ruleCollection->addWebsiteFilter($websiteId);
        $rulesId = [];
        foreach ($ruleCollection as $rule) {
            $rulesId[] = $rule->getRuleId();
        }
        if (count($rulesId)) {
            $inRoules = implode(',', $rulesId);
            $collection = $this->_couponCollection->create();
            $collection->getSelect()->joinLeft(
                ['m4m' => $this->_helper->getTableName('sqmmc_sync_ecommerce')],
                "m4m.related_id = main_table.coupon_id and m4m.type = '" .
                \SqualoMail\SqmMcMagentoTwo\Helper\Data::IS_PROMO_CODE .
                "' and m4m.sqmmc_store_id = '" . $sqmmcStoreId . "'",
                ['m4m.*']
            );
            $collection->getSelect()->joinLeft(
                ['rules' => $this->_helper->getTableName('salesrule')],
                'main_table.rule_id = rules.rule_id'
            );
            $collection->getSelect()->where("m4m.sqmmc_sync_delta IS null and (rules.use_auto_generation = 1 and main_table.is_primary is null or rules.use_auto_generation = 0 and main_table.is_primary = 1) and main_table.rule_id in ($inRoules)");
            $collection->getSelect()->limit(self::MAX);
            $counter = 0;
            /**
             * @var $item \Magento\SalesRule\Model\Coupon
             */
            foreach ($collection as $item) {
                $this->_token = null;
                $ruleId = $item->getRuleId();
                $couponId = $item->getCouponId();
                try {
                    $promoRule = $this->_helper->getChimpSyncEcommerce(
                        $sqmmcStoreId,
                        $ruleId,
                        \SqualoMail\SqmMcMagentoTwo\Helper\Data::IS_PROMO_RULE
                    );
                    if (!$promoRule->getMailchimpSyncDelta() ||
                        $promoRule->getMailchimpSyncDelta() < $this->_helper->getMCMinSyncDateFlag($magentoStoreId)) {
                        // must send the promorule before the promocode
                        $newPromoRule = $this->_promoRules->getNewPromoRule(
                            $ruleId,
                            $sqmmcStoreId,
                            $magentoStoreId
                        );
                        if (!empty($newPromoRule)) {
                            $batchArray[$counter] = $newPromoRule;
                            $counter++;
                        } else {
                            $error = __('Parent rule with id ' . $ruleId . 'has not been correctly sent.');
                            $this->_updateSyncData(
                                $sqmmcStoreId,
                                $ruleId,
                                $this->_helper->getGmtDate(),
                                $error,
                                0
                            );
                            continue;
                        }
                    }
                    if ($promoRule->getMailchimpSyncError()) {
                        // the promorule associated has an error
                        $error = __('Parent rule with id ' . $ruleId . 'has not been correctly sent.');
                        $this->_updateSyncData(
                            $sqmmcStoreId,
                            $couponId,
                            $this->_helper->getGmtDate(),
                            $error,
                            0
                        );
                        continue;
                    }
                    $promoCodeJson = json_encode($this->generateCodeData($item, $magentoStoreId));
                    if ($promoCodeJson !== false) {
                        if (!empty($promoCodeJson)) {
                            $batchArray[$counter]['method'] = 'POST';
                            $batchArray[$counter]['path'] =
                                "/ecommerce/stores/$sqmmcStoreId/promo-rules/$ruleId/promo-codes/";
                            $batchArray[$counter]['operation_id'] = $this->_batchId . '_' . $couponId;
                            $batchArray[$counter]['body'] = $promoCodeJson;
                        } else {
                            $error = __('Something went wrong when retrieving the information for promo rule');
                            $this->_updateSyncData(
                                $sqmmcStoreId,
                                $couponId,
                                $this->_helper->getGmtDate(),
                                $error,
                                0
                            );
                            continue;
                        }
                        $counter++;
                        $this->_updateSyncData($sqmmcStoreId, $couponId);
                    } else {
                        $error = json_last_error_msg();
                        $this->_updateSyncData(
                            $sqmmcStoreId,
                            $couponId,
                            $this->_helper->getGmtDate(),
                            $error,
                            0
                        );
                    }
                } catch (Exception $e) {
                    $this->_helper->log($e->getMessage());
                }
            }
        }
        return $batchArray;
    }
    protected function generateCodeData($item, $magentoStoreId)
    {
        $data = [];
        $data['id'] = $item->getCouponId();
        $data['code'] = $item->getCode();
        $data['redemption_url'] = $this->_getRedemptionUrl($item->getCode(), $magentoStoreId);
        $data['usage_count'] = (int)$item->getTimesUsed();

        return $data;
    }
    protected function _getRedemptionUrl($code, $magentoStoreId)
    {
        $token = hash('md5', rand(0, 9999999));
        $url = $this->_helper->getRedemptionUrl($magentoStoreId, $code, $token);
        $this->_token = $token;
        return $url;
    }
    protected function _updateSyncData(
        $storeId,
        $entityId,
        $sync_delta = null,
        $sync_error = null,
        $sync_modified = null,
        $sync_deleted = null
    ) {
        $this->_helper->saveEcommerceData(
            $storeId,
            $entityId,
            \SqualoMail\SqmMcMagentoTwo\Helper\Data::IS_PROMO_CODE,
            $sync_delta,
            $sync_error,
            $sync_modified,
            $sync_deleted,
            $this->_token
        );
    }
}
