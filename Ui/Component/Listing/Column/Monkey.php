<?php
/**
 * mc-magento2 Magento Component
 *
 * @category SqualoMail
 * @package mc-magento2
 * @author Ebizmarts Team <info@ebizmarts.com>
 * @copyright Ebizmarts (http://ebizmarts.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @date: 3/15/17 1:23 AM
 * @file: Monkey.php
 */
namespace SqualoMail\SqmMcMagentoTwo\Ui\Component\Listing\Column;

use \Magento\Sales\Api\OrderRepositoryInterface;
use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Ui\Component\Listing\Columns\Column;
use \Magento\Framework\Api\SearchCriteriaBuilder;
use \Magento\Store\Model\StoreManagerInterface;

class Monkey extends Column
{
    /**
     * @var OrderRepositoryInterface
     */
    protected $_orderRepository;
    /**
     * @var SearchCriteriaBuilder
     */
    protected $_searchCriteria;
    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $_assetRepository;
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_requestInterfase;
    /**
     * @var \SqualoMail\SqmMcMagentoTwo\Helper\Data
     */
    protected $_helper;
    /**
     * @var \SqualoMail\SqmMcMagentoTwo\Model\ResourceModel\SqmMcSyncEcommerce\CollectionFactory
     */
    protected $_syncCommerceCF;
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;
    /**
     * @var \SqualoMail\SqmMcMagentoTwo\Model\SqmMcErrorsFactory
     */
    protected $_mailChimpErrorsFactory;

    /**
     * Monkey constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param \Magento\Framework\View\Asset\Repository $assetRepository
     * @param \Magento\Framework\App\RequestInterface $requestInterface
     * @param SearchCriteriaBuilder $criteria
     * @param \SqualoMail\SqmMcMagentoTwo\Helper\Data $helper
     * @param \SqualoMail\SqmMcMagentoTwo\Model\ResourceModel\SqmMcSyncEcommerce\CollectionFactory $syncCommerceCF
     * @param \SqualoMail\SqmMcMagentoTwo\Model\SqmMcErrorsFactory $mailChimpErrorsFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        OrderRepositoryInterface $orderRepository,
        \Magento\Framework\View\Asset\Repository $assetRepository,
        \Magento\Framework\App\RequestInterface $requestInterface,
        SearchCriteriaBuilder $criteria,
        \SqualoMail\SqmMcMagentoTwo\Helper\Data $helper,
        \SqualoMail\SqmMcMagentoTwo\Model\ResourceModel\SqmMcSyncEcommerce\CollectionFactory $syncCommerceCF,
        \SqualoMail\SqmMcMagentoTwo\Model\SqmMcErrorsFactory $mailChimpErrorsFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        array $components = [],
        array $data = []
    ) {
    
        $this->_orderRepository = $orderRepository;
        $this->_searchCriteria  = $criteria;
        $this->_assetRepository = $assetRepository;
        $this->_requestInterfase= $requestInterface;
        $this->_helper          = $helper;
        $this->_syncCommerceCF  = $syncCommerceCF;
        $this->_orderFactory    = $orderFactory;
        $this->_mailChimpErrorsFactory  = $mailChimpErrorsFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $status = $item['mailchimp_flag'];
                $order = $this->_orderFactory->create()->loadByIncrementId($item['increment_id']);
                $params = ['_secure' => $this->_requestInterfase->isSecure()];
                if ($this->_helper->getConfigValue(\SqualoMail\SqmMcMagentoTwo\Helper\Data::XML_PATH_ACTIVE, $order->getStoreId())) {
                    $mailchimpStoreId = $this->_helper->getConfigValue(
                        \SqualoMail\SqmMcMagentoTwo\Helper\Data::XML_SQM_MC_STORE,
                        $order->getStoreId()
                    );
                    $syncData = $this->_helper->getChimpSyncEcommerce(
                        $mailchimpStoreId,
                        $order->getId(),
                        \SqualoMail\SqmMcMagentoTwo\Helper\Data::IS_ORDER
                    );
                    $alt = '';
                    if (!$syncData || $syncData->getMailchimpStoreId() != $mailchimpStoreId ||
                        $syncData->getRelatedId() != $order->getId() ||
                        $syncData->getType() != \SqualoMail\SqmMcMagentoTwo\Helper\Data::IS_ORDER) {
                        $url = $this->_assetRepository->getUrlWithParams(
                            'SqualoMail_SqmMcMagentoTwo::images/no.png',
                            $params
                        );
                        $text = __('Syncing');
                    } else {
                        $sync = $syncData->getMailchimpSent();
                        switch ($sync) {
                            case \SqualoMail\SqmMcMagentoTwo\Helper\Data::SYNCED:
                                $url = $this->_assetRepository->getUrlWithParams(
                                    'SqualoMail_SqmMcMagentoTwo::images/yes.png',
                                    $params
                                );
                                $text = __('Synced');
                                break;
                            case \SqualoMail\SqmMcMagentoTwo\Helper\Data::WAITINGSYNC:
                                $url = $this->_assetRepository->getUrlWithParams(
                                    'SqualoMail_SqmMcMagentoTwo::images/waiting.png',
                                    $params
                                );
                                $text = __('Waiting');
                                break;
                            case \SqualoMail\SqmMcMagentoTwo\Helper\Data::SYNCERROR:
                                $url = $this->_assetRepository->getUrlWithParams(
                                    'SqualoMail_SqmMcMagentoTwo::images/error.png',
                                    $params
                                );
                                $text = __('Error');
                                $orderError = $this->_getError($order->getId(), $order->getStoreId());
                                if ($orderError) {
                                    $alt = $orderError->getErrors();
                                }
                                break;
                            case \SqualoMail\SqmMcMagentoTwo\Helper\Data::NEEDTORESYNC:
                                $url = $this->_assetRepository->getUrlWithParams(
                                    'SqualoMail_SqmMcMagentoTwo::images/resync.png',
                                    $params
                                );
                                $text = __('Resyncing');
                                break;
                            case \SqualoMail\SqmMcMagentoTwo\Helper\Data::NOTSYNCED:
                                $url = $this->_assetRepository->getUrlWithParams(
                                    'SqualoMail_SqmMcMagentoTwo::images/never.png',
                                    $params
                                );
                                $text = __('With error');
                                $alt = $syncData->getMailchimpSyncError();
                                break;
                            default:
                                $url ='';
                                $text = '';
                        }
                    }
                    $item['mailchimp_sync'] =
                        "<div style='width: 50%;margin: 0 auto;text-align: center'><img src='".$url."' style='border: none; width: 5rem; text-align: center; max-width: 100%' title='$alt' />$text</div>";
                    if ($status) {
                        $url = $this->_assetRepository->getUrlWithParams('SqualoMail_SqmMcMagentoTwo::images/freddie.png', $params);
                        $item['mailchimp_status'] =
                            "<div style='width: 50%;margin: 0 auto'><img src='".$url."' style='border: none; width: 5rem; text-align: center; max-width: 100%'/></div>";
                    }
                }
            }
        }

        return $dataSource;
    }
    private function _getError($orderId, $storeId)
    {
        /**
         * @var $error \SqualoMail\SqmMcMagentoTwo\Model\SqmMcErrors
         */
        $error = $this->_mailChimpErrorsFactory->create();
        return $error->getByStoreIdType($storeId, $orderId, \SqualoMail\SqmMcMagentoTwo\Helper\Data::IS_ORDER);
    }
}
