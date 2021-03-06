<?php
/**
 * mc-magento2 Magento Component
 *
 * @category SqualoMail
 * @package mc-magento2
 * @author Ebizmarts Team <info@ebizmarts.com>
 * @copyright Ebizmarts (http://ebizmarts.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @date: 4/17/17 11:05 AM
 * @file: Save.php
 */

namespace SqualoMail\SqmMcMagentoTwo\Controller\Adminhtml\Stores;

class Save extends \SqualoMail\SqmMcMagentoTwo\Controller\Adminhtml\Stores
{
    public function execute()
    {
        $isPost = $this->getRequest()->getPost();
        if ($isPost) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $returnToEdit = (bool)$this->getRequest()->getParam('back', false);
            $storeModel = $this->_sqmmcStoresFactory->create();
            $formData = $this->getRequest()->getParam('stores');
            $storeId = isset($formData['id']) ? $formData['id'] : null;
            if ($storeId) {
                $storeModel->getResource()->load($storeModel, $storeId);
            }
            try {
                $formData['storeid'] = $this->_updateSqmmc($formData);
                $formData['platform'] = \SqualoMail\SqmMcMagentoTwo\Helper\Data::PLATFORM;
                $storeModel->setData($formData);
                $storeModel->getResource()->save($storeModel);
                if ($returnToEdit) {
                    if (!$storeId) {
                        $storeId = $storeModel->getId();
                    }
                    return $resultRedirect->setPath('sqmmc/stores/edit', ['id'=>$storeId]);
                } else {
                    return $resultRedirect->setPath('sqmmc/stores');
                }
            } catch (\SqualoMailMc_Error $e) {
                $this->messageManager->addErrorMessage(__('Store could not be saved.'.$e->getMessage()));
                $this->_helper->log($e->getFriendlyMessage());
                return $resultRedirect->setPath('sqmmc/stores/edit', ['id'=>$storeId]);
            }
        }
    }
    protected function _updateSqmmc($formData)
    {
        $api = $this->_helper->getApiByApiKey($formData['apikey'], true);
        // set the address
        $address = [];
        $address['address1']    = $formData['address_address_one'];
        $address['address2']    = $formData['address_address_two'];
        $address['city']        = $formData['address_city'];
        $address['province']    = '';
        $address['province_code'] = '';
        $address['postal_code'] = $formData['address_postal_code'];
        $address['country']     = '';
        $address['country_code'] = $formData['address_country_code'];
        $emailAddress   = $formData['email_address'];
        $currencyCode   = $formData['currency_code'];
        $primaryLocale  = $formData['primary_locale'];
        $timeZone       = $formData['timezone'];
        $phone          = $formData['phone'];
        $name           = $formData['name'];
        $domain         = $formData['domain'];
        $storeId = isset($formData['storeid']) ? $formData['storeid'] : null;
        $is_sync = null;

        if ($storeId) {
            $api->ecommerce->stores->edit(
                $storeId,
                \SqualoMail\SqmMcMagentoTwo\Helper\Data::PLATFORM,
                $domain,
                $name,
                $emailAddress,
                $currencyCode,
                null,
                $primaryLocale,
                $timeZone,
                $phone,
                $address,
                $is_sync
            );
        } else {
            $date = $this->_helper->getDateMicrotime();
            $sqmmcStoreId = hash('md5', $name. '_' . $date);
            //$sqmmcStoreId = md5($name. '_' . $date);
            $is_sync = true;
            $ret =$api->ecommerce->stores->add(
                $sqmmcStoreId,
                $formData['list_id'],
                $name,
                $currencyCode,
                \SqualoMail\SqmMcMagentoTwo\Helper\Data::PLATFORM,
                $domain,
                $emailAddress,
                null,
                $primaryLocale,
                $timeZone,
                $phone,
                $address,
                $is_sync
            );
            $formData['storeid'] = $sqmmcStoreId;
        }
        return $formData['storeid'];
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('SqualoMail_SqmMcMagentoTwo::stores_edit');
    }
}
