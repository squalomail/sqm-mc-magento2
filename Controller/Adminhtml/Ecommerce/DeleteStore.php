<?php
/**
 * mc-magento2 Magento Component
 *
 * @category SqualoMail
 * @package mc-magento2
 * @author Ebizmarts Team <info@ebizmarts.com>
 * @copyright Ebizmarts (http://ebizmarts.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @date: 2/21/17 5:07 PM
 * @file: ResetLocalErrors.php
 */

namespace SqualoMail\SqmMcMagentoTwo\Controller\Adminhtml\Ecommerce;

use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\ValidatorException;
use Symfony\Component\Config\Definition\Exception\Exception;

class DeleteStore extends \Magento\Backend\App\Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;
    /**
     * @var \SqualoMail\SqmMcMagentoTwo\Helper\Data
     */
    protected $helper;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $_config;

    /**
     * DeleteStore constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param JsonFactory $resultJsonFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
     * @param \SqualoMail\SqmMcMagentoTwo\Helper\Data $helper
     * @param \Magento\Config\Model\ResourceModel\Config $config
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        JsonFactory $resultJsonFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \SqualoMail\SqmMcMagentoTwo\Helper\Data $helper,
        \Magento\Config\Model\ResourceModel\Config $config
    ) {
    
        parent::__construct($context);
        $this->resultJsonFactory    = $resultJsonFactory;
        $this->helper               = $helper;
        $this->storeManager         = $storeManagerInterface;
        $this->_config              = $config;
    }

    public function execute()
    {
        $valid = 1;
        $message = '';
        $params = $this->getRequest()->getParams();
        if (isset($params['website'])) {
            $scope = 'website';
            $storeId = $params['website'];
        } elseif (isset($params['store'])) {
            $scope = 'store';
            $storeId = $params['store'];
        } else {
            //$storeId = $this->storeManager->getDefaultStoreView()->getWebsiteId();
            $storeId = 0;
            $scope = 'default';
        }

        $sqmmcStore = $this->helper->getConfigValue(
            \SqualoMail\SqmMcMagentoTwo\Helper\Data::XML_SQM_MC_STORE,
            $storeId,
            $scope
        );
        $resultJson = $this->resultJsonFactory->create();
        try {
            $this->helper->deleteStore($sqmmcStore);
            $this->_config->deleteConfig(\SqualoMail\SqmMcMagentoTwo\Helper\Data::XML_SQM_MC_STORE, $scope, $storeId);
        } catch (ValidatorException $e) {
            $valid = 0;
            $message = $e->getMessage();
        }
        return $resultJson->setData([
            'valid' => (int)$valid,
            'message' => $message,
        ]);
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('SqualoMail_SqmMcMagentoTwo::config_sqmmc');
    }
}
