<?php
/**
 * mc-magento2 Magento Component
 *
 * @category SqualoMail
 * @package mc-magento2
 * @author Ebizmarts Team <info@ebizmarts.com>
 * @copyright Ebizmarts (http://ebizmarts.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @date: 10/21/16 4:09 PM
 * @file: Result.php
 */

namespace SqualoMail\SqmMcMagentoTwo\Model\Api;

class Result
{
    const SQM_MC_TEMP_DIR = 'SqualoMail';
    /**
     * @var \SqualoMail\SqmMcMagentoTwo\Model\ResourceModel\SqmMcSyncBatches\CollectionFactory
     */
    private $_batchCollection;
    /**
     * @var \SqualoMail\SqmMcMagentoTwo\Helper\Data
     */
    private $_helper;
    /**
     * @var \Magento\Framework\Archive
     */
    private $_archive;
    /**
     * @var \SqualoMail\SqmMcMagentoTwo\Model\SqmMcErrorsFactory
     */
    private $_chimpErrors;
    /**
     * @var \Magento\Framework\Filesystem\Driver\File
     */
    private $_driver;
    /**
     * @var \Magento\Framework\HTTP\Client\CurlFactory
     */
    private $_curlFactory;

    /**
     * Result constructor.
     * @param \SqualoMail\SqmMcMagentoTwo\Helper\Data $helper
     * @param \SqualoMail\SqmMcMagentoTwo\Model\ResourceModel\SqmMcSyncBatches\CollectionFactory $batchCollection
     * @param \SqualoMail\SqmMcMagentoTwo\Model\SqmMcErrorsFactory $chimpErrors
     * @param \Magento\Framework\Archive $archive
     * @param \Magento\Framework\Filesystem\Driver\File $driver
     * @param \Magento\Framework\HTTP\Client\CurlFactory $curlFactory
     */
    public function __construct(
        \SqualoMail\SqmMcMagentoTwo\Helper\Data $helper,
        \SqualoMail\SqmMcMagentoTwo\Model\ResourceModel\SqmMcSyncBatches\CollectionFactory $batchCollection,
        \SqualoMail\SqmMcMagentoTwo\Model\SqmMcErrorsFactory $chimpErrors,
        \Magento\Framework\Archive $archive,
        \Magento\Framework\Filesystem\Driver\File $driver,
        \Magento\Framework\HTTP\Client\CurlFactory $curlFactory
    ) {
    
        $this->_batchCollection     = $batchCollection;
        $this->_helper              = $helper;
        $this->_archive             = $archive;
        $this->_chimpErrors         = $chimpErrors;
        $this->_driver              = $driver;
        $this->_curlFactory         = $curlFactory;
    }
    public function processResponses($storeId, $isSqmMcStoreId = false, $sqmmcStoreId=null)
    {
        $collection = $this->_batchCollection->create();
        $collection
            ->addFieldToFilter('store_id', ['eq' => $storeId])
            ->addFieldToFilter('status', ['eq' => 'pending'])
            ->addFieldToFilter('sqmmc_store_id', ['eq' => $sqmmcStoreId]);
        /**
         * @var $item \SqualoMail\SqmMcMagentoTwo\Model\SqmMcSyncBatches
         */
        $item = null;
        foreach ($collection as $item) {
            try {
                $files = $this->getBatchResponse($item->getBatchId(), $storeId);
                if (is_array($files) && count($files)) {
                    $this->processEachResponseFile($files, $item->getBatchId(), $sqmmcStoreId, $storeId);
                    $item->setStatus(\SqualoMail\SqmMcMagentoTwo\Helper\Data::BATCH_COMPLETED);
                    $item->setModifiedDate($this->_helper->getGmtDate());
                    $item->getResource()->save($item);
                } elseif ($files === false) {
                    $item->setStatus(\SqualoMail\SqmMcMagentoTwo\Helper\Data::BATCH_ERROR);
                    $item->getResource()->save($item);
                    $this->_helper->deleteAllByBatchId($item->getBatchId());
                    continue;
                }
                $baseDir = $this->_helper->getBaseDir();
                if ($this->_driver->isDirectory($baseDir . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR .
                    self::SQM_MC_TEMP_DIR . DIRECTORY_SEPARATOR . $item->getBatchId())) {
                    $dirFiles = $this->_driver->readDirectory(
                        $baseDir . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR .
                        self::SQM_MC_TEMP_DIR . DIRECTORY_SEPARATOR .
                        $item->getBatchId().DIRECTORY_SEPARATOR
                    );
                    foreach ($dirFiles as $dirFile) {
                        $this->_driver->deleteFile(
                            $baseDir . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR .
                            self::SQM_MC_TEMP_DIR . DIRECTORY_SEPARATOR .
                            $item->getBatchId().DIRECTORY_SEPARATOR.$dirFile
                        );
                    }
                    $this->_driver->deleteDirectory($baseDir . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR .
                        self::SQM_MC_TEMP_DIR . DIRECTORY_SEPARATOR . $item->getBatchId());
                }
            } catch (\Exception $e) {
                $this->_helper->log("Error with a response: " . $e->getMessage());
            }
        }
    }
    public function getBatchResponse($batchId, $storeId = null)
    {
        $files = [];
        try {
            $baseDir = $this->_helper->getBaseDir();
            $api = $this->_helper->getApi($storeId);
            // check the status of the job
            $response = $api->batchOperation->status($batchId);

            if (isset($response['status']) && $response['status'] == 'finished') {
                // Create temporary directory, if that does not exist
                if (!$this->_driver->isDirectory($baseDir . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . self::SQM_MC_TEMP_DIR)) {
                    $this->_driver->createDirectory($baseDir . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . self::SQM_MC_TEMP_DIR);
                }
                // get the tar.gz file with the results
                $fileUrl = urldecode($response['response_body_url']);
                $fileName = $baseDir . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR .
                    self::SQM_MC_TEMP_DIR . DIRECTORY_SEPARATOR . $batchId;
                $fd = $this->_driver->fileOpen($fileName . '.tar.gz', 'w');
                $ch = $this->_curlFactory->create();
                $ch->setOption(CURLOPT_URL, $fileUrl);
                $ch->setOption(CURLOPT_FILE, $fd);
                $ch->setOption(CURLOPT_FOLLOWLOCATION, true);
                $r =$ch->get($fileUrl);
                $this->_driver->fileClose($fd);
                $this->_driver->createDirectory($baseDir . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR .
                    self::SQM_MC_TEMP_DIR . DIRECTORY_SEPARATOR . $batchId);
                $archive = $this->_archive;
                $archive->unpack(
                    $fileName . '.tar.gz',
                    $baseDir . DIRECTORY_SEPARATOR . 'var' .
                    DIRECTORY_SEPARATOR . self::SQM_MC_TEMP_DIR . DIRECTORY_SEPARATOR . $batchId
                );
                $archive->unpack(
                    $baseDir . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR .
                    self::SQM_MC_TEMP_DIR . DIRECTORY_SEPARATOR . $batchId . '/' . $batchId . '.tar',
                    $baseDir . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR .
                    self::SQM_MC_TEMP_DIR . DIRECTORY_SEPARATOR . $batchId
                );
                $dirFiles = $this->_driver->readDirectory($baseDir . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR .
                    self::SQM_MC_TEMP_DIR . DIRECTORY_SEPARATOR . $batchId);
                foreach ($dirFiles as $dirFile) {
                    $name = pathinfo($dirFile);
                    if ($name['extension'] == 'json') {
                        $files[] = $dirFile;
                    }
                }
                $this->_driver->deleteFile($baseDir . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR .
                    self::SQM_MC_TEMP_DIR . DIRECTORY_SEPARATOR . $batchId . '/' . $batchId . '.tar');
                $this->_driver->deleteFile($fileName . '.tar.gz');
            }
        } catch (\SqualoMailMc_Error $e) {
            $this->_helper->log($e->getFriendlyMessage());
            return false;
        } catch (\Exception $e) {
            $this->_helper->log($e->getMessage());
        }
        return $files;
    }
    protected function processEachResponseFile($files, $batchId, $sqmmcStoreId, $storeId)
    {
        $listId = $this->_helper->getDefaultList($storeId);
        foreach ($files as $file) {
            $items = json_decode($this->_driver->fileGetContents($file));
            if ($items!==false) {
                foreach ($items as $item) {
                    $line = explode('_', $item->operation_id);
                    $type = $line[0];
                    $id = $line[2];
                    if ($item->status_code != 200) {
                        //parse error
                        $response = json_decode($item->response);
                        if (preg_match('/already exists/', $response->detail)) {
                            $this->_updateSyncData(
                                $sqmmcStoreId,
                                $listId,
                                $type,
                                $id,
                                null,
                                \SqualoMail\SqmMcMagentoTwo\Helper\Data::SYNCED
                            );
                            continue;
                        }
                        $errorDetails = "";
                        if (!empty($response->errors)) {
                            foreach ($response->errors as $error) {
                                if (isset($error->field) && isset($error->message)) {
                                    $errorDetails .= $errorDetails != "" ? " / " : "";
                                    $errorDetails .= $error->field . " : " . $error->message;
                                }
                            }
                        }
                        if ($errorDetails == "") {
                            $errorDetails = $response->detail;
                        }

                        $error = $response->title . " : " . $response->detail;
                        $this->_updateSyncData(
                            $sqmmcStoreId,
                            $listId,
                            $type,
                            $id,
                            $error,
                            \SqualoMail\SqmMcMagentoTwo\Helper\Data::SYNCERROR
                        );
                        if (!$this->_helper->getConfigValue(\SqualoMail\SqmMcMagentoTwo\Helper\Data::XML_DISABLE_ERROR_LOG, $storeId)) {
                            $sqmmcErrors = $this->_chimpErrors->create();
                            $sqmmcErrors->setType($response->type);
                            $sqmmcErrors->setTitle($response->title);
                            $sqmmcErrors->setStatus($item->status_code);
                            $sqmmcErrors->setErrors($errorDetails);
                            $sqmmcErrors->setRegtype($type);
                            $sqmmcErrors->setOriginalId($id);
                            $sqmmcErrors->setBatchId($batchId);
                            $sqmmcErrors->setSqmmcStoreId($sqmmcStoreId);
                            $sqmmcErrors->setOriginalId($id);
                            $sqmmcErrors->setBatchId($batchId);
                            $sqmmcErrors->setStoreId($storeId);
                            $sqmmcErrors->getResource()->save($sqmmcErrors);
                        }
                    } else {
                        $this->_updateSyncData(
                            $sqmmcStoreId,
                            $listId,
                            $type,
                            $id,
                            null,
                            \SqualoMail\SqmMcMagentoTwo\Helper\Data::SYNCED
                        );
                    }
                }
            } else {
                switch (json_last_error()) {
                    case JSON_ERROR_DEPTH:
                        $this->_helper->log(' - Maximum stack depth exceeded');
                        break;
                    case JSON_ERROR_CTRL_CHAR:
                        $this->_helper->log(' - Unexpected control character found');
                        break;
                    case JSON_ERROR_SYNTAX:
                        $this->_helper->log(' - Syntax error, malformed JSON');
                        break;
                    case JSON_ERROR_NONE:
                        $this->_helper->log(' - No errors');
                        break;
                }
            }
            $this->_driver->deleteFile($file);
        }
    }
    private function _updateSyncData($sqmmcStoreId, $listId, $type, $id, $error, $status)
    {
        /**
         * @var \SqualoMail\SqmMcMagentoTwo\Model\SqmMcSyncEcommerce $chimpSync
         */
        if ($type == \SqualoMail\SqmMcMagentoTwo\Helper\Data::IS_SUBSCRIBER) {
            $sqmmcStore = $listId;
        } else {
            $sqmmcStore = $sqmmcStoreId;
        }
        $chimpSync = $this->_helper->getChimpSyncEcommerce($sqmmcStore, $id, $type);
        if ($chimpSync->getSqmmcStoreId() ==
            $sqmmcStore && $chimpSync->getType() == $type && $chimpSync->getRelatedId() == $id) {
            $chimpSync->setSqmmcSent($status);
            $chimpSync->setSqmmcSyncError($error);
            $chimpSync->getResource()->save($chimpSync);
        } else {
            $this->_helper->log("Can't find original register for type $type and id $id");
        }
    }
}
