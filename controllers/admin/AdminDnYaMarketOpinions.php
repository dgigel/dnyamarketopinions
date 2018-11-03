<?php

/**
 * @author Daniel Gigel <daniel@gigel.ru>
 * @link http://Daniel.Gigel.ru/
 * Date: 19.10.2016
 * Time: 16:35
 */

use zapalm\prestashopHelpers\helpers\ValidateHelper;

class AdminDnYaMarketOpinionsController extends ModuleAdminController
{
    public function ajaxProcessAddOpinion()
    {
        $order = new Order((int)Tools::getValue('id_order'));

        if (ValidateHelper::isLoadedObject($order)) {
            $opinion = new DnYaMarketOpinion();
            $opinion->id_order = $order->id;
            $opinion->opinion_sent = 1;

            $customer = new Customer($order->id_customer);

            if (DnYaMarketOpinion::checkOpinion($order->id)) {
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
                        '{id_order}'  => (int)$order->id,
                        '{firstname}' => $customer->firstname
                    );

                    $sent = (int)Mail::send(
                        Language::getIdByIso('RU'), //id_lang
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
                    );

                    if (0 === $sent) {
                        exit(json_encode([
                            'result' => 'error',
                            'error'  => 'E-mail не отправлен.',
                        ]));
                    }
                }
            }

            die(Tools::jsonEncode(array(
                'result' => 'ok'
            )));
        }

        exit(json_encode([
            'result' => 'error',
            'error'  => 'Не указан ID заказа.',
        ]));
    }

    public function ajaxProcessAddRule()
    {
        $order = new Order((int)Tools::getValue('id_order'));

        if (ValidateHelper::isLoadedObject($order)) {
            $id_opinion = DnYaMarketOpinion::checkOpinion($order->id);

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
                            '{id_order}' => (int)$order->id,
                            '{firstname}' => $customer->firstname,
                            '{code}' => $rule->code
                        );

                        $sent = (int)Mail::send(
                            Language::getIdByIso('RU'), //id_lang
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
                        );

                        if (0 === $sent) {
                            exit(json_encode([
                                'result' => 'error',
                                'error'  => 'E-mail не отправлен.',
                            ]));
                        }
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

        exit(json_encode([
            'result' => 'error',
            'error'  => 'Не указан ID заказа.',
        ]));
    }
}