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

class ResyncSubscribers extends \Magento\Backend\App\Action
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
     * ResetLocalErrors constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param JsonFactory $resultJsonFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
     * @param \SqualoMail\SqmMcMagentoTwo\Helper\Data $helper
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        JsonFactory $resultJsonFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \SqualoMail\SqmMcMagentoTwo\Helper\Data $helper
    ) {
    
        parent::__construct($context);
        $this->resultJsonFactory    = $resultJsonFactory;
        $this->helper               = $helper;
        $this->storeManager         = $storeManagerInterface;
    }

    public function execute()
    {
        $valid = 1;
        $message = '';
        $params = $this->getRequest()->getParams();
        if (isset($params['website'])) {
            $sqmmcList = $this->helper->getConfigValue(
                \SqualoMail\SqmMcMagentoTwo\Helper\Data::XML_PATH_LIST,
                $params['website'],
                'website'
            );
        } elseif (isset($params['store'])) {
            $sqmmcList = $this->helper->getConfigValue(
                \SqualoMail\SqmMcMagentoTwo\Helper\Data::XML_PATH_LIST,
                $params['store'],
                'store'
            );
        } else {
            $sqmmcList = $this->helper->getConfigValue(
                \SqualoMail\SqmMcMagentoTwo\Helper\Data::XML_PATH_LIST,
                $this->storeManager->getStore()
            );
        }

        $resultJson = $this->resultJsonFactory->create();
        try {
            $this->helper->resyncAllSubscribers($sqmmcList);
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
        return $this->_authorization->isAllowed('SqualoMail_SqmMcMagentoTwo::config_mailchimp');
    }
}
