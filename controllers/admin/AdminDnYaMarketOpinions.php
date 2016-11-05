<?php

/**
 * @author Daniel Gigel <daniel@gigel.ru>
 * @link http://Daniel.Gigel.ru/
 * Date: 19.10.2016
 * Time: 16:35
 */
class AdminDnYaMarketOpinionsController extends ModuleAdminController
{
    public function ajaxProcessAddOpinion()
    {
        if ($id_order = Tools::getValue('id_order')) { //todo: если id_order не задан, то вернет пусто, хотя ожидается ajax-ответ
            $opinion = new DnYaMarketOpinion();
            $opinion->id_order = (int)$id_order;
            $opinion->opinion_sent = 1;

            $order = new Order((int)$id_order);
            $customer = new Customer($order->id_customer);

            if (DnYaMarketOpinion::checkOpinion((int)$id_order)) {
                die(Tools::jsonEncode(array(
                    'result' => 'error',
                    'error' => Tools::displayError('Просьба уже была отправлена.')
                )));
            }

            if (!$opinion->add()) {
                die(Tools::jsonEncode(array(
                    'result' => 'error',
                    'error' => Tools::displayError('Ошибка создания просьбы.')
                )));
            } else {
                if (Validate::isLoadedObject($customer) && Validate::isLoadedObject($order)) {

                    $mailVars = array(
                        '{id_order}' => (int)$id_order,
                        '{firstname}' => $customer->firstname
                    );

                    @Mail::send(
                        Configuration::get('PS_LANG_DEFAULT'), //id_lang
                        'thanks', //template
                        'Дарим бонус за отзыв на ЯндексМаркет', //subject
                        $mailVars, //template_vars
                        $customer->email, //to
                        null, //to name
                        null, //from
                        null, //from name
                        null, //file_attachment
                        null, //mode smtp
                        _PS_MODULE_DIR_ . 'dnyamarketopinions/mails/' //template path
                    ); // todo: нет проверки возвращаемого значения; ухо часто - зло
                }
            }

            die(Tools::jsonEncode(array(
                'result' => 'ok'
            )));
        }
    }

    public function ajaxProcessAddRule()
    {
        if ($id_order = Tools::getValue('id_order')) { //todo: если id_order не задан, то вернет пусто, хотя ожидается ajax-ответ
            $id_opinion = DnYaMarketOpinion::checkOpinion((int)$id_order);

            if ($id_opinion) {
                $opinion = new DnYaMarketOpinion($id_opinion);

                $rule = new CartRule();
                $rule->name = array(Configuration::get('PS_LANG_DEFAULT') => 'Скидочный купон за отзыв на Яндекс.Маркет');
                $rule->code = DnYaMarketOpinion::generateVoucherCode();
                $rule->date_from = date('Y-m-d H:i:s');
                $rule->date_to = date("Y-m-d H:i:s", strtotime('next year'));
                $rule->quantity = 1;
                $rule->quantity_per_user = 1;
                $rule->minimum_amount_currency = 1;
                $rule->partial_use = 0;
                $rule->product_restriction = 0;
                $rule->reduction_percent = 0;
                $rule->gift_product = 0;
                $rule->gift_product_attribute = 0;
                $rule->reduction_amount = 300;
                $rule->reduction_currency = Configuration::get('PS_CURRENCY_DEFAULT');
                $rule->highlight = 0;
                $rule->active = 1;

                $order = new Order((int)$id_order);
                $customer = new Customer($order->id_customer);

                if (!$rule->add()) {
                    die(Tools::jsonEncode(array(
                        'result' => 'error',
                        'error' => Tools::displayError('Ошибка создания правила корзины.')
                    )));
                }

                $opinion->id_cart_rule = $rule->id;

                if (!$opinion->update()) {
                    die(Tools::jsonEncode(array(
                        'result' => 'error',
                        'error' => Tools::displayError('Ошибка обновления просьбы.')
                    )));
                } else {
                    if (Validate::isLoadedObject($customer) && Validate::isLoadedObject($order)) {

                        $mailVars = array(
                            '{id_order}' => (int)$id_order,
                            '{firstname}' => $customer->firstname,
                            '{code}' => $rule->code
                        );

                        @Mail::send(
                            Configuration::get('PS_LANG_DEFAULT'), //id_lang
                            'promokod', //template
                            'Скидочный купон', //subject
                            $mailVars, //template_vars
                            $customer->email, //to
                            null, //to name
                            null, //from
                            null, //from name
                            null, //file_attachment
                            null, //mode smtp
                            _PS_MODULE_DIR_ . 'dnyamarketopinions/mails/' //template path
                        ); // todo: нет проверки возвращаемого значения; ухо часто - зло

                    }
                }

                die(Tools::jsonEncode(array(
                    'result' => 'ok',
                    'voucher_code' => $rule->code
                )));
            } else {
                die(Tools::jsonEncode(array(
                    'result' => 'error',
                    'error' => Tools::displayError('Сначала нужно отправить просьбу.')
                )));
            }
        }
    }
}