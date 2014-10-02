{route name='zikulalegalmodule_user_privacypolicy' assign='policyUrl'}
{assign var='customUrl' value='Zikula\LegalModule\Constant::MODVAR_PRIVACY_URL'|constant}
{assign var='customUrl' value=$modvars.$module.$customUrl}
{if $customUrl ne ''}{assign var='policyUrl' value=$customUrl}{/if}
<a class="legal_inlinelink_privacypolicy" href="{$policyUrl|safetext}" target="{$target}">{gt text='Privacy Policy'}</a>