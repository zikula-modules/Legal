<div class="z-form">
    <fieldset>
        <legend>{gt text='Site policies'}</legend>
        {if $activePolicies.termsOfUse && $viewablePolicies.termsOfUse}
            {modurl modname='Legal' type='user' func='termsofuse' assign='policyUrl'}
            {assign var='policyLink' value='<a class="legal_popup" href="%1$s" target="_blank">Policy</a>'|sprintf:$policyUrl}
            <div class="z-formrow">
                <label>{gt text='Terms of Use:'}</label>
                <span>{if $acceptedPolicies.termsOfUse}{gt text='%1$s accepted.' tag1=$policyLink}{else}{gt text='%1$s not accepted.' tag1=$policyLink}{/if}</span>
            </div>
        {/if}
        {if $activePolicies.privacyPolicy && $viewablePolicies.privacyPolicy}
            {modurl modname='Legal' type='user' func='privacypolicy' assign='policyUrl'}
            {assign var='policyLink' value='<a class="legal_popup" href="%1$s" target="_blank">Policy</a>'|sprintf:$policyUrl}
            <div class="z-formrow">
                <label>{gt text='Privacy Policy:'}</label>
                <span>{if $acceptedPolicies.privacyPolicy}{gt text='%1$s accepted.' tag1=$policyLink}{else}{gt text='%1$s not accepted.' tag1=$policyLink}{/if}</span>
            </div>
        {/if}
        {if $activePolicies.agePolicy && $viewablePolicies.agePolicy}
            <div class="z-formrow">
                <label>{gt text='Minimum Age'}</label>
                <span>{if $acceptedPolicies.agePolicy}{gt text='Confirmed minimum age requirement (%1$s years of age) met.' tag1=$modvars.Legal.minimumAge}{else}{gt text='Minimum age requirement not confirmed.'}{/if}</span>
            </div>
        {/if}
    </fieldset>
</div>
