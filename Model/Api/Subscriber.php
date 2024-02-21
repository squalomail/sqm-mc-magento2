<?php
/**
 * mc-magento2 Magento Component
 *
 * @category SqualoMail
 * @package mc-magento2
 * @author Ebizmarts Team <info@ebizmarts.com>
 * @copyright Ebizmarts (http://ebizmarts.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @date: 5/15/17 11:02 AM
 * @file: Subscriber.php
 */
namespace SqualoMail\SqmMcMagentoTwo\Model\Api;

class Subscriber
{
    const BATCH_LIMIT = 100;
    /**
     * @var \SqualoMail\SqmMcMagentoTwo\Helper\Data
     */
    protected $_helper;
    /**
     * @var \Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory
     */
    protected $_subscriberCollection;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_message;
    /**
     * @var \Magento\Newsletter\Model\SubscriberFactory
     */
    protected $_subscriberFactory;
    protected $_interest=null;

    /**
     * Subscriber constructor.
     * @param \SqualoMail\SqmMcMagentoTwo\Helper\Data $helper
     * @param \Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory $subscriberCollection
     * @param \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory
     * @param \Magento\Framework\Message\ManagerInterface $message
     */
    public function __construct(
        \SqualoMail\SqmMcMagentoTwo\Helper\Data $helper,
        \Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory $subscriberCollection,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        \Magento\Framework\Message\ManagerInterface $message
    ) {
    
        $this->_helper                  = $helper;
        $this->_subscriberCollection    = $subscriberCollection;
        $this->_message                 = $message;
        $this->_subscriberFactory       = $subscriberFactory;
    }

    public function sendSubscribers($storeId, $listId)
    {
        //get subscribers
//        $listId = $this->_helper->getGeneralList($storeId);
        $this->_interest = $this->_helper->getInterest($storeId);
        $collection = $this->_subscriberCollection->create();
        $collection->addFieldToFilter('subscriber_status', ['eq' => 1])
            ->addFieldToFilter('store_id', ['eq' => $storeId]);
        $collection->getSelect()->joinLeft(
            ['m4m' => $this->_helper->getTableName('sqmmc_sync_ecommerce')],
            "m4m.related_id = main_table.subscriber_id and m4m.type = '".
            \SqualoMail\SqmMcMagentoTwo\Helper\Data::IS_SUBSCRIBER.
            "' and m4m.sqmmc_store_id = '".$listId."'",
            ['m4m.*']
        );
        $collection->getSelect()->where("m4m.sqmmc_sync_delta IS null ".
            "OR (m4m.sqmmc_sync_delta > '".$this->_helper->getMCMinSyncDateFlag().
            "' and m4m.sqmmc_sync_modified = 1)");
        $batchLimit = self::BATCH_LIMIT;
        if ($this->_helper->getConfigValue(\SqualoMail\SqmMcMagentoTwo\Helper\Data::XML_INCREASE_BATCH, $storeId)) {
            $batchLimit = \SqualoMail\SqmMcMagentoTwo\Helper\Data::MAX_GROUP_BATCHCOUNT;
        }
        $collection->getSelect()->limit($batchLimit);
        $subscriberArray = [];
        $date = $this->_helper->getDateMicrotime();
        $batchId = \SqualoMail\SqmMcMagentoTwo\Helper\Data::IS_SUBSCRIBER . '_' . $date;
        $counter = 0;
        /**
         * @var $subscriber \Magento\Newsletter\Model\Subscriber
         */
        foreach ($collection as $subscriber) {
            $data = $this->_buildSubscriberData($subscriber);
            $md5HashEmail = hash('md5', strtolower($subscriber->getSubscriberEmail()));
            $subscriberJson = "";
            //enconde to JSON
            $subscriberJson = json_encode($data);
            if ($subscriberJson!==false) {
                if (!empty($subscriberJson)) {
                    if ($subscriber->getSqmmcSyncModified() == 1) {
                        $this->_helper->modifyCounter(\SqualoMail\SqmMcMagentoTwo\Helper\Data::SUB_MOD);
                    } else {
                        $this->_helper->modifyCounter(\SqualoMail\SqmMcMagentoTwo\Helper\Data::SUB_NEW);
                    }
                    $subscriberArray[$counter]['method'] = "PUT";
                    $subscriberArray[$counter]['path'] = "/lists/" . $listId . "/members/" . $md5HashEmail;
                    $subscriberArray[$counter]['operation_id'] = $batchId . '_' . $subscriber->getSubscriberId();
                    $subscriberArray[$counter]['body'] = $subscriberJson;
                    //update subscribers delta
                    $this->_updateSubscriber($listId, $subscriber->getId());
                }
                $counter++;
            } else {
                $errorMessage = json_last_error_msg();
                $this->_updateSubscriber(
                    $listId,
                    $subscriber->getId(),
                    $this->_helper->getGmtDate(),
                    $errorMessage,
                    0
                );
            }
        }
        $this->_helper->resetBatchCount();
        $this->_helper->addBatchCount(count($subscriberArray));
        return $subscriberArray;
    }

    protected function _buildSubscriberData(\Magento\Newsletter\Model\Subscriber $subscriber)
    {
        $storeId = $subscriber->getStoreId();
        $data = [];
        $data["email_address"] = $subscriber->getSubscriberEmail();
        $mergeVars = $this->_helper->getMergeVarsBySubscriber($subscriber);
        if ($mergeVars) {
            $data["merge_fields"] = $mergeVars;
        }
        $data["status_if_new"] = $this->_getMCStatus($subscriber->getStatus(), $storeId);
        $interest = $this->_getInterest($subscriber);
        if (count($interest)) {
            $data['interests'] = $interest;
        }

        return $data;
    }
    protected function _getInterest(\Magento\Newsletter\Model\Subscriber $subscriber)
    {
        $rc = [];
        $interest = $this->_helper->getSubscriberInterest(
            $subscriber->getSubscriberId(),
            $subscriber->getStoreId(),
            $this->_interest
        );
        foreach ($interest as $i) {
            foreach ($i['category'] as $key => $value) {
                $rc[$value['id']] = $value['checked'];
            }
        }
        return $rc;
    }
    /**
     * Get status to send confirmation if Need to Confirm enabled on Magento
     *
     * @param $status
     * @param $storeId
     * @return string
     */
    protected function _getMCStatus($status, $storeId)
    {
        $confirmationFlagPath = \Magento\Newsletter\Model\Subscriber::XML_PATH_CONFIRMATION_FLAG;
        if ($status == \Magento\Newsletter\Model\Subscriber::STATUS_UNSUBSCRIBED) {
            $status = 'unsubscribed';
        } elseif ($this->_helper->getConfigValue($confirmationFlagPath, $storeId) &&
            ($status == \Magento\Newsletter\Model\Subscriber::STATUS_NOT_ACTIVE ||
                $status == \Magento\Newsletter\Model\Subscriber::STATUS_UNCONFIRMED)
        ) {
            $status = 'pending';
        } elseif ($status == \Magento\Newsletter\Model\Subscriber::STATUS_SUBSCRIBED) {
            $status = 'subscribed';
        }
        return $status;
    }
    public function deleteSubscriber(\Magento\Newsletter\Model\Subscriber $subscriber)
    {
        $storeId = $subscriber->getStoreId();
        $listId = $this->_helper->getGeneralList($storeId);
        $api = $this->_helper->getApi($storeId);
        try {
            $md5HashEmail = hash('md5', strtolower($subscriber->getSubscriberEmail()));
            $api->lists->members->update($listId, $md5HashEmail, null, 'cleaned');
        } catch (\SqualoMailMc_Error $e) {
            $this->_helper->log($e->getFriendlyMessage(), $storeId);
            $this->_message->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->_helper->log($e->getMessage(), $storeId);
        }
    }
    public function update(\Magento\Newsletter\Model\Subscriber $subscriber)
    {
        $storeId = $subscriber->getStoreId();
        $listId = $this->_helper->getGeneralList($storeId);
        $this->_updateSubscriber(
            $listId,
            $subscriber->getId(),
            $this->_helper->getGmtDate(),
            '',
            1
        );
    }
    protected function _updateSubscriber(
        $listId,
        $entityId,
        $sync_delta = null,
        $sync_error = null,
        $sync_modified = null
    ) {
        $this->_helper->saveEcommerceData(
            $listId,
            $entityId,
            \SqualoMail\SqmMcMagentoTwo\Helper\Data::IS_SUBSCRIBER,
            $sync_delta,
            $sync_error,
            $sync_modified
        );
    }
}
