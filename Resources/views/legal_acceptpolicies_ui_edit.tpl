{if (is_numeric($policiesUid) && ($policiesUid > 2) || ($policiesUid == ''))}
<fieldset>
    <legend>{gt text='Site policies'}</legend>
    <input type="hidden" id="acceptpolicies_csrftoken" name="acceptpolicies_csrftoken" value="{insert name='csrftoken'}" />
    <input type="hidden" id="acceptpolicies_uid" name="acceptedpolicies_uid" value="{$policiesUid}" />
    {if $activePolicies.termsOfUse && $viewablePolicies.termsOfUse}
        {route name='zikulalegalmodule_user_termsofuse' assign='policyUrl'}
        {assign var='customUrl' value='Zikula\LegalModule\Constant::MODVAR_TERMS_URL'|constant}
        {assign var='customUrl' value=$modvars.$module.$customUrl}
        {if $customUrl ne ''}
            {assign var='policyUrl' value=$customUrl}
        {/if}
        {gt text='Terms of Use' assign='policyName'}
        {assign var='policyLink' value='<a class="legal_popup" href="%1$s" target="_blank">%2$s</a>'|sprintf:$policyUrl:$policyName}
        <div class="form-group{if isset($fieldErrors.termsofuse) && !empty($fieldErrors.termsofuse)} has-error{/if}">
            <label class="col-lg-3 control-label">{gt text='Terms of Use'}</label>
            {if ($editablePolicies.termsOfUse)}
            <div class="col-lg-9">
                <div class="radio">
                    <input type="radio" id="acceptpolicies_termsofuse_yes" name="acceptedpolicies_termsofuse"{if $acceptedPolicies.termsOfUse} checked="checked"{/if} value="1" />
                    <label for="acceptpolicies_termsofuse_yes">{gt text='%1$s accepted.' tag1=$policyLink|safehtml}</label>
                </div>
                <div class="radio">
                    <input type="radio" id="acceptpolicies_termsofuse_no" name="acceptedpolicies_termsofuse"{if !$acceptedPolicies.termsOfUse} checked="checked"{/if} value="0" />
                    <label for="acceptpolicies_termsofuse_no">{gt text='Policy not accepted.'}</label>
                </div>
                <p id="acceptpolicies_termsofuse_error" class="alert alert-danger{if !isset($fieldErrors.termsofuse) || empty($fieldErrors.termsofuse)} hidden{/if}">
                    {$fieldErrors.termsofuse|default:''|safetext}
                </p>
            </div>
            {else}
            <div class="col-lg-9">
                <span class="form-control-static">{if $acceptedPolicies.termsOfUse}{gt text='Accepted.'}{else}{gt text='Not accepted.'}{/if}</span>
            </div>
            {/if}
        </div>
    {/if}
    {if $activePolicies.privacyPolicy && $viewablePolicies.privacyPolicy}
        {route name='zikulalegalmodule_user_privacypolicy' assign='policyUrl'}
        {assign var='customUrl' value='Zikula\LegalModule\Constant::MODVAR_PRIVACY_URL'|constant}
        {assign var='customUrl' value=$modvars.$module.$customUrl}
        {if $customUrl ne ''}
            {assign var='policyUrl' value=$customUrl}
        {/if}
        {gt text='Privacy Policy' assign='policyName'}
        {assign var='policyLink' value='<a class="legal_popup" href="%1$s" target="_blank">%2$s</a>'|sprintf:$policyUrl:$policyName}
        <div class="form-group{if isset($fieldErrors.privacypolicy) && !empty($fieldErrors.privacypolicy)} has-error{/if}">
            <label class="col-lg-3 control-label">{gt text='Privacy Policy'}</label>
            {if ($editablePolicies.privacyPolicy)}
            <div class="col-lg-9">
                <div class="radio">
                    <input type="radio" id="acceptpolicies_privacypolicy_yes" name="acceptedpolicies_privacypolicy"{if $acceptedPolicies.privacyPolicy} checked="checked"{/if} value="1" />
                    <label for="acceptpolicies_privacypolicy_yes">{gt text='%1$s accepted.' tag1=$policyLink|safehtml}</label>
                </div>
                <div class="radio">
                    <input type="radio" id="acceptpolicies_privacypolicy_no" name="acceptedpolicies_privacypolicy"{if !$acceptedPolicies.privacyPolicy} checked="checked"{/if} value="0" />
                    <label for="acceptpolicies_privacypolicy_no">{gt text='Policy not accepted.'}</label>
                </div>
                <p id="acceptpolicies_privacypolicy_error" class="alert alert-danger{if !isset($fieldErrors.privacypolicy) || empty($fieldErrors.privacypolicy)} hidden{/if}">
                    {$fieldErrors.privacypolicy|default:''|safetext}
                </p>
            </div>
            {else}
            <div class="col-lg-9">
                <span class="form-control-static">{if $acceptedPolicies.privacyPolicy}{gt text='Accepted.'}{else}{gt text='Not accepted.'}{/if}</span>
            </div>
            {/if}
        </div>
    {/if}
    {if $activePolicies.agePolicy && $viewablePolicies.agePolicy}
        <div class="form-group{if isset($fieldErrors.agepolicy) && !empty($fieldErrors.agepolicy)} has-error{/if}">
            <label class="col-lg-3 control-label">{gt text='Minimum Age'}</label>
            {if ($editablePolicies.agePolicy)}
            <div class="col-lg-9">
                <div class="radio">
                    <input type="radio" id="acceptpolicies_agepolicy_yes" name="acceptedpolicies_agepolicy"{if $acceptedPolicies.agePolicy} checked="checked"{/if} value="1" />
                    <label for="acceptpolicies_agepolicy_yes">{gt text='Confirmed minimum age requirement (%1$s years of age) met.' tag1=$modvars.$module.minimumAge|safetext}</label>
                </div>
                <div class="radio">
                    <input type="radio" id="acceptpolicies_agepolicy_no" name="acceptedpolicies_agepolicy"{if !$acceptedPolicies.agePolicy} checked="checked"{/if} value="0" />
                    <label for="acceptpolicies_agepolicy_no">{gt text='Minimum age requirement not confirmed.'}</label>
                </div>
                <p id="acceptpolicies_agepolicy_error" class="alert alert-danger{if !isset($fieldErrors.agepolicy) || empty($fieldErrors.agepolicy)} hidden{/if}">
                    {$fieldErrors.agepolicy|default:''|safetext}
                </p>
            </div>
            {else}
            <div class="col-lg-9">
                <span class="form-control-static">{if $acceptedPolicies.agePolicy}{gt text='Confirmed minimum age requirement (%1$s years of age) met.' tag1=$modvars.$module.minimumAge|safetext}{else}{gt text='Minimum age requirement not confirmed.'}{/if}</span>
            </div>
            {/if}
        </div>
    {/if}
    {if $activePolicies.tradeConditions && $viewablePolicies.tradeConditions}
        {route name='zikulalegalmodule_user_tradeconditions' assign='policyUrl'}
        {assign var='customUrl' value='Zikula\LegalModule\Constant::MODVAR_TRADECONDITIONS_URL'|constant}
        {assign var='customUrl' value=$modvars.$module.$customUrl}
        {if $customUrl ne ''}
            {assign var='policyUrl' value=$customUrl}
        {/if}
        {gt text='General Terms and Conditions of Trade' assign='policyName'}
        {assign var='policyLink' value='<a class="legal_popup" href="%1$s" target="_blank">%2$s</a>'|sprintf:$policyUrl:$policyName}
        <div class="form-group{if isset($fieldErrors.tradeconditions) && !empty($fieldErrors.tradeconditions)} has-error{/if}">
            <label class="col-lg-3 control-label">{gt text='General Terms and Conditions of Trade'}</label>
            {if ($editablePolicies.tradeConditions)}
            <div class="col-lg-9">
                <div class="radio">
                    <input type="radio" id="acceptpolicies_tradeconditions_yes" name="acceptedpolicies_tradeconditions"{if $acceptedPolicies.tradeConditions} checked="checked"{/if} value="1" />
                    <label for="acceptpolicies_tradeconditions_yes">{gt text='%1$s accepted.' tag1=$policyLink|safehtml}</label>
                </div>
                <div class="radio">
                    <input type="radio" id="acceptpolicies_tradeconditions_no" name="acceptedpolicies_tradeconditions"{if !$acceptedPolicies.tradeConditions} checked="checked"{/if} value="0" />
                    <label for="acceptpolicies_tradeconditions_no">{gt text='Policy not accepted.'}</label>
                </div>
                <p id="acceptpolicies_tradeconditions_error" class="alert alert-danger{if !isset($fieldErrors.tradeconditions) || empty($fieldErrors.tradeconditions)} hidden{/if}">
                    {$fieldErrors.tradeconditions|default:''|safetext}
                </p>
            </div>
            {else}
            <div class="col-lg-9">
                <span class="form-control-static">{if $acceptedPolicies.tradeConditions}{gt text='Accepted.'}{else}{gt text='Not accepted.'}{/if}</span>
            </div>
            {/if}
        </div>
    {/if}
    {if $activePolicies.cancellationRightPolicy && $viewablePolicies.cancellationRightPolicy}
        {route name='zikulalegalmodule_user_cancellationrightpolicy' assign='policyUrl'}
        {assign var='customUrl' value='Zikula\LegalModule\Constant::MODVAR_CANCELLATIONRIGHTPOLICY_URL'|constant}
        {assign var='customUrl' value=$modvars.$module.$customUrl}
        {if $customUrl ne ''}
            {assign var='policyUrl' value=$customUrl}
        {/if}
        {gt text='Cancellation Right Policy' assign='policyName'}
        {assign var='policyLink' value='<a class="legal_popup" href="%1$s" target="_blank">%2$s</a>'|sprintf:$policyUrl:$policyName}
        <div class="form-group{if isset($fieldErrors.cancellationrightpolicy) && !empty($fieldErrors.cancellationrightpolicy)} has-error{/if}">
            <label class="col-lg-3 control-label">{gt text='Cancellation Right Policy'}</label>
            {if ($editablePolicies.cancellationRightPolicy)}
            <div class="col-lg-9">
                <div class="radio">
                    <input type="radio" id="acceptpolicies_cancellationrightpolicy_yes" name="acceptedpolicies_cancellationrightpolicy"{if $acceptedPolicies.cancellationRightPolicy} checked="checked"{/if} value="1" />
                    <label for="acceptpolicies_cancellationrightpolicy_yes">{gt text='%1$s accepted.' tag1=$policyLink|safehtml}</label>
                </div>
                <div class="radio">
                    <input type="radio" id="acceptpolicies_cancellationrightpolicy_no" name="acceptedpolicies_cancellationrightpolicy"{if !$acceptedPolicies.cancellationRightPolicy} checked="checked"{/if} value="0" />
                    <label for="acceptpolicies_cancellationrightpolicy_no">{gt text='Policy not accepted.'}</label>
                </div>
                <p id="acceptpolicies_cancellationrightpolicy_error" class="alert alert-danger{if !isset($fieldErrors.cancellationrightpolicy) || empty($fieldErrors.cancellationrightpolicy)} hidden{/if}">
                    {$fieldErrors.cancellationrightpolicy|default:''|safetext}
                </p>
            </div>
            {else}
            <div class="col-lg-9">
                <span class="form-control-static">{if $acceptedPolicies.cancellationRightPolicy}{gt text='Accepted.'}{else}{gt text='Not accepted.'}{/if}</span>
            </div>
            {/if}
        </div>
    {/if}
</fieldset>
{/if}