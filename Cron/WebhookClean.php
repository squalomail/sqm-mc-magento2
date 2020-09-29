<?php
/**
 * MailChimp Magento Component
 *
 * @category SqualoMail
 * @package MailChimp
 * @author Ebizmarts Team <info@ebizmarts.com>
 * @copyright Ebizmarts (http://ebizmarts.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @date: 22/11/18 10:02 AM
 * @file: WebhookClean.php
 */
namespace SqualoMail\SqmMcMagentoTwo\Cron;

class WebhookClean
{
    /**
     * @var \SqualoMail\SqmMcMagentoTwo\Helper\Data
     */
    protected $helper;
    /**
     * @var \SqualoMail\SqmMcMagentoTwo\Model\MailChimpWebhookRequest
     */
    protected $webhooks;

    /**
     * WebhookClean constructor.
     * @param \SqualoMail\SqmMcMagentoTwo\Helper\Data $helper
     * @param \SqualoMail\SqmMcMagentoTwo\Model\MailChimpWebhookRequest $webhookRequest
     */
    public function __construct(
        \SqualoMail\SqmMcMagentoTwo\Helper\Data $helper,
        \SqualoMail\SqmMcMagentoTwo\Model\MailChimpWebhookRequest $webhookRequest
    ) {
        $this->helper   = $helper;
        $this->webhooks = $webhookRequest;
    }
    public function execute()
    {
        try {
            $connection = $this->webhooks->getResource()->getConnection();
            $tableName = $this->webhooks->getResource()->getMainTable();
            $quoteInto = $connection->quoteInto('processed = ? and date_add(fired_at, interval 1 month) < now()', 1);
            $connection->delete($tableName, $quoteInto);
        } catch (\Exception $e) {
            $this->helper->log($e->getMessage());
        }
    }
}
