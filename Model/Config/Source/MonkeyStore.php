<?php
/**
 * mc-magento2 Magento Component
 *
 * @category SqualoMail
 * @package mc-magento2
 * @author Ebizmarts Team <info@ebizmarts.com>
 * @copyright Ebizmarts (http://ebizmarts.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @date: 3/28/17 10:57 AM
 * @file: MonkeyStore.php
 */

namespace SqualoMail\SqmMcMagentoTwo\Model\Config\Source;

class MonkeyStore implements \Magento\Framework\Option\ArrayInterface
{
    private $options = null;

    /**
     * MonkeyStore constructor.
     * @param \SqualoMail\SqmMcMagentoTwo\Helper\Data $helper
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \SqualoMail\SqmMcMagentoTwo\Helper\Data $helper,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $storeId = (int) $request->getParam("store", 0);
        if ($request->getParam('website', 0)) {
            $scope = 'website';
            $storeId = $request->getParam('website', 0);
        } elseif ($request->getParam('store', 0)) {
            $scope = 'stores';
            $storeId = $request->getParam('store', 0);
        } else {
            $scope = 'default';
        }
        if ($helper->getApiKey($storeId, $scope)) {
            try {
                $this->options = $helper->getApi($storeId, $scope)->ecommerce->stores->get(
                    null,
                    null,
                    null,
                    \SqualoMail\SqmMcMagentoTwo\Helper\Data::MAXSTORES
                );
            } catch (\SqualoMailMc_Error $e) {
                $helper->log($e->getFriendlyMessage());
            }
        }
    }
    public function toOptionArray()
    {
        if (is_array($this->options)) {
            $rc = [];
            $rc[] = ['value' => -1, 'label' => 'Select one SqualoMail Store'];
            foreach ($this->options['stores'] as $store) {
                if ($store['platform'] == \SqualoMail\SqmMcMagentoTwo\Helper\Data::PLATFORM) {
                    if ($store['list_id']=='') {
                        continue;
                    }
                    if (isset($store['connected_site'])) {
                        $label = $store['name'];
                    } else {
                        $label = $store['name'].' (Warning: not connected)';
                    }

                    $rc[] = ['value'=> $store['id'], 'label' => $label];
                }
            }
        } else {
            $rc[] = ['value' => 0, 'label' => __('---No Data---')];
        }
        return $rc;
    }
    public function toArray()
    {
        $rc = [];
        foreach ($this->options['stores'] as $store) {
            $rc[$store['id']] = $store['name'];
        }
        return $rc;
    }
}
