{route name='zikulalegalmodule_user_legalnotice' assign='policyUrl'}
{assign var='customUrl' value='Zikula\LegalModule\Constant::MODVAR_LEGALNOTICE_URL'|constant}
{assign var='customUrl' value=$modvars.$module.$customUrl}
{if $customUrl ne ''}{assign var='policyUrl' value=$customUrl}{/if}
<a class="legal_inlinelink_legalnotice" href="{$policyUrl|safetext}" target="{$target}">{gt text='Legal notice'}</a>