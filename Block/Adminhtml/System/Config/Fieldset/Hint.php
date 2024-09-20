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

namespace SqualoMail\SqmMcMagentoTwo\Block\Adminhtml\System\Config\Fieldset;

class Hint extends \Magento\Backend\Block\Template implements
    \Magento\Framework\Data\Form\Element\Renderer\RendererInterface
{
    /**
     * @var string
     */
    protected $_template = 'SqualoMail_SqmMcMagentoTwo::system/config/fieldset/hint.phtml';
    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $_metaData;
    /**
     * @var \SqualoMail\SqmMcMagentoTwo\Helper\Data
     */
    private $_helper;
    /**
     * @var \Magento\Backend\Block\Template\Context
     */
    private $_context;
    /**
     * @var \SqualoMail\SqmMcMagentoTwo\Model\Config\ModuleVersion
     */
    private $_moduleVersion;

    /**
     * Hint constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetaData
     * @param \SqualoMail\SqmMcMagentoTwo\Helper\Data $helper
     * @param \SqualoMail\SqmMcMagentoTwo\Model\Config\ModuleVersion $moduleVersion
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\App\ProductMetadataInterface $productMetaData,
        \SqualoMail\SqmMcMagentoTwo\Helper\Data $helper,
        \SqualoMail\SqmMcMagentoTwo\Model\Config\ModuleVersion $moduleVersion,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_metaData = $productMetaData;
        $this->_helper = $helper;
        $this->_moduleVersion   = $moduleVersion;
        $this->_context = $context;
    }
    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return mixed
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->toHtml();
    }

    public function getModuleVersion()
    {
        return $this->_moduleVersion->getModuleVersion('SqualoMail_SqmMcMagentoTwo');
    }
    public function getHasApiKey()
    {
        $apikey = $this->_helper->getApiKey($this->_context->getStoreManager()->getStore()->getId());
        if ($apikey) {
            return true;
        } else {
            return false;
        }
    }
    public function getScope()
    {
        $params = $this->getRequest()->getParams();
        $scope = 'default';
        if (isset($params['website'])) {
            $scope = 'website';
        } elseif (isset($params['store'])) {
            $scope = 'store';
        }
        return $scope;
    }
    public function getScopeId()
    {
        $params = $this->getRequest()->getParams();
        $scopeId = 0;
        if (isset($params['website'])) {
            $scopeId = $params['website'];
        } elseif (isset($params['store'])) {
            $scopeId = $params['store'];
        }
        return $scopeId;
    }
}
