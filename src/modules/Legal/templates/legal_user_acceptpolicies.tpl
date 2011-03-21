{if $policies.termsOfUse && $policies.privacyPolicy}
    {gt text='Terms of Use and Privacy Policy' assign='templatetitle'}
    {gt text='Terms of Use and Privacy Policy' assign='policyText'}
    {legalinlinelink policyType='termsOfUse' target='_blank' assign='termsOfUseLink'}
    {legalinlinelink policyType='privacyPolicy' target='_blank' assign='privacyPolicyLink'}
{elseif $policies.termsOfUse}
    {gt text='Terms of Use' assign='templatetitle'}
    {gt text='Terms of Use' assign='policyText'}
    {legalinlinelink policyType='termsOfUse' target='_blank' assign='termsOfUseLink'}
{elseif $policies.privacyPolicy}
    {gt text='Privacy Policy' assign='templatetitle'}
    {gt text='privacy Policy' assign='policyText'}
    {legalinlinelink policyType='privacyPolicy' target='_blank' assign='privacyPolicyLink'}
{/if}
{pagesetvar name='title' value=$templatetitle}
<h2>{$templatetitle}</h2>

{insert name='getstatusmsg'}

{if $login}
    <div class="z-warningmsg">
        <p>{gt text='Before logging in, the site administrator has asked that you accept the site\'s %1$s.' tag1=$policyText}</p>
        <p>{gt text='If you leave this page without successfully accepting the %1$s, then you will not be logged in.' tag1=$policyText}</p>
    </div>
{/if}

<form id="legal_user_acceptpolicies" class="z-form" action="{modurl modname="Legal" type="user" func="updatePolicyAcceptance"}" method="post">
    <div>
        <input type="hidden" id="acceptpolicies_csrftoken" name="csrftoken" value="{insert name='csrftoken'}" />
        <input type="hidden" id="acceptpolicies_uid" name="acceptPolicies[uid]" value="{$uid}" />
        <fieldset>
            <div class="z-formrow">
                <span class="z-formlist">
                    <input type="checkbox" id="acceptpolicies_termsofuse" name="acceptPolicies[termsOfUse]" class="{if isset($fieldErrors.termsOfUse) && !empty($fieldErrors.termsOfUse)}z-form-error{/if}" value="1" />
                    <label for="acceptpolicies_termsofuse">{gt text="Check this box to indicate your acceptance of this site's %s."|sprintf:$termsOfUseLink}</label>
                </span>
                {if isset($fieldErrors.termsOfUse) && !empty($fieldErrors.termsOfUse)}
                <div class="z-formnote z-errormsg">
                    {foreach from=$fieldErrors.termsOfUse item='message' name='messages'}
                    <p>{$message}</p>
                    {/foreach}
                </div>
                {/if}
            </div>
            <div class="z-formrow">
                <span class="z-formlist">
                    <input type="checkbox" id="acceptpolicies_privacypolicy" name="acceptPolicies[privacyPolicy]" class="{if isset($fieldErrors.privacyPolicy) && !empty($fieldErrors.privacyPolicy)}z-form-error{/if}" value="1" />
                    <label for="acceptpolicies_privacypolicy">{gt text="Check this box to indicate your acceptance of this site's %s."|sprintf:$privacyPolicyLink}</label>
                </span>
                {if isset($fieldErrors.privacyPolicy) && !empty($fieldErrors.privacyPolicy)}
                <div class="z-formnote z-errormsg">
                    {foreach from=$fieldErrors.privacyPolicy item='message' name='messages'}
                    <p>{$message}</p>
                    {/foreach}
                </div>
                {/if}
            </div>
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
