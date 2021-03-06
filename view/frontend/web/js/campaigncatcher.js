/**
 * SqualoMail_SqmMcMagentoTwo Magento JS component
 *
 * @category    SqualoMail
 * @package     SqualoMail_SqmMcMagentoTwo
 * @author      Ebizmarts Team <info@ebizmarts.com>
 * @copyright   Ebizmarts (http://ebizmarts.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'mage/cookies'
    ],
    function ($) {
        "use strict";

        $.widget('mage.campaigncatcher', {
            "options": {
                "checkCampaignUrl": ""
            },

            _init: function () {
                var self = this;
                $(document).ready(function () {
                    var path = location;
                    var urlparams = null;
                    var isGet = path.search.search('\\?');
                    var mc_cid = null;
                    var isSqmmc = false;
                    var checkCampaignUrl = self.options.checkCampaignUrl;
                    if(isGet != -1) {
                        urlparams = self.getUrlVars();
                        urlparams.forEach(function (item) {
                            if (item.key=='utm_source') {
                                var reg = /^squalomail$/;
                                if (reg.exec(item.value)) {
                                    isSqmmc = true;
                                }
                            } else {
                                if (item.key=='mc_cid') {
                                    mc_cid = item.value;
                                }
                            }
                        });
                    } else {
                        urlparams = path.href.split('/');
                        var utmIndex = $.inArray('utm_source', urlparams);
                        var mccidIndex = $.inArray('mc_cid', urlparams);
                        if (utmIndex != -1) {
                            var value = urlparams[utmIndex + 1];
                            var reg = /^squalomail$/;
                            if (reg.exec(value)) {
                                isSqmmc = true;
                            }
                        } else {
                            if (mccidIndex != -1) {
                                mc_cid = urlparams[mccidIndex + 1];
                            }
                        }
                    }
                    if (mc_cid && !isSqmmc) {
                        $.ajax({
                            url: checkCampaignUrl + 'mc_cid/' + mc_cid + '/',
                            type: 'GET',
                            dataType: 'json',
                            cache: true,
                            showLoader: false
                        }).done(function (data) {
                            if (data.valid==0) {
                                $.mage.cookies.clear('sqmmc_campaign_id');
                                $.mage.cookies.set('sqmmc_landing_page', location);
                            } else if (data.valid==1) {
                                $.mage.cookies.set('sqmmc_campaign_id' , mc_cid);
                                $.mage.cookies.set('sqmmc_landing_page', location);
                            }
                        });
                    }

                    if (isSqmmc) {
                        $.mage.cookies.clear('sqmmc_campaign_id');
                        $.mage.cookies.set('sqmmc_landing_page', location);
                    }
                });
            },
            getUrlVars: function() {
                var vars = [];
                var i = 0;
                var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
                    vars[i] = {'value':value,'key':key};
                    i++;
                });
                return vars;
            }
        });
        return $.mage.campaigncatcher;
    }
);
