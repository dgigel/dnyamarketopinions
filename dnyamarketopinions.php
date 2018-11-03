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
        $this->ps_versions_compliancy = array('min' => '1.5', 'max' => '1.6');
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

        if (!$this->installModuleTab('AdminDnYaMarketOpinions', 'AdminDnYaMarketOpinions', -1)) { // todo: вторым параметром должен передаваться title, а не системное наименовение таба
            return false;
        }

        return $this->registerHook('displayAdminOrder')
            && $this->registerHook('BackOfficeHeader')
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

        if (!$this->uninstallModuleTab('AdminDnYaMarketOpinions')) {
            return false;
        }

        return parent::uninstall();
    }

    public function hookDisplayBackOfficeHeader()
    {
        //хз почему его добавляет перед jquery
        //$this->context->controller->addJS(($this->_path) . 'js/dnyamarketopinions.js');
        //todo: попробовать сделать вызов до собственных js: $this->context->controller->addJquery();

        return '
			<script type="text/javascript">
				var urlDnYaMarketOpinions = "' . $this->context->link->getAdminLink('AdminDnYaMarketOpinions') . '";
				var tokenDnYaMarketOpinions = "' . Tools::getAdminTokenLite('AdminDnYaMarketOpinions') . '";
			</script>
			<script type="text/javascript" src="' . ($this->_path) . 'js/dnyamarketopinions.js"></script>';
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
            'id_order' => $idOrder, // todo: не используется
            'opinion'  => $opinion,
            'rule'     => $rule
        ));

        return $this->display(__FILE__, 'displayAdminOrder.tpl');
    }

    private function installModuleTab($tab_class, $tab_name, $id_tab_parent)
    {
        $tab = new Tab();
        $tab->class_name = $tab_class;
        $tab->module = $this->name;
        $tab->id_parent = $id_tab_parent;

        $languages = Language::getLanguages();
        foreach ($languages as $lang)
            $tab->name[$lang['id_lang']] = $this->l($tab_name); // todo: должен передаваться title, а не системное наименовение таба

        return $tab->save();
    }

    /**
     * @param string $tab_class
     *
     * @return bool
     */
    private function uninstallModuleTab($tab_class)
    {
        $idTab = Tab::getIdFromClassName($tab_class);
        if ($idTab !== false) {
            $tab = new Tab($idTab);

            return $tab->delete();
        }

        return true; // Т.к. уже удален или несуществует
    }
}