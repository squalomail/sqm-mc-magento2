<?php
/**
 * mc-magento2 Magento Component
 *
 * @category SqualoMail
 * @package mc-magento2
 * @author Ebizmarts Team <info@ebizmarts.com>
 * @copyright Ebizmarts (http://ebizmarts.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @date: 4/17/17 12:03 PM
 * @file: Delete.php
 */

namespace SqualoMail\SqmMcMagentoTwo\Controller\Adminhtml\Stores;

class Delete extends \SqualoMail\SqmMcMagentoTwo\Controller\Adminhtml\Stores
{
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $storeId = (int) $this->getRequest()->getParam('id');
        if ($storeId) {
            $storeModel = $this->_mailchimpStoresFactory->create();
            $storeModel->getResource()->load($storeModel, $storeId);
            try {
                $api = $this->_helper->getApiByApiKey($storeModel->getApikey(), true);
                $api->ecommerce->stores->delete($storeModel->getStoreid());
                $this->messageManager->addSuccess(__('You deleted the store.'));
                return $resultRedirect->setPath('mailchimp/stores');
            } catch (\SqualoMailMc_Error $e) {
                $this->messageManager->addError(__('Store could not be deleted.'.$e->getMessage()));
                $this->_helper->log($e->getFriendlyMessage());
                return $resultRedirect->setPath('mailchimp/stores/edit', ['id'=>$storeId]);
            }
        }
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('SqualoMail_SqmMcMagentoTwo::stores_edit');
    }
}
