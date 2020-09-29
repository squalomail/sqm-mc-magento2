<?php
/**
 * mc-magento2 Magento Component
 *
 * @category SqualoMail
 * @package mc-magento2
 * @author Ebizmarts Team <info@ebizmarts.com>
 * @copyright Ebizmarts (http://ebizmarts.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @date: 4/12/17 11:35 AM
 * @file: Stores.php
 */

namespace SqualoMail\SqmMcMagentoTwo\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use SqualoMail\SqmMcMagentoTwo\Model\SqmMcStoresFactory;

class Stores extends Action
{
    /**
     * @var Registry
     */
    protected $_coreRegistry;
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;
    /**
     * @var SqmMcStoresFactory
     */
    protected $_mailchimpStoresFactory;
    /**
     * @var \SqualoMail\SqmMcMagentoTwo\Helper\Data
     */
    protected $_helper;

    /**
     * Stores constructor.
     * @param Context $context
     * @param Registry $registry
     * @param PageFactory $resultPageFactory
     * @param SqmMcStoresFactory $storesFactory
     * @param \SqualoMail\SqmMcMagentoTwo\Helper\Data $helper
     */
    public function __construct(
        Context $context,
        Registry $registry,
        PageFactory $resultPageFactory,
        SqmMcStoresFactory $storesFactory,
        \SqualoMail\SqmMcMagentoTwo\Helper\Data $helper
    ) {

        parent::__construct($context);
        $this->_coreRegistry            = $registry;
        $this->_resultPageFactory       = $resultPageFactory;
        $this->_mailchimpStoresFactory  = $storesFactory;
        $this->_helper                  = $helper;
    }
    public function execute()
    {
        return 1;
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('SqualoMail_SqmMcMagentoTwo::stores_grid');
    }
}
