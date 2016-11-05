<br/>
<fieldset>
    <legend>
        <img src="../img/admin/comment.gif">Попросить отзыв на <span style="color:red;">Я</span>ндекс.Маркет
    </legend>
    <p id="dnyamarketopinions_opinion_sent"><img class="dnyamarketopinions_flag" src="../img/admin/{if !$opinion->id}disabled{else}enabled{/if}.gif"> Просьба отправлена</p>
    <p id="dnyamarketopinions_rule_sent"><img class="dnyamarketopinions_flag" src="../img/admin/{if !$opinion->id_cart_rule}disabled{else}enabled{/if}.gif"> Купон отправлен <b><span id="dnyamarketopinions_cart_rule_code">{$rule->code}</span></b></p>
    <br>
    <p>
        {if !$opinion->id}<a id="submitAddOpinion" class="button" href="#"><img src="../img/admin/comment_edit.png">Попросить отзыв</a>{/if}
        {if !$opinion->id_cart_rule}<a id="submitAddRule" class="button" href="#"><img src="../img/admin/coupon.gif">Оправить купон</a>{/if}
    </p>
</fieldset>