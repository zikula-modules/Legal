{* TODO - Use Zikula.UI to display policies in a pop-up window. *}
{ajaxheader modname=$module filename='Legal.UI.Edit.js' noscriptaculous=true effects=true}
<fieldset>
    <legend>{gt text='Site policies'}</legend>
    <input type="hidden" id="acceptpolicies_csrftoken" name="acceptpolicies_csrftoken" value="{insert name='csrftoken'}" />
    <input type="hidden" id="acceptpolicies_uid" name="acceptedpolicies_uid" value="" />
    {if $activePolicies.termsOfUse}
        {modurl modname=$module type='user' func='termsofuse' assign='policyUrl'}
        {assign var='customUrl' value='Zikula\LegalModule\Constant::MODVAR_TERMS_URL'|constant}
        {assign var='customUrl' value=$modvars.$module.$customUrl}
        {if $customUrl ne ''}
            {assign var='policyUrl' value=$customUrl}
        {/if}
        {gt text='Terms of Use' assign='policyName'}
        {assign var='policyLink' value='<a class="legal_popup" href="%1$s" target="_blank">%2$s</a>'|sprintf:$policyUrl:$policyName}
        <div class="form-group{if isset($fieldErrors.termsofuse) && !empty($fieldErrors.termsofuse)} has-error{/if}">
            <label class="col-lg-3 control-label" for="acceptpolicies_termsofuse">{gt text='Terms of Use'}&nbsp;<span class="required"></span></label>
            <div class="col-lg-9">
                <div class="checkbox">
                    <input type="checkbox" id="acceptpolicies_termsofuse" name="acceptedpolicies_termsofuse"{if $acceptedPolicies.termsOfUse} checked="checked"{/if} value="1" />
                    <em class="help-text">{gt text='Check this box to indicate your acceptance of this site\'s %1$s.' tag1=$policyLink|safehtml}</em>
                </div>
                <p id="acceptpolicies_termsofuse_error" class="alert alert-danger{if !isset($fieldErrors.termsofuse) || empty($fieldErrors.termsofuse)} hidden{/if}">
                    {$fieldErrors.termsofuse|default:''|safetext}
                </p>
            </div>
        </div>
    {/if}
    {if $activePolicies.privacyPolicy}
        {modurl modname=$module type='user' func='privacyPolicy' assign='policyUrl'}
        {assign var='customUrl' value='Zikula\LegalModule\Constant::MODVAR_PRIVACY_URL'|constant}
        {assign var='customUrl' value=$modvars.$module.$customUrl}
        {if $customUrl ne ''}
            {assign var='policyUrl' value=$customUrl}
        {/if}
        {gt text='Privacy Policy' assign='policyName'}
        {assign var='policyLink' value='<a class="legal_popup" href="%1$s" target="_blank">%2$s</a>'|sprintf:$policyUrl:$policyName}
        <div class="form-group{if isset($fieldErrors.privacypolicy) && !empty($fieldErrors.privacypolicy)} has-error{/if}">
            <label class="col-lg-3 control-label" for="acceptpolicies_privacypolicy">{gt text='Privacy Policy'}&nbsp;<span class="required"></span></label>
            <div class="col-lg-9">
                <div class="checkbox">
                    <input type="checkbox" id="acceptpolicies_privacypolicy" name="acceptedpolicies_privacypolicy"{if $acceptedPolicies.privacyPolicy} checked="checked"{/if} value="1" />
                    <em class="help-text">{gt text='Check this box to indicate your acceptance of this site\'s %1$s.' tag1=$policyLink|safehtml}</em>
                </div>
                <p id="acceptpolicies_privacypolicy_error" class="alert alert-danger{if !isset($fieldErrors.privacypolicy) || empty($fieldErrors.privacypolicy)} hidden{/if}">
                    {$fieldErrors.privacypolicy|default:''|safetext}
                </p>
            </div>
        </div>
    {/if}
    {if $activePolicies.agePolicy}
        {modurl modname=$module type='user' func='termsofuse' assign='policyUrl'}
        {assign var='customUrl' value='Zikula\LegalModule\Constant::MODVAR_TERMS_URL'|constant}
        {assign var='customUrl' value=$modvars.$module.$customUrl}
        {if $customUrl ne ''}
            {assign var='policyUrl' value=$customUrl}
        {/if}
        {gt text='Terms of Use' assign='policyName'}
        {assign var='termsOfUseLink' value='<a class="legal_popup" href="%1$s" target="_blank">%2$s</a>'|sprintf:$policyUrl:$policyName}

        {modurl modname=$module type='user' func='privacypolicy' assign='policyUrl'}
        {assign var='customUrl' value='Zikula\LegalModule\Constant::MODVAR_PRIVACY_URL'|constant}
        {assign var='customUrl' value=$modvars.$module.$customUrl}
        {if $customUrl ne ''}
            {assign var='policyUrl' value=$customUrl}
        {/if}
        {gt text='Privacy Policy' assign='policyName'}
        {assign var='privacyPolicyLink' value='<a class="legal_popup" href="%1$s" target="_blank">%2$s</a>'|sprintf:$policyUrl:$policyName}

        <div class="form-group{if isset($fieldErrors.agepolicy) && !empty($fieldErrors.agepolicy)} has-error{/if}">
            <label class="col-lg-3 control-label" for="acceptpolicies_agepolicy">{gt text='Minimum Age'}&nbsp;<span class="required"></span></label>
            <div class="col-lg-9">
                <div class="checkbox">
                    <input type="checkbox" id="acceptpolicies_agepolicy" name="acceptedpolicies_agepolicy"{if $acceptedPolicies.agePolicy} checked="checked"{/if} value="1" />
                    <em class="help-text">{gt text='Check this box to indicate that you are %1$s years of age or older.' tag1=$modvars.$module.minimumAge|safetext}</em>
                </div>
                <div class="alert alert-info">{gt text='Information on our minimum age policy, and on how we handle personally identifiable information can be found in our %1$s and in our %2$s.' tag1=$termsOfUseLink|safehtml tag2=$privacyPolicyLink|safehtml}</div>
                <p id="acceptpolicies_agepolicy_error" class="alert alert-danger{if !isset($fieldErrors.agepolicy) || empty($fieldErrors.agepolicy)} hidden{/if}">
                    {$fieldErrors.agepolicy|default:''|safetext}
                </p>
            </div>
        </div>
    {/if}
    {if $activePolicies.tradeConditions}
        {modurl modname=$module type='user' func='tradeConditions' assign='policyUrl'}
        {assign var='customUrl' value='Zikula\LegalModule\Constant::MODVAR_TRADECONDITIONS_URL'|constant}
        {assign var='customUrl' value=$modvars.$module.$customUrl}
        {if $customUrl ne ''}
            {assign var='policyUrl' value=$customUrl}
        {/if}
        {gt text='General Terms and Conditions of Trade' assign='policyName'}
        {assign var='policyLink' value='<a class="legal_popup" href="%1$s" target="_blank">%2$s</a>'|sprintf:$policyUrl:$policyName}
        <div class="form-group{if isset($fieldErrors.tradeconditions) && !empty($fieldErrors.tradeconditions)} has-error{/if}">
            <label class="col-lg-3 control-label" for="acceptpolicies_tradeconditions">{gt text='General Terms and Conditions of Trade'}&nbsp;<span class="required"></span></label>
            <div class="col-lg-9">
                <div class="checkbox">
                    <input type="checkbox" id="acceptpolicies_tradeconditions" name="acceptedpolicies_tradeconditions"{if $acceptedPolicies.tradeConditions} checked="checked"{/if} value="1" />
                    <em class="help-text">{gt text='Check this box to indicate your acceptance of this site\'s %1$s.' tag1=$policyLink|safehtml}</em>
                </div>
                <p id="acceptpolicies_tradeconditions_error" class="alert alert-danger{if !isset($fieldErrors.tradeconditions) || empty($fieldErrors.tradeconditions)} hidden{/if}">
                    {$fieldErrors.tradeconditions|default:''|safetext}
                </p>
            </div>
        </div>
    {/if}
    {if $activePolicies.cancellationRightPolicy}
        {modurl modname=$module type='user' func='cancellationRightPolicy' assign='policyUrl'}
        {assign var='customUrl' value='Zikula\LegalModule\Constant::MODVAR_CANCELLATIONRIGHTPOLICY_URL'|constant}
        {assign var='customUrl' value=$modvars.$module.$customUrl}
        {if $customUrl ne ''}
            {assign var='policyUrl' value=$customUrl}
        {/if}
        {gt text='Cancellation Right Policy' assign='policyName'}
        {assign var='policyLink' value='<a class="legal_popup" href="%1$s" target="_blank">%2$s</a>'|sprintf:$policyUrl:$policyName}
        <div class="form-group{if isset($fieldErrors.cancellationrightpolicy) && !empty($fieldErrors.cancellationrightpolicy)} has-error{/if}">
            <label class="col-lg-3 control-label" for="acceptpolicies_cancellationrightpolicy">{gt text='Cancellation Right Policy'}&nbsp;<span class="required"></span></label>
            <div class="col-lg-9">
                <div class="checkbox">
                    <input type="checkbox" id="acceptpolicies_cancellationrightpolicy" name="acceptedpolicies_cancellationrightpolicy"{if $acceptedPolicies.cancellationRightPolicy} checked="checked"{/if} value="1" />
                    <em class="help-text">{gt text='Check this box to indicate your acceptance of this site\'s %1$s.' tag1=$policyLink|safehtml}</em>
                </div>
                <p id="acceptpolicies_cancellationrightpolicy_error" class="alert alert-danger{if !isset($fieldErrors.cancellationrightpolicy) || empty($fieldErrors.cancellationrightpolicy)} hidden{/if}">
                    {$fieldErrors.cancellationrightpolicy|default:''|safetext}
                </p>
            </div>
        </div>
    {/if}
</fieldset>