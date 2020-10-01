<?php
/**
 * mc-magento2 Magento Component
 *
 * @category SqualoMail
 * @package mc-magento2
 * @author Ebizmarts Team <info@ebizmarts.com>
 * @copyright Ebizmarts (http://ebizmarts.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @date: 10/31/16 3:28 PM
 * @file: UpgradeData.php
 */
namespace SqualoMail\SqmMcMagentoTwo\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\App\DeploymentConfig;

class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var ResourceConnection
     */
    protected $_resource;
    /**
     * @var DeploymentConfig
     */
    protected $_deploymentConfig;
    /**
     * @var \SqualoMail\SqmMcMagentoTwo\Model\ResourceModel\SqmMcInterestGroup\CollectionFactory
     */
    protected $_insterestGroupCollectionFactory;
    /**
     * @var \SqualoMail\SqmMcMagentoTwo\Model\ResourceModel\SqmMcWebhookRequest\CollectionFactory
     */
    protected $_webhookCollectionFactory;
    /**
     * @var \SqualoMail\SqmMcMagentoTwo\Helper\Data
     */
    protected $_helper;
    /**
     * @var \Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory
     */
    protected $configFactory;

    /**
     * UpgradeData constructor.
     * @param ResourceConnection $resource
     * @param DeploymentConfig $deploymentConfig
     * @param \SqualoMail\SqmMcMagentoTwo\Model\ResourceModel\SqmMcInterestGroup\CollectionFactory $interestGroupCollectionFactory
     * @param \SqualoMail\SqmMcMagentoTwo\Model\ResourceModel\SqmMcWebhookRequest\CollectionFactory $webhookCollectionFactory
     * @param \Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory $configFactory
     * @param \SqualoMail\SqmMcMagentoTwo\Helper\Data $helper
     */
    public function __construct(
        ResourceConnection $resource,
        DeploymentConfig $deploymentConfig,
        \SqualoMail\SqmMcMagentoTwo\Model\ResourceModel\SqmMcInterestGroup\CollectionFactory $interestGroupCollectionFactory,
        \SqualoMail\SqmMcMagentoTwo\Model\ResourceModel\SqmMcWebhookRequest\CollectionFactory $webhookCollectionFactory,
        \Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory $configFactory,
        \SqualoMail\SqmMcMagentoTwo\Helper\Data $helper
    ) {
    
        $this->_resource            = $resource;
        $this->_deploymentConfig    = $deploymentConfig;
        $this->_insterestGroupCollectionFactory = $interestGroupCollectionFactory;
        $this->_webhookCollectionFactory        = $webhookCollectionFactory;
        $this->configFactory                    = $configFactory;
        $this->_helper              = $helper;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        if (version_compare($context->getVersion(), '1.0.24') < 0) {
            $setup->startSetup();
            $connection = $this->_resource->getConnectionByName('default');
            if ($this->_deploymentConfig->get(
                \Magento\Framework\Config\ConfigOptionsListConstants::CONFIG_PATH_DB_CONNECTIONS . '/sales'
            )
            ) {
                    $salesConnection = $this->_resource->getConnectionByName('sales');
            } else {
                    $salesConnection = $connection;
            }
            $table = $setup->getTable('sales_order');
            $select = $salesConnection->select()
                ->from(
                    false,
                    ['sqmmc_flag' => new \Zend_Db_Expr('IF(sqmmc_abandonedcart_flag OR sqmmc_campaign_id OR sqmmc_landing_page, 1, 0)')]
                )->join(['O'=>$table], 'O.entity_id = G.entity_id', []);

            $query = $salesConnection->updateFromSelect($select, ['G' => $setup->getTable('sales_order_grid')]);

            $salesConnection->query($query);
            $setup->endSetup();
        }
        if (version_compare($context->getVersion(), '1.2.32') < 0) {
            // delete the old serialized data from core_config_data
            $setup->startSetup();
            $connection = $this->_resource->getConnectionByName('default');
            $table = $setup->getTable('core_config_data');
            try {
                $connection->delete($table, ['path = ?'=> \SqualoMail\SqmMcMagentoTwo\Helper\Data::XML_MERGEVARS]);
            } catch (\Exception $e) {
                $this->_helper->log($e->getMessage());
            }

            // empty table sqmmc_interest_group
            /**
             * @var \SqualoMail\SqmMcMagentoTwo\Model\ResourceModel\SqmMcInterestGroup $item
             */
            $table = $setup->getTable('sqmmc_interest_group');

            try {
                $connection->delete($table);
            } catch (\Exception $e) {
                $this->_helper->log($e->getMessage());
            }
            // convert table sqmmc_webhook_request
            /**
             * @var \SqualoMail\SqmMcMagentoTwo\Model\ResourceModel\SqmMcWebhookRequest $webhookItem
             */
            $lastId = 0;
            $done = false;
            while (!$done) {
                $webhookCollection = $this->_webhookCollectionFactory->create();
                $webhookCollection->addFieldToFilter('processed', ['neq' => 1]);
                $webhookCollection->addFieldToFilter('id', ['gt' => $lastId]);
                $webhookCollection->getSelect()->limit(500);
                if (!$webhookCollection->getSize()) {
                    $done = true;
                } else {
                    foreach ($webhookCollection as $webhookItem) {
                        try {
                            $webhookItem->setProcessed(\SqualoMail\SqmMcMagentoTwo\Cron\Webhook::DATA_NOT_CONVERTED);
                            $webhookItem->getResource()->save($webhookItem);
                        } catch (\Exception $e) {
                            $this->_helper->log($e->getMessage());
                            $webhookItem->setProcesed(\SqualoMail\SqmMcMagentoTwo\Cron\Webhook::DATA_WITH_ERROR);
                            $webhookItem->getResource()->save($webhookItem);
                        }
                        $lastId = $webhookItem->getId();
                    }
                }
            }
        }
        if (version_compare($context->getVersion(), '102.3.35') < 0) {
            $configCollection = $this->configFactory->create();
            $configCollection->addFieldToFilter('path', ['eq' => \SqualoMail\SqmMcMagentoTwo\Helper\Data::XML_PATH_APIKEY]);
            /**
             * @var $config \Magento\Config\Model\ResourceModel\Config
             */
            foreach ($configCollection as $config) {
                try {
                    $config->setValue($this->_helper->encrypt($config->getvalue()));
                    $config->getResource()->save($config);
                } catch (\Exception $e) {
                    $this->_helper->log($e->getMessage());
                }
            }
            $configCollection = $this->configFactory->create();
            $configCollection->addFieldToFilter(
                'path',
                ['eq' => \SqualoMail\SqmMcMagentoTwo\Helper\Data::XML_PATH_APIKEY_LIST]
            );
            foreach ($configCollection as $config) {
                $config->getResource()->delete($config);
            }
        }
    }
}
