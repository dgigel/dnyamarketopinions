{**
 * DnYaMarketOpinion: модуль для PrestaShop.
 *
 * @author    Daniel Gigel <daniel@gigel.ru>
 * @author    Maksim T. <zapalm@yandex.com>
 * @copyright 2016
 * @license   https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      http://Daniel.Gigel.ru/
 * @link      https://prestashop.modulez.ru/en/ Модули для PrestaShop CMS
 *}

<br/>
<fieldset>
    <legend>
        <img src="../img/admin/comment.gif">Попросить отзыв на <span style="color:red;">Я</span>ндекс.Маркет
    </legend>
    <p id="dnyamarketopinions_opinion_sent">
        <img class="dnyamarketopinions_flag" src="../img/admin/{if !$opinion->id}disabled{else}enabled{/if}.gif">
        Просьба отправлена
    </p>
    <p id="dnyamarketopinions_rule_sent">
        <img class="dnyamarketopinions_flag" src="../img/admin/{if !$opinion->id_cart_rule}disabled{else}enabled{/if}.gif">
        Купон отправлен
        <b><span id="dnyamarketopinions_cart_rule_code">{$rule->code}</span></b>
    </p>
    <br>
    <p>
        {if !$opinion->id}
            <a id="submitAddOpinion" class="button" href="#">
                <img src="../img/admin/comment_edit.png">
                Попросить отзыв
            </a>
        {/if}
        {if !$opinion->id_cart_rule}
            <a id="submitAddRule" class="button" href="#">
                <img src="../img/admin/coupon.gif">
                Оправить купон
            </a>
        {/if}
    </p>
</fieldset>