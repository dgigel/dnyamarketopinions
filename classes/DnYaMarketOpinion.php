<?php

/**
 * @author Daniel Gigel <daniel@gigel.ru>
 * @link http://Daniel.Gigel.ru/
 * Date: 19.10.2016
 * Time: 13:43
 */
class DnYaMarketOpinion extends ObjectModel
{
    public $id_opinion;
    public $id_order;
    public $id_cart_rule;
    public $date_add;
    public $date_upd;

    public static $definition = array(
        'table' => 'dnyamarketopinions',
        'primary' => 'id_opinion',
        'fields' => array(
            'id_order' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'id_cart_rule' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'),
            'date_add' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_upd' => array('type' => self::TYPE_DATE, 'validate' => 'isDate')
        )
    );

    public static function checkOpinion($id_order)
    {
        return Db::getInstance()->getValue('SELECT `id_opinion` FROM `' . _DB_PREFIX_ . 'dnyamarketopinions` WHERE `id_order`=' . (int)$id_order);
    }

    public static function generateVoucherCode()
    {
        // todo: чем не подходит Tools::passwdGen() ?
        $code = 'YA';
        $symbols = array_merge(range('A', 'Z'), range(0, 9));
        for ($i = 0; $i <= 6; $i++) {
            $code .= $symbols[rand(0, (count($symbols) - 1))];
        }

        // Если вдруг такой код уже есть, генерируем заново
        if (CartRule::getIdByCode($code))
            $code = DnYaMarketOpinion::generateVoucherCode();

        return $code;
    }
}