<?php
/**
 * mc-magento2 Magento Component
 *
 * @category SqualoMail
 * @package mc-magento2
 * @author Ebizmarts Team <info@ebizmarts.com>
 * @copyright Ebizmarts (http://ebizmarts.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @date: 5/3/17 3:28 PM
 * @file: Getresponse.php
 */

namespace SqualoMail\SqmMcMagentoTwo\Controller\Adminhtml\Errors;

use Magento\Framework\Controller\ResultFactory;

class Getresponse extends \Magento\Backend\App\Action
{
    const MAX_RETRIES = 5;
    /**
     * @var ResultFactory
     */
    protected $_resultFactory;
    /**
     * @var \SqualoMail\SqmMcMagentoTwo\Model\SqmMcErrorsFactory
     */
    protected $_errorsFactory;
    /**
     * @var \SqualoMail\SqmMcMagentoTwo\Model\Api\Result
     */
    protected $_result;
    /**
     * @var \SqualoMail\SqmMcMagentoTwo\Helper\Data
     */
    protected $_helper;
    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    protected $_driver;

    /**
     * Getresponse constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \SqualoMail\SqmMcMagentoTwo\Model\SqmMcErrorsFactory $errorsFactory
     * @param \SqualoMail\SqmMcMagentoTwo\Helper\Data $helper
     * @param \SqualoMail\SqmMcMagentoTwo\Model\Api\Result $result
     * @param \Magento\Framework\Filesystem\Driver\File $driver
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \SqualoMail\SqmMcMagentoTwo\Model\SqmMcErrorsFactory $errorsFactory,
        \SqualoMail\SqmMcMagentoTwo\Helper\Data $helper,
        \SqualoMail\SqmMcMagentoTwo\Model\Api\Result $result,
        \Magento\Framework\Filesystem\Driver\File $driver
    ) {
        parent::__construct($context);
        $this->_resultFactory       = $context->getResultFactory();
        $this->_errorsFactory       = $errorsFactory;
        $this->_result              = $result;
        $this->_helper              = $helper;
        $this->_driver              = $driver;
    }

    public function execute()
    {
        $errorId    = $this->getRequest()->getParam('id');
        $errors = $this->_errorsFactory->create();
        $errors->getResource()->load($errors, $errorId);
        $batchId = $errors->getBatchId();
        $fileContent = [];
        $counter = 0;
        do {
            $counter++;
            $files = $this->_result->getBatchResponse($batchId, $errors->getStoreId());
            if ($files===false) {
                $fileContent = "Response was deleted from SqualoMail servers";
                break;
            }
            foreach ($files as $file) {
                $items = json_decode($this->_driver->fileGetContents($file));
                foreach ($items as $item) {
                    $content = [
                        'status_code' => $item->status_code,
                        'operation_id' => $item->operation_id,
                        'response' => json_decode($item->response)
                    ];
                    $fileContent[] = $content;
                }
                $this->_driver->deleteFile($file);
            }
            $baseDir = $this->_helper->getBaseDir();
            if ($this->_driver->isDirectory($baseDir . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR .
                \SqualoMail\SqmMcMagentoTwo\Model\Api\Result::SQM_MC_TEMP_DIR . DIRECTORY_SEPARATOR . $batchId)) {
                $this->_driver->deleteDirectory(
                    $baseDir . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR .
                    \SqualoMail\SqmMcMagentoTwo\Model\Api\Result::SQM_MC_TEMP_DIR . DIRECTORY_SEPARATOR . $batchId
                );
            }
        } while (!count($fileContent) && $counter<self::MAX_RETRIES);
        $resultJson =$this->_resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setHeader('Content-disposition', 'attachment; filename='.$batchId.'.json');
        $resultJson->setHeader('Content-type', 'application/json');
        $data = json_encode($fileContent, JSON_PRETTY_PRINT);
        $resultJson->setJsonData($data);
        return $resultJson;
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('SqualoMail_SqmMcMagentoTwo::error_grid');
    }
}
