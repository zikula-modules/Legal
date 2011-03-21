{ajaxheader modname='Legal' filename='Legal.UI.Edit.js' noscriptaculous=true effects=true}
<fieldset>
    <legend>{gt text='Site policies'}</legend>
    <input type="hidden" id="acceptpolicies_csrftoken" name="acceptpolicies_csrftoken" value="{insert name='csrftoken'}" />
    <input type="hidden" id="acceptpolicies_uid" name="acceptedpolicies_uid" value="{$policiesUid}" />
    {if $activePolicies.termsOfUse && $viewablePolicies.termsOfUse}
        {modurl modname='Legal' type='user' func='termsofuse' assign='policyUrl'}
        {assign var='policyLink' value='<a class="legal_popup" href="%1$s" target="_blank">Policy</a>'|sprintf:$policyUrl}
        <div class="z-formrow">
            <label>{gt text='Terms of Use'}</label>
            {if ($editablePolicies.termsOfUse)}
            <span class="z-formlist">
                <input type="radio" id="acceptpolicies_termsofuse_yes" name="acceptedpolicies_termsofuse" class="{if isset($fieldErrors.termsOfUse) && !empty($fieldErrors.termsOfUse)}z-form-error{/if}" {if $acceptedPolicies.termsOfUse}checked="checked"{/if} value="1" />
                <label for="acceptpolicies_termsofuse_yes">{gt text='%1$s accepted.' tag1=$policyLink}</label>
            </span>
            <span class="z-formlist">
                <input type="radio" id="acceptpolicies_termsofuse_no" name="acceptedpolicies_termsofuse" class="{if isset($fieldErrors.termsOfUse) && !empty($fieldErrors.termsOfUse)}z-form-error{/if}" {if !$acceptedPolicies.termsOfUse}checked="checked"{/if} value="0" />
                <label for="acceptpolicies_termsofuse_no">{gt text='Policy not accepted.'}</label>
            </span>
            <p id="acceptpolicies_termsofuse_error" class="z-formnote z-errormsg {if !isset($fieldErrors.termsofuse) || empty($fieldErrors.termsofuse)}z-hide{/if}">
                {$fieldErrors.termsofuse|default:''|safetext}
            </p>
            {else}
            <span>{if $acceptedPolicies.termsOfUse}{gt text='Accepted.'}{else}{gt text='Not accepted.'}{/if}</span>
            {/if}
        </div>
    {/if}
    {if $activePolicies.privacyPolicy && $viewablePolicies.privacyPolicy}
        {modurl modname='Legal' type='user' func='privacypolicy' assign='policyUrl'}
        {assign var='policyLink' value='<a class="legal_popup" href="%1$s" target="_blank">Policy</a>'|sprintf:$policyUrl}
        <div class="z-formrow">
            <label>{gt text='Privacy Policy'}</label>
            {if ($editablePolicies.privacyPolicy)}
            <span class="z-formlist">
                <input type="radio" id="acceptpolicies_privacypolicy_yes" name="acceptedpolicies_privacypolicy" class="{if isset($fieldErrors.privacyPolicy) && !empty($fieldErrors.privacyPolicy)}z-form-error{/if}" {if $acceptedPolicies.privacyPolicy}checked="checked"{/if} value="1" />
                <label for="acceptpolicies_privacypolicy_yes">{gt text='%1$s accepted.' tag1=$policyLink}</label>
            </span>
            <span class="z-formlist">
                <input type="radio" id="acceptpolicies_privacypolicy_no" name="acceptedpolicies_privacypolicy" class="{if isset($fieldErrors.privacyPolicy) && !empty($fieldErrors.privacyPolicy)}z-form-error{/if}" {if !$acceptedPolicies.privacyPolicy}checked="checked"{/if} value="0" />
                <label for="acceptpolicies_privacypolicy_no">{gt text='Policy not accepted.'}</label>
            </span>
            <p id="acceptpolicies_privacypolicy_error" class="z-formnote z-errormsg {if !isset($fieldErrors.privacypolicy) || empty($fieldErrors.privacypolicy)}z-hide{/if}">
                {$fieldErrors.privacypolicy|default:''|safetext}
            </p>
            {else}
            <span>{if $acceptedPolicies.privacyPolicy}{gt text='Accepted.'}{else}{gt text='Not accepted.'}{/if}</span>
            {/if}
        </div>
    {/if}
    {if $activePolicies.agePolicy && $viewablePolicies.agePolicy}
        <div class="z-formrow">
            <label>{gt text='Minimum Age'}</label>
            {if ($editablePolicies.agePolicy)}
            <span class="z-formlist">
                <input type="radio" id="acceptpolicies_agepolicy_yes" name="acceptedpolicies_agepolicy" class="{if isset($fieldErrors.agePolicy) && !empty($fieldErrors.agePolicy)}z-form-error{/if}" {if $acceptedPolicies.agePolicy}checked="checked"{/if} value="1" />
                <label for="acceptpolicies_agepolicy_yes">{gt text='Confirmed minimum age requirement (%1$s years of age) met.' tag1=$modvars.Legal.minimumAge}</label>
            </span>
            <span class="z-formlist">
                <input type="radio" id="acceptpolicies_agepolicy_no" name="acceptedpolicies_agepolicy" class="{if isset($fieldErrors.agePolicy) && !empty($fieldErrors.agePolicy)}z-form-error{/if}" {if !$acceptedPolicies.agePolicy}checked="checked"{/if} value="0" />
                <label for="acceptpolicies_agepolicy_no">{gt text='Minimum age requirement not confirmed.'}</label>
            </span>
            <p id="acceptpolicies_agepolicy_error" class="z-formnote z-errormsg {if !isset($fieldErrors.agepolicy) || empty($fieldErrors.agepolicy)}z-hide{/if}">
                {$fieldErrors.agepolicy|default:''|safetext}
            </p>
            {else}
            <span>{if $acceptedPolicies.agePolicy}{gt text='Confirmed minimum age requirement (%1$s years of age) met.' tag1=$modvars.Legal.minimumAge}{else}{gt text='Minimum age requirement not confirmed.'}{/if}</span>
            {/if}
        </div>
    {/if}
</fieldset>

