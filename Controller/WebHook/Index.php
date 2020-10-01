<?php
/**
 * mc-magento2 Magento Component
 *
 * @category SqualoMail
 * @package mc-magento2
 * @author Ebizmarts Team <info@ebizmarts.com>
 * @copyright Ebizmarts (http://ebizmarts.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @date: 5/23/17 3:36 PM
 * @file: Index.php
 */

namespace SqualoMail\SqmMcMagentoTwo\Controller\WebHook;

use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultInterface;

class Index extends Action implements CsrfAwareActionInterface
{
    const WEBHOOK__PATH = 'sqmmc/webhook/index';
    /**
     * @var ResultFactory
     */
    protected $_resultFactory;
    /**
     * @var \SqualoMail\SqmMcMagentoTwo\Helper\Data
     */
    protected $_helper;
    /**
     * @var \SqualoMail\SqmMcMagentoTwo\Model\SqmMcWebhookRequestFactory
     */
    protected $_chimpWebhookRequestFactory;
    private $_remoteAddress;

    /**
     * Index constructor.
     * @param Context $context
     * @param \SqualoMail\SqmMcMagentoTwo\Helper\Data $helper
     * @param \SqualoMail\SqmMcMagentoTwo\Model\SqmMcWebhookRequestFactory $chimpWebhookRequestFactory
     * @param \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress
     */
    public function __construct(
        Context $context,
        \SqualoMail\SqmMcMagentoTwo\Helper\Data $helper,
        \SqualoMail\SqmMcMagentoTwo\Model\SqmMcWebhookRequestFactory $chimpWebhookRequestFactory,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress
    ) {
    
        parent::__construct($context);
        $this->_resultFactory              = $context->getResultFactory();
        $this->_helper                      = $helper;
        $this->_chimpWebhookRequestFactory  = $chimpWebhookRequestFactory;
        $this->_remoteAddress               = $remoteAddress;
    }
    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException {
        return null;
    }
    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    public function execute()
    {
        $requestKey = $this->getRequest()->getParam('wkey');
        /**
         * @var ResultInterface $result
         */
        $result = $this->_resultFactory->create(ResultFactory::TYPE_RAW);
        $result->setContents('');
        if (!$requestKey) {
            $this->_helper->log('No wkey parameter from ip: '.$this->_remoteAddress->getRemoteAddress());
            $result->setHttpResponseCode(403);
            return $result;
        }
        $key = $this->_helper->getWebhooksKey();
        if ($key!=$requestKey) {
            $this->_helper->log('wkey parameter is invalid from ip: '.$this->_remoteAddress->getRemoteAddress());
            $result->setHttpResponseCode(403);
            return $result;
        }
        if ($this->getRequest()->getPost('type')) {
            $request = $this->getRequest()->getPost();
            if ($this->_helper->getConfigValue(\SqualoMail\SqmMcMagentoTwo\Helper\Data::XML_PATH_WEBHOOK_ACTIVE) ||
                $request['type']==\SqualoMail\SqmMcMagentoTwo\Cron\Webhook::TYPE_SUBSCRIBE) {
                try {
                    $chimpRequest = $this->_chimpWebhookRequestFactory->create();
                    $chimpRequest->setType($request['type']);
                    $chimpRequest->setFiredAt($request['fired_at']);
                    $chimpRequest->setDataRequest($this->_helper->serialize($request['data']));
                    $chimpRequest->setProcessed(false);
                    $chimpRequest->getResource()->save($chimpRequest);
                    $result->setHttpResponseCode(200);
                } catch(\Exception $e) {
                    $this->_helper->log($e->getMessage());
                    $this->_helper->log($request['data']);
                    $result->setHttpResponseCode(403);
                }
            }
        } else {
            $this->_helper->log('An empty request comes from ip: '.$this->_remoteAddress->getRemoteAddress());
            $result->setHttpResponseCode(200);
        }
        return $result;
    }
}
