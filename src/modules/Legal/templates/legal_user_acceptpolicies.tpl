{* TODO - Use Zikula.UI to display policies in a pop-up window. *}
{gt text='Site policies' assign='templatetitle'}
{pagesetvar name='title' value=$templatetitle}
<h2>{$templatetitle}</h2>

{insert name='getstatusmsg'}

{if $login}
    <div class="z-warningmsg">
        {gt text='In order to log in you must accept this site\'s policies. If you have accepted the site\'s policies in the past, then they have been updated and we ask that you review the changes.'}
        <hr />
        {gt text='If you leave this page without successfully accepting the policies, then you will not be logged in.'}
    </div>
{/if}

<form id="legal_user_acceptpolicies" class="z-form" action="{modurl modname="Legal" type="user" func="acceptPolicies"}" method="post">
    <div>
        <input type="hidden" id="acceptpolicies_csrftoken" name="csrftoken" value="{insert name='csrftoken'}" />
        <input type="hidden" id="acceptpolicies_uid" name="acceptedpolicies_uid" value="{$policiesUid}" />
        <fieldset>
        {if $activePolicies.termsOfUse && !$originalAcceptedPolicies.termsOfUse}
            {modurl modname='Legal' type='user' func='termsofuse' assign='policyUrl'}
            {gt text='Terms of Use' assign='policyName'}
            {assign var='policyLink' value='<a class="legal_popup" href="%1$s" target="_blank">%2$s</a>'|sprintf:$policyUrl:$policyName}
            <div class="z-formrow">
                <label for="acceptpolicies_termsofuse">{gt text='Terms of Use'}</label>
                <span class="z-formlist">
                    <input type="checkbox" id="acceptpolicies_termsofuse" name="acceptedpolicies_termsofuse" {if isset($fieldErrors.termsOfUse) && !empty($fieldErrors.termsOfUse)}class="z-form-error"{/if} {if $acceptedPolicies.termsOfUse}checked="checked"{/if} value="1" />
                    <label for="acceptpolicies_termsofuse">{gt text='Check this box to indicate your acceptance of this site\'s %1$s.' tag1=$policyLink|safehtml}</label>
                </span>
                <p id="acceptpolicies_termsofuse_error" class="z-formnote z-errormsg {if !isset($fieldErrors.termsofuse) || empty($fieldErrors.termsofuse)}z-hide{/if}">
                    {$fieldErrors.termsofuse|default:''|safetext}
                </p>
            </div>
        {/if}
        {if $activePolicies.privacyPolicy && !$originalAcceptedPolicies.privacyPolicy}
            {modurl modname='Legal' type='user' func='privacypolicy' assign='policyUrl'}
            {gt text='Privacy Policy' assign='policyName'}
            {assign var='policyLink' value='<a class="legal_popup" href="%1$s" target="_blank">%2$s</a>'|sprintf:$policyUrl:$policyName}
            <div class="z-formrow">
                <label for="acceptpolicies_privacypolicy">{gt text='Privacy Policy'}</label>
                <span class="z-formlist">
                    <input type="checkbox" id="acceptpolicies_privacypolicy" name="acceptedpolicies_privacypolicy" {if isset($fieldErrors.privacyPolicy) && !empty($fieldErrors.privacyPolicy)}class="z-form-error"{/if} {if $acceptedPolicies.privacyPolicy}checked="checked"{/if} value="1" />
                    <label for="acceptpolicies_privacypolicy">{gt text='Check this box to indicate your acceptance of this site\'s %1$s.' tag1=$policyLink|safehtml}</label>
                </span>
                <p id="acceptpolicies_privacypolicy_error" class="z-formnote z-errormsg {if !isset($fieldErrors.privacypolicy) || empty($fieldErrors.privacypolicy)}z-hide{/if}">
                    {$fieldErrors.privacypolicy|default:''|safetext}
                </p>
            </div>
        {/if}
        {if $activePolicies.agePolicy && !$originalAcceptedPolicies.agePolicy}
            {modurl modname='Legal' type='user' func='termsofuse' assign='policyUrl'}
            {gt text='Terms of Use' assign='policyName'}
            {assign var='termsOfUseLink' value='<a class="legal_popup" href="%1$s" target="_blank">%2$s</a>'|sprintf:$policyUrl:$policyName}
            {modurl modname='Legal' type='user' func='privacypolicy' assign='policyUrl'}
            {gt text='Privacy Policy' assign='policyName'}
            {assign var='privacyPolicyLink' value='<a class="legal_popup" href="%1$s" target="_blank">%2$s</a>'|sprintf:$policyUrl:$policyName}
            <div class="z-formrow">
                <label for="acceptpolicies_agepolicy">{gt text='Minimum Age'}</label>
                <span class="z-formlist">
                    <input type="checkbox" id="acceptpolicies_agepolicy" name="acceptedpolicies_agepolicy" {if isset($fieldErrors.agePolicy) && !empty($fieldErrors.agePolicy)}class="z-form-error"{/if} {if $acceptedPolicies.agePolicy}checked="checked"{/if} value="1" />
                    <label for="acceptpolicies_agepolicy">{gt text='Check this box to indicate that you are %1$s years of age or older.' tag1=$modvars.Legal.minimumAge|safetext}</label>
                </span>
                <em class="z-formnote z-sub">{gt text='Information on our minimum age policy, and on how we handle personally identifiable information can be found in our %1$s and in our %2$s.' tag1=$termsOfUseLink|safehtml tag2=$privacyPolicyLink|safehtml}</em>
                <p id="acceptpolicies_agepolicy_error" class="z-formnote z-errormsg {if !isset($fieldErrors.agepolicy) || empty($fieldErrors.agepolicy)}z-hide{/if}">
                    {$fieldErrors.agepolicy|default:''|safetext}
                </p>
            </div>
        {/if}
        </fieldset>
        <div class="z-formbuttons z-buttons">
            {if $login}
            {button src='button_ok.png' set='icons/extrasmall' __alt='Save and continue logging in' __title='Save and continue logging in' __text='Save and continue logging in'}
            {else}
            {button src='button_ok.png' set='icons/extrasmall' __alt='Save' __title='Save' __text='Save'}
            {/if}
            <a href="{homepage}" title="{gt text='Cancel'}">{img modname='core' src='button_cancel.png' set='icons/extrasmall' __alt='Cancel' __title='Cancel'} {gt text='Cancel'}</a>
        </div>
    </div>
</form>
