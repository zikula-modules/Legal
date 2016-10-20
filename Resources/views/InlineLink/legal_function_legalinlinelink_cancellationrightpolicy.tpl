{route name='zikulalegalmodule_user_cancellationrightpolicy' assign='policyUrl'}
{assign var='customUrl' value='Zikula\LegalModule\Constant::MODVAR_CANCELLATIONRIGHTPOLICY_URL'|constant}
{assign var='customUrl' value=$modvars.$module.$customUrl}
{if $customUrl ne ''}{assign var='policyUrl' value=$customUrl}{/if}
<a class="legal_inlinelink_cancellationrightpolicy" href="{$policyUrl|safetext}" target="{$target}">{gt text='Cancellation Right Policy'}</a>