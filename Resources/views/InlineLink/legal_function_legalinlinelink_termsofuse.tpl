{route name='zikulalegalmodule_user_termsofuse' assign='policyUrl'}
{assign var='customUrl' value='Zikula\LegalModule\Constant::MODVAR_TERMS_URL'|constant}
{assign var='customUrl' value=$modvars.$module.$customUrl}
{if $customUrl ne ''}{assign var='policyUrl' value=$customUrl}{/if}
<a class="legal_inlinelink_termsofuse" href="{$policyUrl|safetext}" target="{$target}">{gt text='Terms of Use'}</a>