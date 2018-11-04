<?php
/**
 * DnYaMarketOpinions: PrestaShop module main file.
 *
 * @author Daniel Gigel <daniel@gigel.ru>
 * @author zapalm <zapalm@ya.ru>
 *
 * @link   http://Daniel.Gigel.ru/
 *
 * Date: 19.10.2016
 * Time: 13:41
 */

if (!defined('_PS_VERSION_'))
    exit;

require_once _PS_MODULE_DIR_ . 'dnyamarketopinions/vendor/autoload.php';

class DnYaMarketOpinions extends Module
{
    public function __construct()
    {
        $this->name = 'dnyamarketopinions';
        $this->tab = 'emailing';
        $this->version = '0.1';
        $this->author = 'Daniel.Gigel.ru';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.5.0.0', 'max' => '1.5.6.3');
        $this->secure_key = Tools::encrypt($this->name);

        parent::__construct();

        $this->displayName = 'Просьба оставить отзыв на Яндекс.Маркет';
        $this->description = 'Отправляет e-mail письма клиенту с просьбой оставить отзыв на Яндекс.Маркет за вознаграждение в виде купона.';
    }

    /**
     * @inheritdoc
     */
    public function install()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'dnyamarketopinions` (
			`id_opinion` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
			`id_order` INT UNSIGNED NOT NULL,
			`id_cart_rule` INT UNSIGNED NULL,
			`date_add` DATETIME NOT NULL,
	        `date_upd` DATETIME NOT NULL
			) ENGINE=' . _MYSQL_ENGINE_
        ;

        if (!Db::getInstance()->execute($sql)) {
            return false;
        }

        if (!parent::install()) {
            return false;
        }

        if (false === $this->installTabs()) {
            return false;
        }

        return $this->registerHook('displayAdminOrder')
            && $this->registerHook('displayBackOfficeFooter')
            && $this->registerHook('actionAdminControllerSetMedia')
        ;
    }

    /**
     * @inheritdoc
     */
    public function uninstall()
    {
        $sql = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'dnyamarketopinions`';
        if (!Db::getInstance()->execute($sql)) {
            return false;
        }

        if (false === \zapalm\prestashopHelpers\helpers\ModuleHelper::uninstallTabs($this->name)) {
            return false;
        }

        return parent::uninstall();
    }

    /**
     * @inheritdoc
     *
     * @author Maksim T. <zapalm@yandex.com>
     */
    public function hookActionAdminControllerSetMedia()
    {
        $this->context->controller->addJquery();
        $this->context->controller->addJS($this->_path . 'js/dnyamarketopinions.js');
    }

    /**
     * @inheritdoc
     *
     * @author Daniel Gigel <daniel@gigel.ru>
     * @author Maksim T. <zapalm@yandex.com>
     */
    public function hookDisplayBackOfficeFooter()
    {
        return '
            <script type="text/javascript">
                var urlDnYaMarketOpinions = "' . $this->context->link->getAdminLink('AdminDnYaMarketOpinions') . '";
                var tokenDnYaMarketOpinions = "' . Tools::getAdminTokenLite('AdminDnYaMarketOpinions') . '";
            </script>
        ';
    }

    /**
     * Установить табы.
     *
     * @return bool
     *
     * @author Maksim T. <zapalm@yandex.com>
     */
    public function installTabs() {
        $tab = \zapalm\prestashopHelpers\helpers\BackendHelper::installTab(
            $this->name,
            'AdminDnYaMarketOpinions',
            \zapalm\prestashopHelpers\helpers\BackendHelper::TAB_PARENT_ID_UNLINKED
        );

        if (false === \zapalm\prestashopHelpers\helpers\ValidateHelper::isLoadedObject($tab)) {
            return false;
        }

        return true;
    }

    /**
     * @param array $params
     *
     * @return string
     */
    public function hookDisplayAdminOrder($params)
    {
        $opinion = new DnYaMarketOpinion();
        $rule    = new CartRule();

        $idOrder = (int)$params['id_order'];
        $id_opinion = DnYaMarketOpinion::checkOpinion($idOrder);
        if ($id_opinion) {
            $opinion = new DnYaMarketOpinion($id_opinion);
            if ($opinion->id_cart_rule) {
                $rule = new CartRule($opinion->id_cart_rule);
            }
        }

        $this->smarty->assign(array(
            'opinion'  => $opinion,
            'rule'     => $rule
        ));

        return $this->display(__FILE__, 'displayAdminOrder.tpl');
    }
}