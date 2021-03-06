<?php
/**
 * mc-magento2 Magento Component
 *
 * @category SqualoMail
 * @package mc-magento2
 * @author Ebizmarts Team <info@ebizmarts.com>
 * @copyright Ebizmarts (http://ebizmarts.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @date: 3/14/17 11:26 PM
 * @file: SaveBefore.php
 */
namespace SqualoMail\SqmMcMagentoTwo\Observer\Sales\Order;

use Magento\Framework\Event\Observer;

class SubmitBefore implements \Magento\Framework\Event\ObserverInterface
{
    private $attributes = [
        'sqmmc_abandonedcart_flag',
        'sqmmc_campaign_id',
        'sqmmc_landing_page'
    ];
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /* @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getData('order');
        /* @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getEvent()->getData('quote');
        $flag = 0;

        foreach ($this->attributes as $attribute) {
            if ($quote->hasData($attribute)) {
                $order->setData($attribute, $quote->getData($attribute));
                if ($quote->getData($attribute)) {
                    $flag = 1;
                }
            }
        }
        $order->setData('sqmmc_flag', $flag);
        return $this;
    }
}
