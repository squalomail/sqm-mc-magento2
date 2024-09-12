<?php
/**
 * SqualoMail_SqmMcMagentoTwo Magento JS component
 *
 * @category    SqualoMail
 * @package     SqualoMail_SqmMcMagentoTwo
 * @author      Ebizmarts Team <info@ebizmarts.com>
 * @copyright   Ebizmarts (http://ebizmarts.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace SqualoMail\SqmMcMagentoTwo\Helper;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\ValidatorException;
use Magento\Store\Model\Store;
use Symfony\Component\Config\Definition\Exception\Exception;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const XML_PATH_ACTIVE            = 'sqmmc/general/active';
    const XML_PATH_APIKEY            = 'sqmmc/general/apikey';
    const XML_PATH_APIKEY_LIST       = 'sqmmc/general/apikeylist';
    const XML_PATH_MAXLISTAMOUNT     = 'sqmmc/general/maxlistamount';
    const XML_PATH_LIST              = 'sqmmc/general/monkeylist';
    const XML_PATH_WEBHOOK_ACTIVE    = 'sqmmc/general/webhook_active';
    const XML_PATH_WEBHOOK_DELETE    = 'sqmmc/general/webhook_delete';
    const XML_PATH_LOG               = 'sqmmc/general/log';
    const XML_PATH_MAPPING           = 'sqmmc/general/mapping';
    const XML_SQM_MC_STORE        = 'sqmmc/general/monkeystore';
    const XML_SQM_MC_JS_URL       = 'sqmmc/general/sqmmcjsurl';
    const XML_PATH_CONFIRMATION_FLAG = 'newsletter/subscription/confirm';
    const XML_PATH_STORE             = 'sqmmc/ecommerce/store';
    const XML_PATH_ECOMMERCE_ACTIVE  = 'sqmmc/ecommerce/active';
    const XML_PATH_SYNC_DATE         = 'sqmmc/general/mcminsyncdateflag';
    const XML_ECOMMERCE_OPTIN        = 'sqmmc/ecommerce/customer_optin';
    const XML_ECOMMERCE_FIRSTDATE    = 'sqmmc/ecommerce/firstdate';
    const XML_ABANDONEDCART_ACTIVE   = 'sqmmc/abandonedcart/active';
    const XML_ABANDONEDCART_FIRSTDATE   = 'sqmmc/abandonedcart/firstdate';
    const XML_ABANDONEDCART_PAGE     = 'sqmmc/abandonedcart/page';
    const XML_PATH_IS_SYNC           = 'sqmmc/general/issync';
    const XML_ABANDONEDCART_EMAIL    = 'sqmmc/abandonedcart/save_email_in_quote';
    const XML_MERGEVARS              = 'sqmmc/general/map_fields';
    const XML_INTEREST               = 'sqmmc/general/interest';
    const XML_INTEREST_IN_SUCCESS    = 'sqmmc/general/interest_in_success';
    const XML_INTEREST_SUCCESS_HTML_BEFORE  = 'sqmmc/general/interest_success_html_before';
    const XML_INTEREST_SUCCESS_HTML_AFTER   = 'sqmmc/general/interest_success_html_after';
    const XML_MAGENTO_MAIL           = 'sqmmc/general/magentoemail';
    const XML_SEND_PROMO             = 'sqmmc/ecommerce/send_promo';
    const XML_INCLUDING_TAXES        = 'sqmmc/ecommerce/including_taxes';
    const XML_INCREASE_BATCH         = 'sqmmc/ecommerce/increase_batch_size';
    const XML_DISABLE_ERROR_LOG      = 'sqmmc/general/disable_error_log';

    const ORDER_STATE_OK             = 'complete';

    const GUEST_GROUP                = 'NOT LOGGED IN';
    const IS_CUSTOMER   = "CUS";
    const IS_PRODUCT    = "PRO";
    const IS_ORDER      = "ORD";
    const IS_QUOTE      = "QUO";
    const IS_SUBSCRIBER = "SUB";
    const IS_PROMO_RULE = "PRL";
    const IS_PROMO_CODE = "PCD";

    const PLATFORM      = 'Magento2';
    const MAXSTORES     = 200;

    const SUB_MOD       = "SubscriberModified";
    const SUB_NEW       = "SubscriberNew";
    const PRO_MOD       = "ProductModified";
    const PRO_NEW       = "ProductNew";
    const CUS_MOD       = "CustomerModified";
    const CUS_NEW       = "CustomerNew";
    const ORD_MOD       = "OrderModified";
    const ORD_NEW       = "OrderNew";
    const QUO_MOD       = "QuoteModified";
    const QUO_NEW       = "QuoteNew";

    const SYNCED        = 1;
    const NEEDTORESYNC  = 2;
    const WAITINGSYNC   = 3;
    const SYNCERROR     = 4;
    const NOTSYNCED = 5;

    const NEVERSYNC     = 6;

    const BATCH_CANCELED = 'canceled';
    const BATCH_COMPLETED = 'completed';
    const BATCH_PENDING = 'pending';
    const BATCH_ERROR = 'error';

    const MAX_MERGEFIELDS = 100;

    const MAX_BATCHCOUNT = 2000;
    const MAX_GROUP_BATCHCOUNT = 500;

    protected $counters = [];

    protected $batchCount = 0;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $_storeManager;
    /**
     * @var \SqualoMail\SqmMcMagentoTwo\Model\Logger\Logger
     */
    private $_mlogger;
    /**
     * @var \Magento\Customer\Model\GroupRegistry
     */
    private $_groupRegistry;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $_scopeConfig;
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;
    /**
     * @var \Magento\Framework\App\State
     */
    private $_state;
    /**
     * @var \Magento\Framework\Module\ModuleList\Loader
     */
    private $_loader;
    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    private $_config;
    /**
     * @var \SqualoMailMc
     */
    private $_api;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CustomerRepository
     */
    private $_customer;
    /**
     * @var \SqualoMail\SqmMcMagentoTwo\Model\SqmMcErrors
     */
    private $_sqmMcErrors;
    /**
     * @var \SqualoMail\SqmMcMagentoTwo\Model\SqmMcSyncEcommerceFactory
     */
    private $_sqmMcSyncEcommerce;
    /**
     * @var \SqualoMail\SqmMcMagentoTwo\Model\SqmMcSyncEcommerce
     */
    private $_sqmMcSyncE;
    /**
     * @var \SqualoMail\SqmMcMagentoTwo\Model\SqmMcSyncBatches
     */
    private $_syncBatches;
    /**
     * @var \SqualoMail\SqmMcMagentoTwo\Model\SqmMcStoresFactory
     */
    private $_sqmMcStoresFactory;
    /**
     * @var \SqualoMail\SqmMcMagentoTwo\Model\SqmMcStores
     */
    private $_sqmMcStores;
    /**
     * @var \Magento\Framework\Encryption\Encryptor
     */
    private $_encryptor;
    /**
     * @var \Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory
     */
    private $_subscriberCollection;
    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    private $_customerCollection;
    private $_addressRepositoryInterface;
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $_resource;
    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    private $_cacheTypeList;
    /**
     * @var \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory
     */
    private $_attCollection;
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customerFactory;
    /**
     * @var \Magento\Directory\Api\CountryInformationAcquirerInterface
     */
    protected $_countryInformation;
    /**
     * @var \SqualoMail\SqmMcMagentoTwo\Model\SqmMcInterestGroupFactory
     */
    protected $_interestGroupFactory;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;
    /**
     * @var \Magento\Framework\App\DeploymentConfig
     */
    protected $_deploymentConfig;
    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $_serializer;

    private $customerAtt    = null;
    private $_mapFields     = null;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \SqualoMail\SqmMcMagentoTwo\Model\Logger\Logger $logger
     * @param \Magento\Customer\Model\GroupRegistry $groupRegistry
     * @param \Magento\Framework\App\State $state
     * @param \Magento\Framework\Module\ModuleList\Loader $loader
     * @param \Magento\Config\Model\ResourceModel\Config $config
     * @param \SqualoMailMc $api
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Customer\Model\ResourceModel\CustomerRepository $customer
     * @param \SqualoMail\SqmMcMagentoTwo\Model\SqmMcErrors $sqmMcErrors
     * @param \SqualoMail\SqmMcMagentoTwo\Model\SqmMcSyncEcommerceFactory $sqmMcSyncEcommerce
     * @param \SqualoMail\SqmMcMagentoTwo\Model\SqmMcSyncEcommerce $sqmMcSyncE
     * @param \SqualoMail\SqmMcMagentoTwo\Model\SqmMcSyncBatches $syncBatches
     * @param \SqualoMail\SqmMcMagentoTwo\Model\SqmMcStoresFactory $sqmMcStoresFactory
     * @param \SqualoMail\SqmMcMagentoTwo\Model\SqmMcStores $sqmMcStores
     * @param \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $attCollection
     * @param \Magento\Framework\Encryption\Encryptor $encryptor
     * @param \Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory $subscriberCollection
     * @param \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollection
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepositoryInterface
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Directory\Api\CountryInformationAcquirerInterface $countryInformation
     * @param ResourceConnection $resource
     * @param \SqualoMail\SqmMcMagentoTwo\Model\SqmMcInterestGroupFactory $interestGroupFactory
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer
     * @param \Magento\Framework\App\DeploymentConfig $deploymentConfig
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \SqualoMail\SqmMcMagentoTwo\Model\Logger\Logger $logger,
        \Magento\Customer\Model\GroupRegistry $groupRegistry,
        \Magento\Framework\App\State $state,
        \Magento\Framework\Module\ModuleList\Loader $loader,
        \Magento\Config\Model\ResourceModel\Config $config,
        \SqualoMailMc $api,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Customer\Model\ResourceModel\CustomerRepository $customer,
        \SqualoMail\SqmMcMagentoTwo\Model\SqmMcErrors $sqmMcErrors,
        \SqualoMail\SqmMcMagentoTwo\Model\SqmMcSyncEcommerceFactory $sqmMcSyncEcommerce,
        \SqualoMail\SqmMcMagentoTwo\Model\SqmMcSyncEcommerce $sqmMcSyncE,
        \SqualoMail\SqmMcMagentoTwo\Model\SqmMcSyncBatches $syncBatches,
        \SqualoMail\SqmMcMagentoTwo\Model\SqmMcStoresFactory $sqmMcStoresFactory,
        \SqualoMail\SqmMcMagentoTwo\Model\SqmMcStores $sqmMcStores,
        \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $attCollection,
        \Magento\Framework\Encryption\Encryptor $encryptor,
        \Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory $subscriberCollection,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollection,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepositoryInterface,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Directory\Api\CountryInformationAcquirerInterface $countryInformation,
        \Magento\Framework\App\ResourceConnection $resource,
        \SqualoMail\SqmMcMagentoTwo\Model\SqmMcInterestGroupFactory $interestGroupFactory,
        \Magento\Framework\Serialize\Serializer\Json $serializer,
        \Magento\Framework\App\DeploymentConfig $deploymentConfig,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {

        $this->_storeManager  = $storeManager;
        $this->_mlogger       = $logger;
        $this->_groupRegistry = $groupRegistry;
        $this->_scopeConfig   = $context->getScopeConfig();
        $this->_request       = $context->getRequest();
        $this->_state         = $state;
        $this->_loader        = $loader;
        $this->_config        = $config;
        $this->_api           = $api;
        $this->_customer      = $customer;
        $this->_sqmMcErrors         = $sqmMcErrors;
        $this->_sqmMcSyncEcommerce  = $sqmMcSyncEcommerce;
        $this->_sqmMcSyncE          = $sqmMcSyncE;
        $this->_syncBatches             = $syncBatches;
        $this->_sqmMcStores         = $sqmMcStores;
        $this->_sqmMcStoresFactory  = $sqmMcStoresFactory;
        $this->_encryptor               = $encryptor;
        $this->_subscriberCollection    = $subscriberCollection;
        $this->_customerCollection      = $customerCollection;
        $this->_addressRepositoryInterface = $addressRepositoryInterface;
        $this->_resource                = $resource;
        $this->connection               = $resource->getConnection();
        $this->_cacheTypeList           = $cacheTypeList;
        $this->_attCollection           = $attCollection;
        $this->_customerFactory         = $customerFactory;
        $this->_countryInformation      = $countryInformation;
        $this->_interestGroupFactory    = $interestGroupFactory;
        $this->_serializer              = $serializer;
        $this->_deploymentConfig        = $deploymentConfig;
        $this->_date                    = $date;
        parent::__construct($context);
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function isSqmMcEnabled($store = null)
    {
        return $this->getConfigValue(self::XML_PATH_ACTIVE, $store);
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function isDoubleOptInEnabled($store = null)
    {
        return $this->getConfigValue(self::XML_PATH_CONFIRMATION_FLAG, $store);
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getApiKey($store = null, $scope = null)
    {
        $apiKey =$this->getConfigValue(self::XML_PATH_APIKEY, $store, $scope);
        return $this->_encryptor->decrypt($apiKey);
    }

    /**
     * @param null $store
     * @return \SqualoMailMc
     */
    public function getApi($store = null, $scope = null)
    {
        $apiKey = $this->getApiKey($store, $scope);
        $this->_api->setApiKey($apiKey);
        $this->_api->setUserAgent('SqualoMail4Magento' . (string)$this->getModuleVersion());
        return $this->_api;
    }
    private function getCustomerAtts()
    {
        $ret = [];
        if (!$this->customerAtt) {
            $collection = $this->_attCollection->create();
            /**
             * @var $item \Magento\Customer\Model\Attribute
             */
            foreach ($collection as $item) {
                try {
                    if ($item->usesSource()) {
                        $options = $item->getSource()->getAllOptions();
                    } else {
                        $options = [];
                    }
                } catch (\Exception $e) {
                    $options = [];
                }
                $isDate = ($item->getBackendModel()==\Magento\Eav\Model\Entity\Attribute\Backend\Datetime::class) ? 1:0;
                $isAddress = (
                    $item->getBackendModel()==\Magento\Customer\Model\Customer\Attribute\Backend\Billing::class ||
                    $item->getBackendModel()==\Magento\Customer\Model\Customer\Attribute\Backend\Shipping::class) ? 1:0;
                $ret[$item->getId()] = [
                    'attCode' => $item->getAttributeCode(),
                    'isDate' =>$isDate,
                    'isAddress' => $isAddress,
                    'options'=>$options
                ] ;
            }

            $this->customerAtt = $ret;
        }
        return $this->customerAtt;
    }
    public function resetMapFields()
    {
        $this->_mapFields = null;
    }
    public function getMapFields($storeId = null)
    {
        if (!$this->_mapFields) {
            $customerAtt = $this->getCustomerAtts();
            $data = $this->getConfigValue(self::XML_MERGEVARS, $storeId);
            try {
                $data = $this->unserialize($data);
                if (is_array($data)) {
                    foreach ($data as $customerFieldId => $sqmmcName) {
                        $this->_mapFields[] = [
                            'sqmmc' => strtoupper($sqmmcName),
                            'customer_field' => $customerAtt[$customerFieldId]['attCode'],
                            'isDate' => $customerAtt[$customerFieldId]['isDate'],
                            'isAddress' => $customerAtt[$customerFieldId]['isAddress'],
                            'options' => $customerAtt[$customerFieldId]['options']
                        ];
                    }
                }
            } catch (\Exception $e) {
                $this->log($e->getMessage());
            }
        }
        return $this->_mapFields;
    }
    public function getDateFormat()
    {
        return 'm/d/Y';
    }

    /**
     * @param $apiKey
     * @param bool $encrypted
     * @return \SqualoMailMc
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getApiByApiKey($apiKey, $encrypted = false)
    {
        if ($encrypted) {
            $this->_api->setApiKey($this->_encryptor->decrypt($apiKey));
        } else {
            $this->_api->setApiKey($apiKey);
        }
        $this->_api->setUserAgent('SqualoMail4Magento' . (string)$this->getModuleVersion());
        return $this->_api;
    }

    /**
     * @param $path
     * @param null $storeId
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getConfigValue($path, $storeId = null, $scope = null)
    {
        if ($scope) {
            $value = $this->_scopeConfig->getValue($path, $scope, $storeId);
        } else {
            $value = $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORES, $storeId);
        }
        return $value;
    }
    public function deleteConfig($path, $storeId = null, $scope = null)
    {
        $this->_config->deleteConfig($path, $scope, $storeId);
    }

    public function saveConfigValue($path, $value, $storeId = null, $scope = null)
    {
        if ($scope) {
            $this->_config->saveConfig($path, $value, $scope, $storeId);
        } else {
            $this->_config->saveConfig($path, $value, \Magento\Store\Model\ScopeInterface::SCOPE_STORES, $storeId);
        }
        $this->_cacheTypeList->cleanType('config');
    }
    public function getMCMinSyncing($storeId)
    {
        $ret = $this->getConfigValue(\SqualoMail\SqmMcMagentoTwo\Helper\Data::XML_PATH_IS_SYNC, $storeId);
        return !$ret;
    }
    public function getCartUrl($storeId, $cartId, $token)
    {
        $rc = $this->_storeManager->getStore($storeId)->getUrl(
            'sqmmc/cart/loadquote',
            [
                'id' => $cartId,
                'token' => $token,
                '_nosid' => true,
                '_secure' => true
            ]
        );
        return $rc;
    }
    public function getRedemptionUrl($storeId, $couponId, $token)
    {
        $rc = $this->_storeManager->getStore($storeId)->getUrl(
            'sqmmc/cart/loadcoupon',
            [
                'id' => $couponId,
                'token' => $token,
                '_nosid' => true,
                '_secure' => true
            ]
        );
        return $rc;
    }
    public function getSuccessInterestUrl($storeId)
    {
        $rc = $this->_storeManager->getStore($storeId)->getUrl(
            'sqmmc/checkout/success',
            [
                '_nosid' => true,
                '_secure' => true
            ]
        );
        return $rc;
    }
    /**
     * @param null $store
     * @return mixed
     */
    public function getDefaultList($store = null)
    {
        return $this->getConfigValue(self::XML_PATH_LIST, $store);
    }

    /**
     * @return \Psr\Log\LoggerInterface
     */
    public function getLogger()
    {
        return $this->_logger;
    }

    /**
     * @param $message
     * @param null $store
     */
    public function log($message, $store = null, $file = null)
    {
        if ($this->getConfigValue(self::XML_PATH_LOG, $store)) {
            $this->_mlogger->sqmmcLog($message, $file);
        }
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getModuleVersion()
    {
        $modules = $this->_loader->load();
        $v = "";
        if (isset($modules['SqualoMail_SqmMcMagentoTwo'])) {
            $v = $modules['SqualoMail_SqmMcMagentoTwo']['setup_version'];
        }
        return $v;
    }
    public function deleteStore($sqmmcStore)
    {
        try {
//            $storeId = $this->getConfigValue(self::XML_SQM_MC_STORE);
            $this->getApi()->ecommerce->stores->delete($sqmmcStore);
            $this->cancelAllPendingBatches($sqmmcStore);
        } catch (\SqualoMailMc_Error $e) {
            $this->log($e->getFriendlyMessage());
        } catch (Exception $e) {
            $this->log($e->getMessage());
        }
    }
    public function markAllBatchesAs($sqmmcStore, $fromStatus, $toStatus)
    {
        $connection = $this->_syncBatches->getResource()->getConnection();
        $tableName = $this->_syncBatches->getResource()->getMainTable();
        $connection->update(
            $tableName,
            ['status' => $toStatus],
            "sqmmc_store_id = '" . $sqmmcStore . "' and status = '" . $fromStatus . "'"
        );
    }

    public function cancelAllPendingBatches($sqmmcStore)
    {
        $this->markAllBatchesAs($sqmmcStore, self::BATCH_PENDING, self::BATCH_CANCELED);
    }

    public function restoreAllCanceledBatches($sqmmcStore)
    {
        $this->markAllBatchesAs($sqmmcStore, self::BATCH_CANCELED, self::BATCH_PENDING);
    }

    public function markRegisterAsModified($registerId, $type)
    {
        if (!empty($registerId)) {
            $this->_sqmMcSyncE->markAllAsModified($registerId, $type);
        }
    }
    public function getMCStoreName($storeId)
    {
        return $this->_storeManager->getStore($storeId)->getFrontendName();
    }
    public function getBaserUrl($storeId, $type)
    {
        return $this->_storeManager->getStore($storeId)->getBaseUrl($type);
    }
    public function createStore(int $listId = null, $storeId)
    {
        if ($listId) {
            //generate store id
            $date = date('Y-m-d-His');
            $baseUrl = $this->_storeManager->getStore($storeId)->getName();
            $sqmmcStoreId = hash('md5', parse_url($baseUrl, PHP_URL_HOST) . '_' . $date);
            $currencyCode = $this->_storeManager->getStore($storeId)->getDefaultCurrencyCode();
            $name = $this->getMCStoreName($storeId);

            //create store in squalomail
            try {
                $this->getApi()->ecommerce->stores->add(
                    $sqmmcStoreId,
                    $listId,
                    $name,
                    $currencyCode,
                    self::PLATFORM
                );
                return $sqmmcStoreId;
            } catch (\SqualoMailMc_Error $e) {
                $this->log($e->getFriendlyMessage());
            } catch (Exception $e) {
                return null;
            }
        }
        return null;
    }
    public function getMCMinSyncDateFlag($storeId = null)
    {
        $syncDate = $this->getConfigValue(self::XML_PATH_SYNC_DATE, $storeId);
        if ($syncDate=='') {
            $syncDate = '1900-01-01';
        }
        return $syncDate;
    }
    public function getBaseDir()
    {
        return BP;
    }

    /**
     * @param \Magento\Customer\Model\Customer $customer
     * @param $storeId
     * @param null $email
     * @return array|null
     */
    public function getMergeVars(\Magento\Customer\Model\Customer $customer, $storeId)
    {
        $mergeVars = [];
        $mapFields = $this->getMapFields($storeId);
        if (is_array($mapFields)) {
            foreach ($mapFields as $map) {
                $value = $customer->getData($map['customer_field']);
                if ($value) {
                    if ($map['isDate']) {
                        $format = $this->getDateFormat();
                        if ($map['customer_field'] == 'dob') {
                            $format = substr($format, 0, 3);
                        }
                        $value = date($format, strtotime($value));
                    } elseif ($map['isAddress']) {
                        $customerAddress = $customer->getPrimaryAddress($map['customer_field']);
                        $value = [];
                        if ($customerAddress !== false) {
                            $value = $this->_getAddressValues($customerAddress);
                        }
                    } elseif (count($map['options'])) {
                        foreach ($map['options'] as $option) {
                            if ($option['value'] == $value) {
                                $value = $option['label'];
                                break;
                            }
                        }
                    }
                    $mergeVars[$map['sqmmc']] = $value;
                }
            }
        }
        return (!empty($mergeVars)) ? $mergeVars : null;
    }

    /**
     * @param \Magento\Customer\Model\Address\AbstractAddress $value
     * @return array
     */
    private function _getAddressValues(\Magento\Customer\Model\Address\AbstractAddress $address)
    {
        $addressData = [];
        if ($address) {
            $street = $address->getStreet();
            if (count($street) > 1) {
                $addressData["addr1"] = $street[0];
                $addressData["addr2"] = $street[1];
            } else {
                if (!empty($street[0])) {
                    $addressData["addr1"] = $street[0];
                }
            }
            if ($address->getCity()) {
                $addressData["city"] = $address->getCity();
            }
            if ($address->getRegion()) {
                $addressData["state"] = $address->getRegion();
            } else {
                $addressData["state"] = "";
            }

            if ($address->getPostcode()) {
                $addressData["zip"] = $address->getPostcode();
            }
            if ($address->getCountry()) {
                $country = $this->_countryInformation->getCountryInfo($address->getCountryId());
                $addressData["country"] = $country->getFullNameLocale();
            }
        }
        return $addressData;
    }

    public function getMergeVarsBySubscriber(\Magento\Newsletter\Model\Subscriber $subscriber, $email = null)
    {
        $mergeVars = [];
        $storeId = $subscriber->getStoreId();
        $webSiteId = $this->getWebsiteId($subscriber->getStoreId());
        if (!$email) {
            $email = $subscriber->getEmail();
        }
        try {
            /**
             * @var $customer \Magento\Customer\Model\Customer
             */
            $customer = $this->_customerFactory->create();
            $customer->setWebsiteId($webSiteId);
            $customer->loadByEmail($email);
            if ($customer->getData('email') == $email) {
                $mergeVars = $this->getMergeVars($customer, $storeId);
            }
        } catch (\Exception $e) {
            $this->log($e->getMessage());
        }
        return $mergeVars;
    }

    /**
     * @param \Magento\Customer\Model\Customer $customer
     * @param $email
     * @return array|null
     */
    public function getMergeVarsByCustomer(\Magento\Customer\Model\Customer $customer, $email)
    {
        return $this->getMergeVars($customer, $customer->getData('store_id'));
    }

    public function getGeneralList($storeId)
    {
        return $this->getConfigValue(self::XML_PATH_LIST, $storeId);
    }

    public function getListForSqmMcStore($sqmmcStoreId, $apiKey)
    {
        try {
            $api = $this->getApiByApiKey($apiKey);
            $store = $api->ecommerce->stores->get($sqmmcStoreId);
            if (isset($store['list_id'])) {
                return $store['list_id'];
            }
        } catch (\SqualoMailMc_Error $e) {
            $this->log($e->getFriendlyMessage());
        }
        return null;
    }

    public function getDateMicrotime()
    {
        $microtime = explode(' ', microtime());
        $msec = $microtime[0];
        $msecArray = explode('.', $msec);
        $date = date('Y-m-d-H-i-s') . '-' . $msecArray[1];
        return $date;
    }
    public function resetErrors($sqmmcStore)
    {
        try {
            // clean the errors table
            $connection = $this->_sqmMcErrors->getResource()->getConnection();
            $tableName = $this->_sqmMcErrors->getResource()->getMainTable();
            $connection->delete($tableName, "sqmmc_store_id = '".$sqmmcStore."'");
            // clean the syncecommerce table with errors
            $connection = $this->_sqmMcSyncE->getResource()->getConnection();
            $tableName = $this->_sqmMcSyncE->getResource()->getMainTable();
            $connection->delete(
                $tableName,
                "sqmmc_store_id = '".$sqmmcStore."' and sqmmc_sync_error is not null"
            );
        } catch (\Zend_Db_Exception $e) {
            throw new ValidatorException(__($e->getMessage()));
        }
    }
    public function resetEcommerce()
    {
        $this->resetErrors();
    }
    public function saveEcommerceData(
        $storeId,
        $entityId,
        $type,
        $date = null,
        $error = null,
        $modified = null,
        $deleted = null,
        $token = null,
        $sent = null
    ) {
        if (!empty($entityId)) {
            $chimpSyncEcommerce = $this->getChimpSyncEcommerce($storeId, $entityId, $type);
            if ($chimpSyncEcommerce->getRelatedId() == $entityId ||
                !$chimpSyncEcommerce->getRelatedId() && $modified != 1) {
                $chimpSyncEcommerce->setSqmmcStoreId($storeId);
                $chimpSyncEcommerce->setType($type);
                $chimpSyncEcommerce->setRelatedId($entityId);
                if ($modified) {
                    $chimpSyncEcommerce->setSqmmcSyncModified($modified);
                }
                if ($date) {
                    $chimpSyncEcommerce->setSqmmcSyncDelta($date);
                } elseif ($modified != 1) {
                    $chimpSyncEcommerce->setBatchId(null);
                }
                if ($error) {
                    $chimpSyncEcommerce->setSqmmcSyncError($error);
                }
                if ($deleted) {
                    $chimpSyncEcommerce->setSqmmcSyncDeleted($deleted);
                    $chimpSyncEcommerce->setSqmmcSyncModified(0);
                }
                if ($token) {
                    $chimpSyncEcommerce->setSqmmcToken($token);
                }
                if ($sent) {
                    $chimpSyncEcommerce->setSqmmcSent($sent);
                }
                $chimpSyncEcommerce->getResource()->save($chimpSyncEcommerce);
            }
        }
    }

    public function markEcommerceAsDeleted($relatedId, $type, $relatedDeletedId = null)
    {
        $this->_sqmMcSyncE->markAllAsDeleted($relatedId, $type, $relatedDeletedId);
    }
    public function ecommerceDeleteAllByIdType($id, $type, $sqmmcStoreId)
    {
        $this->_sqmMcSyncE->deleteAllByIdType($id, $type, $sqmmcStoreId);
    }
    public function deleteAllByBatchId($batchId)
    {
        $this->_sqmMcSyncE->deleteAllByBatchid($batchId);
    }
    public function getChimpSyncEcommerce($storeId, $id, $type)
    {
        $chimp = $this->_sqmMcSyncEcommerce->create();
        return $chimp->getByStoreIdType($storeId, $id, $type);
    }
    public function loadStores()
    {
        
        $mcUserName = [];
        $connection = $this->_sqmMcStores->getResource()->getConnection();
        $tableName = $this->_sqmMcStores->getResource()->getMainTable();
        $connection->truncateTable($tableName);
        $keys = $this->getAllApiKeys();
        foreach ($keys as $apiKey) {
            if (!$apiKey || $apiKey =='') {
                continue;
            }
            $this->_api->setApiKey(trim($apiKey));
            $this->_api->setUserAgent('SqualoMail4Magento' . (string)$this->getModuleVersion());

            try {
                $apiStores = $this->_api->ecommerce->stores->get(null, null, null, self::MAXSTORES);
            } catch (\SqualoMailMc_Error $sqmmcError) {
                $this->log($sqmmcError->getFriendlyMessage());
                continue;
            } catch (\SqualoMailMc_HttpError $sqmmcError) {
                $this->log($sqmmcError->getMessage());
                continue;
            }

            foreach ($apiStores['stores'] as $store) {
                if ($store['platform']!=self::PLATFORM) {
                    continue;
                }
                if (isset($store['connected_site'])) {
                    $name = $store['name'];
                } else {
                    $name = $store['name'].' (Warning: not connected)';
                }
                $mstore = $this->_sqmMcStoresFactory->create();
                $mstore->setApikey($this->_encryptor->encrypt(trim($apiKey)));
                $mstore->setStoreid($store['id']);
                $mstore->setListId($store['list_id']);
                $mstore->setName($name);
                $mstore->setPlatform($store['platform']);
                $mstore->setIsSync($store['is_syncing']);
                $mstore->setEmailAddress($store['email_address']);
                $mstore->setDomain($store['domain']);
                $mstore->setCurrencyCode($store['currency_code']);
                $mstore->setPrimaryLocale($store['primary_locale']);
                $mstore->setTimezone($store['timezone']);
                $mstore->setPhone($store['phone']);
                $mstore->setAddressAddressOne($store['address']['address1']);
                $mstore->setAddressAddressTwo($store['address']['address2']);
                $mstore->setAddressCity($store['address']['city']);
                $mstore->setAddressProvince($store['address']['province']);
                $mstore->setAddressProvinceCode($store['address']['province_code']);
                $mstore->setAddressPostalCode($store['address']['postal_code']);
                $mstore->setAddressCountry($store['address']['country']);
                $mstore->setAddressCountryCode($store['address']['country_code']);
                if (!isset($mcUserName[$apiKey])) {
                    $mcInfo = $this->_api->root->info();
                    $mcUserName[$apiKey] = $mcInfo['account_name'];
                }
                try {
                    $listInfo = $this->_api->lists->getLists($store['list_id']);
                    if (isset($listInfo['name'])) {
                        $mstore->setListName($listInfo['name']);
                        $mstore->setMcAccountName($mcUserName[$apiKey]);
                        $mstore->getResource()->save($mstore);
                    }
                } catch (\SqualoMailMc_Error $e) {
                    $this->log($e->getFriendlyMessage());
                }
            }
        }
    }
    public function saveJsUrl($storeId, $scope = null, $sqmMcStoreId = null)
    {
        if (!$scope) {
            $scope = \Magento\Store\Model\ScopeInterface::SCOPE_STORES;
        }
        if ($this->getConfigValue(self::XML_PATH_ACTIVE, $storeId, $scope)) {
            try {
                $api = $this->getApi($storeId);
                $storeData = $api->ecommerce->stores->get($sqmMcStoreId);
                if (isset($storeData['connected_site']['site_script']['url'])) {
                    $url = $storeData['connected_site']['site_script']['url'];
                    $this->_config->saveConfig(
                        self::XML_SQM_MC_JS_URL,
                        $url,
                        $scope,
                        $storeId
                    );
                }
            } catch (\SqualoMailMc_Error $e) {
                $this->log($e->getFriendlyMessage());
            }
        }

    }
    public function getJsUrl($storeId)
    {
        $url = $this->getConfigValue(self::XML_SQM_MC_JS_URL, $storeId);
        if ($this->getConfigValue(self::XML_PATH_ACTIVE, $storeId) && !$url) {
            $sqmMcStoreId = $this->getConfigValue(self::XML_SQM_MC_STORE, $storeId);
            try {
                $api = $this->getApi($storeId);
                $storeData = $api->ecommerce->stores->get($sqmMcStoreId);
                if (isset($storeData['connected_site']['site_script']['url'])) {
                    $url = $storeData['connected_site']['site_script']['url'];
                    $this->_config->saveConfig(
                        self::XML_SQM_MC_JS_URL,
                        $url,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORES,
                        $storeId
                    );
                }
            } catch (\SqualoMailMc_Error $e) {
                $this->log($e->getFriendlyMessage());
            }
        }
        return $url;
    }

    public function getWebhooksKey()
    {
        $keys =explode("\n", $this->_encryptor->exportKeys());
        $crypt = hash('md5', (string)$keys[0]);
        $key = substr($crypt, 0, (strlen($crypt) / 2));

        return $key;
    }

    public function createWebHook($apikey, $listId, $scope=null, $scopeId=null)
    {
        $events = [
            'subscribe' => true,
            'unsubscribe' => true,
            'profile' => true,
            'cleaned' => true,
            'upemail' => true,
            'campaign' => false
        ];
        $sources = [
            'user' => true,
            'admin' => true,
            'api' => true
        ];
        try {
            $api = $this->getApiByApiKey($apikey);
            $hookUrl = $this->_getUrl(\SqualoMail\SqmMcMagentoTwo\Controller\WebHook\Index::WEBHOOK__PATH, [
            '_scope' => $scopeId,
            'wkey' => $this->getWebhooksKey(),
            '_nosid' => true,
            '_secure' => true]);
            // the urlencode of the hookUrl not work
            $ret = $api->lists->webhooks->add($listId, $hookUrl, $events, $sources);
        } catch (\SqualoMailMc_Error $e) {
            $this->log($e->getFriendlyMessage());
            $ret ['message']= $e->getMessage();
        }
        return $ret;
    }
    public function deleteWebHook($apikey, $listId)
    {
        if (empty($listId)) {
            return;
        }
        try {
            $api = $this->getApiByApiKey($apikey);
            $webhooks = $api->lists->webhooks->getAll($listId);
            $hookUrl = $this->_getUrl(\SqualoMail\SqmMcMagentoTwo\Controller\WebHook\Index::WEBHOOK__PATH, [
                '_nosid' => true,
                '_secure' => true]);
            if (isset($webhooks['webhooks'])) {
                foreach ($webhooks['webhooks'] as $wh) {
                    if ($wh['url'] == $hookUrl) {
                        $api->lists->webhooks->delete($listId, $wh['id']);
                    }
                }
            }
        } catch (\SqualoMailMc_Error $e) {
            $this->log($e->getFriendlyMessage());
        }
    }

    /**
     * @param $listId
     * @param $mail
     * @return \Magento\Newsletter\Model\ResourceModel\Subscriber\Collection
     */
    public function loadListSubscribers($listId, $mail)
    {
        $collection = null;
        $storeIds = $this->getMagentoStoreIdsByListId($listId);
        $storeIds[] = 0;
        if (count($storeIds) > 0) {
            $collection = $this->_subscriberCollection->create();
            $collection
                ->addFieldToFilter('store_id', ['in'=>$storeIds])
                ->addFieldToFilter('subscriber_email', ['eq'=>$mail]);
        }
        return $collection;
    }
    public function getMagentoStoreIdsByListId($listId)
    {
        $storeIds = [];
        foreach ($this->_storeManager->getStores() as $storeId => $val) {
            if ($this->isSqmMcEnabled($storeId)) {
                $storeListId = $this->getConfigValue(self::XML_PATH_LIST, $storeId);
                if ($storeListId == $listId) {
                    $storeIds[] = $storeId;
                }
            }
        }
        return $storeIds;
    }

    /**
     * @param $listId
     * @param $email
     * @return \Magento\Customer\Model\ResourceModel\Customer\Collection
     */
    public function loadListCustomers($listId, $email)
    {
        $customer = null;
        $storeIds = $this->getMagentoStoreIdsByListId($listId);
        if (count($storeIds) > 0) {
            $customer = $this->_customerCollection->create();
            $customer
                ->addFieldToSelect('entity_id')
                ->addFieldToFilter('store_id', ['in' => $storeIds])
                ->addFieldToFilter('email', ['eq' => $email]);
        }
        return $customer;
    }

    /**
     * @param $tableName
     * @param string $conn
     * @return string
     */
    public function getTableName($tableName, $conn = ResourceConnection::DEFAULT_CONNECTION)
    {
        $dbName = $this->_deploymentConfig->get("db/connection/$conn/dbname");
        return $dbName.'.'.$this->_resource->getTableName($tableName, $conn);
    }
    public function getWebsiteId($storeId)
    {
        return $this->_storeManager->getStore($storeId)->getWebsiteId();
    }
    public function getInterest($storeId)
    {
        $rc = [];
        $interest = $this->getConfigValue(self::XML_INTEREST, $storeId);
        if ($interest!='') {
            $interest = explode(",", $interest);
        } else {
            $interest = [];
        }
        try {
            $api = $this->getApi($storeId);
            $listId = $this->getConfigValue(self::XML_PATH_LIST, $storeId);
            $allInterest = $api->lists->interestCategory->getAll($listId);
            if (is_array($allInterest) &&
                array_key_exists('categories', $allInterest) &&
                is_array($allInterest['categories'])) {
                foreach ($allInterest['categories'] as $item) {
                    if (in_array($item['id'], $interest)) {
                        $rc[$item['id']]['interest'] =
                            ['id' => $item['id'], 'title' => $item['title'], 'type' => $item['type']];
                    }
                }
                foreach ($interest as $interestId) {
                    $sqmmcInterest = $api->lists->interestCategory->interests->getAll($listId, $interestId);
                    foreach ($sqmmcInterest['interests'] as $mi) {
                        $rc[$mi['category_id']]['category'][$mi['display_order']] =
                            ['id' => $mi['id'], 'name' => $mi['name'], 'checked' => false];
                    }
                }
            } else {
                $this->log(__('Error retrieving interest groups for store ').$storeId);
                $rc = [];
            }
        } catch (\SqualoMailMc_Error $e) {
            $this->log($e->getFriendlyMessage());
        }
        return $rc;
    }
    public function getSubscriberInterest($subscriberId, $storeId, $interest = null)
    {
        if (!$interest) {
            $interest = $this->getInterest($storeId);
        }
        /**
         * @var $interestGroup \SqualoMail\SqmMcMagentoTwo\Model\SqmMcInterestGroup
         */
        $interestGroup = $this->_interestGroupFactory->create();
        $interestGroup->getBySubscriberIdStoreId($subscriberId, $storeId);
        $serialized = $interestGroup->getGroupdata();
        if ($serialized&&is_array($interest)&&count($interest)) {
            try {
                $groups = $this->unserialize($serialized);
                if (isset($groups['group'])) {
                    foreach ($groups['group'] as $key => $value) {
                        if (array_key_exists($key, $interest)) {
                            if (is_array($value)) {
                                foreach ($value as $groupId) {
                                    foreach ($interest[$key]['category'] as $gkey => $gvalue) {
                                        if ($gvalue['id'] == $groupId) {
                                            $interest[$key]['category'][$gkey]['checked'] = true;
                                        } elseif (!isset($interest[$key]['category'][$gkey]['checked'])) {
                                            $interest[$key]['category'][$gkey]['checked'] = false;
                                        }
                                    }
                                }
                            } else {
                                foreach ($interest[$key]['category'] as $gkey => $gvalue) {
                                    if ($gvalue['id'] == $value) {
                                        $interest[$key]['category'][$gkey]['checked'] = true;
                                    } else {
                                        $interest[$key]['category'][$gkey]['checked'] = false;
                                    }
                                }
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                $this->log($e->getMessage());
            }
        }
        return $interest;
    }
    public function getGmtDate($format = null)
    {
        return $this->_date->gmtDate($format);
    }
    public function getGmtTimeStamp()
    {
        return $this->_date->gmtTimestamp();
    }
    public function getAllApiKeys()
    {
        $apiKeys = [];
        foreach ($this->_storeManager->getStores() as $storeId => $val) {
            $apiKey = $this->getApiKey($storeId);
            if (!in_array($apiKey, $apiKeys)) {
                $apiKeys[] = $apiKey;
            }
        }
        return $apiKeys;
    }
    public function modifyCounter($index, $increment = 1)
    {
        if (array_key_exists($index, $this->counters)) {
            $this->counters[$index] = $this->counters[$index] + $increment;
        } else {
            $this->counters[$index] = 1;
        }
    }
    public function resetCounters()
    {
        $this->counters = [];
    }
    public function getCounters()
    {
        return $this->counters;
    }
    public function serialize($data)
    {
        return $this->_serializer->serialize($data);
    }
    public function unserialize($string)
    {
        return $this->_serializer->unserialize($string);
    }
    public function isEmailSavingEnabled($storeId)
    {
        return $this->_scopeConfig->isSetFlag(
            self::XML_ABANDONEDCART_EMAIL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORES,
            $storeId
        );
    }
    public function resyncAllSubscribers($sqmmcList)
    {
        $connection = $this->_sqmMcSyncE->getResource()->getConnection();
        $tableName = $this->_sqmMcSyncE->getResource()->getMainTable();
        $connection->update(
            $tableName,
            ['sqmmc_sync_modified' => 1],
            "type = '" . self::IS_SUBSCRIBER . "' and sqmmc_store_id = '$sqmmcList'"
        );
    }
    public function decrypt($value)
    {
        return $this->_encryptor->decrypt($value);
    }
    public function encrypt($value)
    {
        return $this->_encryptor->encrypt($value);
    }
    public function getSizeLeftBatchCount($type)
    {
        $batchSizeLeft = self::MAX_BATCHCOUNT;

        switch ($type) {
            case \SqualoMail\SqmMcMagentoTwo\Helper\Data::IS_PRODUCT:
                $batchSizeLeft = self::MAX_GROUP_BATCHCOUNT + (self::MAX_GROUP_BATCHCOUNT - $this->batchCount);
                break;
            case \SqualoMail\SqmMcMagentoTwo\Helper\Data::IS_CUSTOMER:
                $batchSizeLeft = self::MAX_GROUP_BATCHCOUNT + ((2 * self::MAX_GROUP_BATCHCOUNT) - $this->batchCount);
                // for every PUT, there is also PATCH (if customer is subscribed), thats why we divide by 2
                $batchSizeLeft = floor($batchSizeLeft / 2);
                break;
            case \SqualoMail\SqmMcMagentoTwo\Helper\Data::IS_ORDER . 'MODIFIED':
                $batchSizeLeft = self::MAX_GROUP_BATCHCOUNT + ((3 * self::MAX_GROUP_BATCHCOUNT) - $this->batchCount);
                // first we check modified orders, later new, thats why we divide by 2
                $batchSizeLeft = floor($batchSizeLeft / 2);
                break;
            case \SqualoMail\SqmMcMagentoTwo\Helper\Data::IS_ORDER:
                $batchSizeLeft = self::MAX_GROUP_BATCHCOUNT + ((3 * self::MAX_GROUP_BATCHCOUNT) - $this->batchCount);
                break;
            default:
                $batchSizeLeft = self::MAX_GROUP_BATCHCOUNT;
                break;
        }

        return $batchSizeLeft;

    }
    public function setBatchCount($count)
    {
        $this->batchCount = $count;
    }
    public function addBatchCount($count)
    {
        $this->batchCount += $count;
    }
    public function resetBatchCount()
    {
        $this->batchCount = 0;
    }
    public function getBatchCount()
    {
        return $this->batchCount;
    }
}
