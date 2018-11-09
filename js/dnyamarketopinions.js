/**
 * DnYaMarketOpinion: модуль для PrestaShop.
 *
 * @author    Daniel Gigel <daniel@gigel.ru>
 * @author    Maksim T. <zapalm@yandex.com>
 * @copyright 2016
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://Daniel.Gigel.ru/
 * @link      https://prestashop.modulez.ru/en/ Модули для PrestaShop CMS
 */

$(document).ready(function () {
    $('#submitAddOpinion').on('click', function (e) {
        console.log(e);
        e.preventDefault();
        stopAjaxQuery();

        var query = 'ajax=1&action=addOpinion&token=' + tokenDnYaMarketOpinions + '&id_order=' + id_order;

        var ajax_query = $.ajax({
            type: 'POST',
            url: urlDnYaMarketOpinions,
            cache: false,
            dataType: 'json',
            data: query,
            success: function (data) {
                if (data.result && data.result == 'ok') {
                    $('#submitAddOpinion').fadeOut();
                    $('#dnyamarketopinions_opinion_sent').find('img.dnyamarketopinions_flag').attr('src', '../img/admin/enabled.gif');
                }
                else
                    jAlert(data.error);
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                jAlert("Ошибка создания просьбы.\n\ntextStatus: '" + textStatus + "'\nerrorThrown: '" + errorThrown + "'\nresponseText:\n" + XMLHttpRequest.responseText);
            }
        });
        ajaxQueries.push(ajax_query);
    });

    $('#submitAddRule').on('click', function (e) {
        console.log(e);
        e.preventDefault();
        stopAjaxQuery();

        var query = 'ajax=1&action=addRule&token=' + tokenDnYaMarketOpinions + '&id_order=' + id_order;

        var ajax_query = $.ajax({
            type: 'POST',
            url: urlDnYaMarketOpinions,
            cache: false,
            dataType: 'json',
            data: query,
            success: function (data) {
                if (data.result && data.result == 'ok') {
                    $('#submitAddRule').fadeOut();
                    $('#dnyamarketopinions_rule_sent').find('img.dnyamarketopinions_flag').attr('src', '../img/admin/enabled.gif');
                    $('#dnyamarketopinions_cart_rule_code').text(data.voucher_code);
                }
                else
                    jAlert(data.error);
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                jAlert("Ошибка создания купона.\n\ntextStatus: '" + textStatus + "'\nerrorThrown: '" + errorThrown + "'\nresponseText:\n" + XMLHttpRequest.responseText);
            }
        });
        ajaxQueries.push(ajax_query);
    });
});
