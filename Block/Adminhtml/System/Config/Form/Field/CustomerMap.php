<?php
/**
 * mc-magento2 Magento Component
 *
 * @category SqualoMail
 * @package mc-magento2
 * @author Ebizmarts Team <info@ebizmarts.com>
 * @copyright Ebizmarts (http://ebizmarts.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @date: 10/27/17 12:01 PM
 * @file: CustomerMap.php
 */

namespace SqualoMail\SqmMcMagentoTwo\Block\Adminhtml\System\Config\Form\Field;

class CustomerMap extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    /**
     * @var VarsMap
     */
    protected $_varsRenderer=null;
    protected $_sqmmcRenderer=null;

    protected function _getVarsRenderer()
    {
        if (!$this->_varsRenderer) {
            $this->_varsRenderer = $this->getLayout()->createBlock(
                \SqualoMail\SqmMcMagentoTwo\Block\Adminhtml\System\Config\Form\Field\VarsMap::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_varsRenderer->setClass('customer_field_select');
        }
        return $this->_varsRenderer;
    }
    protected function _getSqmmcRenderer()
    {
        if (!$this->_sqmmcRenderer) {
            $this->_sqmmcRenderer = $this->getLayout()->createBlock(
                \SqualoMail\SqmMcMagentoTwo\Block\Adminhtml\System\Config\Form\Field\SqmmcMap::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
            $this->_sqmmcRenderer->setClass('sqmmc_field_select');
        }
        return $this->_sqmmcRenderer;
    }

    protected function _prepareToRender()
    {
//        $this->addColumn('sqmmc', ['label' => __('SqualoMail')]);
        $this->addColumn(
            'sqmmc_field_id',
            ['label' => __('SqualoMail'), 'renderer' => $this->_getSqmmcRenderer()]
        );
        $this->addColumn(
            'customer_field_id',
            ['label' => __('Magento'), 'renderer' => $this->_getVarsRenderer()]
        );
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }
    protected function _prepareArrayRow(\Magento\Framework\DataObject $row)
    {
        $optionExtraAttr = [];
        $optionExtraAttr['option_' . $this->_getVarsRenderer()->calcOptionHash($row->getData('customer_field_id'))] =
            'selected="selected"';
        $optionExtraAttr['option_' . $this->_getSqmmcRenderer()->calcOptionHash(
            $row->getData('sqmmc_field_id')
        )
        ] =
            'selected="selected"';
        $row->setData(
            'option_extra_attrs',
            $optionExtraAttr
        );
    }
}
