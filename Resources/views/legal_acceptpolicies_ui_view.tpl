<div class="form-horizontal">
    <fieldset>
        <legend>{gt text='Site policies'}</legend>
        {if $activePolicies.termsOfUse && $viewablePolicies.termsOfUse}
            {modurl modname=$module type='user' func='termsofuse' assign='policyUrl'}
            {assign var='customUrl' value='Zikula\LegalModule\Constant::MODVAR_TERMS_URL'|constant}
            {assign var='customUrl' value=$modvars.$module.$customUrl}
            {if $customUrl ne ''}
                {assign var='policyUrl' value=$customUrl}
            {/if}
            {gt text='Terms of Use' assign='policyName'}
            {assign var='policyLink' value='<a class="legal_popup" href="%1$s" target="_blank">%2$s</a>'|sprintf:$policyUrl:$policyName}
            <div class="form-group">
                <label class="col-lg-3 control-label">{gt text='Terms of Use:'}</label>
                <div class="col-lg-9">
                    <span class="form-control-static">{if $acceptedPolicies.termsOfUse}{gt text='%1$s accepted.' tag1=$policyLink|safehtml}{else}{gt text='%1$s not accepted.' tag1=$policyLink|safehtml}{/if}</span>
                </div>
            </div>
        {/if}
        {if $activePolicies.privacyPolicy && $viewablePolicies.privacyPolicy}
            {modurl modname=$module type='user' func='privacypolicy' assign='policyUrl'}
            {assign var='customUrl' value='Zikula\LegalModule\Constant::MODVAR_PRIVACY_URL'|constant}
            {assign var='customUrl' value=$modvars.$module.$customUrl}
            {if $customUrl ne ''}
                {assign var='policyUrl' value=$customUrl}
            {/if}
            {gt text='Privacy Policy' assign='policyName'}
            {assign var='policyLink' value='<a class="legal_popup" href="%1$s" target="_blank">%2$s</a>'|sprintf:$policyUrl:$policyName}
            <div class="form-group">
                <label class="col-lg-3 control-label">{gt text='Privacy Policy:'}</label>
                <div class="col-lg-9">
                    <span class="form-control-static">{if $acceptedPolicies.privacyPolicy}{gt text='%1$s accepted.' tag1=$policyLink|safehtml}{else}{gt text='%1$s not accepted.' tag1=$policyLink|safehtml}{/if}</span>
                </div>
            </div>
        {/if}
        {if $activePolicies.agePolicy && $viewablePolicies.agePolicy}
            <div class="form-group">
                <label class="col-lg-3 control-label">{gt text='Minimum Age'}</label>
                <div class="col-lg-9">
                    <span class="form-control-static">{if $acceptedPolicies.agePolicy}{gt text='Confirmed minimum age requirement (%1$s years of age) met.' tag1=$modvars.$module.minimumAge|safetext}{else}{gt text='Minimum age requirement not confirmed.'}{/if}</span>
                </div>
            </div>
        {/if}
        {if $activePolicies.tradeConditions && $viewablePolicies.tradeConditions}
            {modurl modname=$module type='user' func='tradeConditions' assign='policyUrl'}
            {assign var='customUrl' value='Zikula\LegalModule\Constant::MODVAR_TRADECONDITIONS_URL'|constant}
            {assign var='customUrl' value=$modvars.$module.$customUrl}
            {if $customUrl ne ''}
                {assign var='policyUrl' value=$customUrl}
            {/if}
            {gt text='General Terms and Conditions of Trade' assign='policyName'}
            {assign var='policyLink' value='<a class="legal_popup" href="%1$s" target="_blank">%2$s</a>'|sprintf:$policyUrl:$policyName}
            <div class="form-group">
                <label class="col-lg-3 control-label">{gt text='General Terms and Conditions of Trade:'}</label>
                <div class="col-lg-9">
                    <span class="form-control-static">{if $acceptedPolicies.tradeConditions}{gt text='%1$s accepted.' tag1=$policyLink|safehtml}{else}{gt text='%1$s not accepted.' tag1=$policyLink|safehtml}{/if}</span>
                </div>
            </div>
        {/if}
        {if $activePolicies.cancellationRightPolicy && $viewablePolicies.cancellationRightPolicy}
            {modurl modname=$module type='user' func='cancellationRightPolicy' assign='policyUrl'}
            {assign var='customUrl' value='Zikula\LegalModule\Constant::MODVAR_CANCELLATIONRIGHTPOLICY_URL'|constant}
            {assign var='customUrl' value=$modvars.$module.$customUrl}
            {if $customUrl ne ''}
                {assign var='policyUrl' value=$customUrl}
            {/if}
            {gt text='Cancellation Right Policy' assign='policyName'}
            {assign var='policyLink' value='<a class="legal_popup" href="%1$s" target="_blank">%2$s</a>'|sprintf:$policyUrl:$policyName}
            <div class="form-group">
                <label class="col-lg-3 control-label">{gt text='Cancellation Right Policy:'}</label>
                <div class="col-lg-9">
                    <span class="form-control-static">{if $acceptedPolicies.cancellationRightPolicy}{gt text='%1$s accepted.' tag1=$policyLink|safehtml}{else}{gt text='%1$s not accepted.' tag1=$policyLink|safehtml}{/if}</span>
                </div>
            </div>
        {/if}
    </fieldset>
</div>
