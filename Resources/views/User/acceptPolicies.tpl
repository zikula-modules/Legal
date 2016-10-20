{* TODO - Use Bootstrap to display policies in a pop-up window. *}
{gt text='Site policies' assign='templatetitle'}
{pagesetvar name='title' value=$templatetitle}
<h2>{$templatetitle}</h2>

{insert name='getstatusmsg'}

{if $login}
    <div class="alert alert-danger">
        {gt text='In order to log in you must accept this site\'s policies. If you have accepted the site\'s policies in the past, then they have been updated and we ask that you review the changes.'}
        <hr />
        {gt text='If you leave this page without successfully accepting the policies, then you will not be logged in.'}
    </div>
{/if}

<form id="legal_user_acceptpolicies" class="form-horizontal" action="{route name='zikulalegalmodule_user_acceptpolicies'}" method="post">
    <div>
        <input type="hidden" id="acceptpolicies_csrftoken" name="csrftoken" value="{insert name='csrftoken'}" />
        <input type="hidden" id="acceptpolicies_uid" name="acceptedpolicies_uid" value="{$policiesUid}" />
        <fieldset>
        {if $activePolicies.termsOfUse && !$originalAcceptedPolicies.termsOfUse}
            {route name='zikulalegalmodule_user_termsofuse' assign='policyUrl'}
            {assign var='customUrl' value='Zikula\LegalModule\Constant::MODVAR_TERMS_URL'|constant}
            {if $customUrl ne ''}
                {assign var='policyUrl' value=$customUrl}
            {/if}
            {gt text='Terms of Use' assign='policyName'}
            {assign var='policyLink' value='<a class="legal_popup" href="%1$s" target="_blank">%2$s</a>'|sprintf:$policyUrl:$policyName}
            <div class="form-group{if isset($fieldErrors.termsOfUse) && !empty($fieldErrors.termsOfUse)} has-error{/if}">
                <div class="col-sm-3 control-label">
                    <label for="acceptpolicies_termsofuse">{gt text='Terms of Use'}</label>
                </div>
                <div class="col-sm-9">
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
        {if $activePolicies.privacyPolicy && !$originalAcceptedPolicies.privacyPolicy}
            {route name='zikulalegalmodule_user_privacypolicy' assign='policyUrl'}
            {assign var='customUrl' value='Zikula\LegalModule\Constant::MODVAR_PRIVACY_URL'|constant}
            {if $customUrl ne ''}
                {assign var='policyUrl' value=$customUrl}
            {/if}
            {gt text='Privacy Policy' assign='policyName'}
            {assign var='policyLink' value='<a class="legal_popup" href="%1$s" target="_blank">%2$s</a>'|sprintf:$policyUrl:$policyName}
            <div class="form-group{if isset($fieldErrors.privacyPolicy) && !empty($fieldErrors.privacyPolicy)} has-error{/if}">
                <div class="col-sm-3 control-label">
                    <label for="acceptpolicies_privacypolicy">{gt text='Privacy Policy'}</label>
                </div>
                <div class="col-sm-9">
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
        {if $activePolicies.agePolicy && !$originalAcceptedPolicies.agePolicy}
            {route name='zikulalegalmodule_user_termsofuse' assign='policyUrl'}
            {assign var='customUrl' value='Zikula\LegalModule\Constant::MODVAR_TERMS_URL'|constant}
            {assign var='customUrl' value=$modvars.$module.$customUrl}
            {if $customUrl ne ''}
                {assign var='policyUrl' value=$customUrl}
            {/if}
            {gt text='Terms of Use' assign='policyName'}
            {assign var='termsOfUseLink' value='<a class="legal_popup" href="%1$s" target="_blank">%2$s</a>'|sprintf:$policyUrl:$policyName}

            {route name='zikulalegalmodule_user_privacypolicy' assign='policyUrl'}
            {assign var='customUrl' value='Zikula\LegalModule\Constant::MODVAR_PRIVACY_URL'|constant}
            {assign var='customUrl' value=$modvars.$module.$customUrl}
            {if $customUrl ne ''}
                {assign var='policyUrl' value=$customUrl}
            {/if}
            {gt text='Privacy Policy' assign='policyName'}
            {assign var='privacyPolicyLink' value='<a class="legal_popup" href="%1$s" target="_blank">%2$s</a>'|sprintf:$policyUrl:$policyName}

            <div class="form-group{if isset($fieldErrors.agePolicy) && !empty($fieldErrors.agePolicy)} has-error{/if}">
                <div class="col-sm-3 control-label">
                    <label for="acceptpolicies_agepolicy">{gt text='Minimum Age'}</label>
                </div>
                <div class="col-sm-9">
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
        {if $activePolicies.tradeConditions && !$originalAcceptedPolicies.tradeConditions}
            {route name='zikulalegalmodule_user_tradeconditions' assign='policyUrl'}
            {assign var='customUrl' value='Zikula\LegalModule\Constant::MODVAR_TRADECONDITIONS_URL'|constant}
            {if $customUrl ne ''}
                {assign var='policyUrl' value=$customUrl}
            {/if}
            {gt text='General Terms and Conditions of Trade' assign='policyName'}
            {assign var='policyLink' value='<a class="legal_popup" href="%1$s" target="_blank">%2$s</a>'|sprintf:$policyUrl:$policyName}
            <div class="form-group{if isset($fieldErrors.tradeconditions) && !empty($fieldErrors.tradeconditions)} has-error{/if}">
                <div class="col-sm-3 control-label">
                    <label for="acceptpolicies_tradeconditions">{gt text='General Terms and Conditions of Trade'}</label>
                </div>
                <div class="col-sm-9">
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
        {if $activePolicies.cancellationRightPolicy && !$originalAcceptedPolicies.cancellationRightPolicy}
            {route name='zikulalegalmodule_user_cancellationrightpolicy' assign='policyUrl'}
            {assign var='customUrl' value='Zikula\LegalModule\Constant::MODVAR_CANCELLATIONRIGHTPOLICY_URL'|constant}
            {if $customUrl ne ''}
                {assign var='policyUrl' value=$customUrl}
            {/if}
            {gt text='Cancellation Right Policy' assign='policyName'}
            {assign var='policyLink' value='<a class="legal_popup" href="%1$s" target="_blank">%2$s</a>'|sprintf:$policyUrl:$policyName}
            <div class="form-group{if isset($fieldErrors.cancellationrightpolicy) && !empty($fieldErrors.cancellationrightpolicy)} has-error{/if}">
                <div class="col-sm-3 control-label">
                    <label for="acceptpolicies_cancellationrightpolicy">{gt text='Cancellation Right Policy'}</label>
                </div>
                <div class="col-sm-9">
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
        <div class="col-sm-offset-3 col-sm-9">
            {if $login}
            <button class="btn btn-success" type="submit" name="Save">{gt text="Save and continue logging in"}</button>
            {else}
            <button class="btn btn-success" type="submit" name="Save">{gt text="Save"}</button>
            {/if}
            <a class="btn btn-danger" href="{homepage}" title="{gt text="Cancel"}">{gt text="Cancel and Logout"}</a>
        </div>
    </div>
</form>
