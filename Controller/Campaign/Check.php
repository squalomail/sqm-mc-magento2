<?php
/**
 * MailChimp Magento Component
 *
 * @category SqualoMail
 * @package SqmMcMagentoTwo
 * @author Ebizmarts Team <info@ebizmarts.com>
 * @copyright Ebizmarts (http://ebizmarts.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @date: 1/19/19 12:36 PM
 * @file: Check.php
 */

namespace SqualoMail\SqmMcMagentoTwo\Controller\Campaign;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class Check extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \SqualoMail\SqmMcMagentoTwo\Helper\Data
     */
    protected $_helper;
    /**
     * @var ResultFactory
     */
    protected $_resultFactory;
    protected $_storeManager;

    /**
     * Get constructor.
     * @param Context $context
     * @param \SqualoMail\SqmMcMagentoTwo\Helper\Data $helper
     */
    public function __construct(
        Context $context,
        \SqualoMail\SqmMcMagentoTwo\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {

        parent::__construct($context);
        $this->_resultFactory       = $context->getResultFactory();
        $this->_helper              = $helper;
        $this->_storeManager        = $storeManager;
    }

    public function execute()
    {
        $param = $this->getRequest()->getParams();
        $mc_cid = null;
        if (key_exists('mc_cid', $param)) {
            $mc_cid = $param['mc_cid'];
            $magentoStoreId = $this->_storeManager->getStore()->getId();
            $api = $this->_helper->getApi($magentoStoreId);
            try {
                $campaign =$api->campaigns->get($mc_cid);
                $sqmmcList = $this->_helper->getConfigValue(
                    \SqualoMail\SqmMcMagentoTwo\Helper\Data::XML_PATH_LIST,
                    $magentoStoreId
                );
                if ($sqmmcList == $campaign['recipients']['list_id']) {
                    $valid = 1;
                } else {
                    $valid = 0;
                }
            } catch (\Exception $e) {
                $valid = 0;
            }
        } else {
            $valid = 0;
        }
        $resultJson = $this->_resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData(['valid' => $valid]);
        return $resultJson;
    }
}
