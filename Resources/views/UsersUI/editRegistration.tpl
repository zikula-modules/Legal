<fieldset>
    <legend>{gt text='Site Policies'}</legend>
    <input type="hidden" id="acceptpolicies_csrftoken" name="acceptpolicies_csrftoken" value="{insert name='csrftoken'}" />
    <input type="hidden" id="acceptpolicies_uid" name="acceptedpolicies_uid" value="" />
    {if ($activePolicies.termsOfUse)}
        {gt text='Terms of Use' assign='policyName'}
        {route name='zikulalegalmodule_user_termsofuse' assign='policyUrl'}
        {assign var='customUrl' value='Zikula\LegalModule\Constant::MODVAR_TERMS_URL'|constant}
        {assign var='customUrl' value=$modvars.$module.$customUrl}
        {if (!empty($customUrl))}
            {assign var='policyUrl' value=$customUrl}
            {assign var='policyLink' value='<a data-toggle="modal" data-target="#modal-terms-of-use" href="%1$s">%2$s</a>'|sprintf:$policyUrl:$policyName}
        {else}
            {assign var='policyLink' value='<a data-toggle="modal" data-target="#modal-terms-of-use">%1$s</a>'|sprintf:$policyName}
        {/if}
        {* Modal Window *}
        <div class="modal fade" id="modal-terms-of-use" tabindex="-1" role="dialog" aria-labelledby="modal-title-terms-of-use" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title" id="modal-title-terms-of-use">{gt text=$policyName}</h4>
                    </div>
                    <div class="modal-body">
                        {if (!empty($customUrl))}
                            <iframe width="101%" frameborder="0" scrolling="auto" allowtransparency="true" src="{$policyUrl}"></iframe>
                        {else}
                            {include file="`$lang`/legal_text_termsofuse.tpl"}
                        {/if}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">{gt text='Close'}</button>
                    </div>
                </div>
            </div>
        </div>
        {* /Modal Window *}
        <div class="form-group{if isset($fieldErrors.termsofuse) && !empty($fieldErrors.termsofuse)} has-error{/if}">
            <label class="col-sm-3 control-label" for="acceptpolicies_termsofuse">{gt text='Terms of Use'}&nbsp;<span class="required"></span></label>
            <div class="col-sm-9">
                <div class="checkbox">
                    <input type="checkbox" id="acceptpolicies_termsofuse" name="acceptedpolicies_termsofuse"{if $acceptedPolicies.termsOfUse} checked="checked"{/if} value="1" required="required" />
                    <em class="help-text">{gt text='Check this box to indicate your acceptance of this site\'s %1$s.' tag1=$policyLink}</em>
                </div>
                <p id="acceptpolicies_termsofuse_error" class="alert alert-danger{if !isset($fieldErrors.termsofuse) || empty($fieldErrors.termsofuse)} hidden{/if}">
                    {$fieldErrors.termsofuse|default:''|safetext}
                </p>
            </div>
        </div>
    {/if}
    {if ($activePolicies.privacyPolicy)}
        {gt text='Privacy Policy' assign='policyName'}
        {route name='zikulalegalmodule_user_privacypolicy' assign='policyUrl'}
        {assign var='customUrl' value='Zikula\LegalModule\Constant::MODVAR_PRIVACY_URL'|constant}
        {assign var='customUrl' value=$modvars.$module.$customUrl}
        {if (!empty($customUrl))}
            {assign var='policyUrl' value=$customUrl}
            {assign var='policyLink' value='<a data-toggle="modal" data-target="#modal-privacy-policy" href="%1$s">%2$s</a>'|sprintf:$policyUrl:$policyName}
        {else}
            {assign var='policyLink' value='<a data-toggle="modal" data-target="#modal-privacy-policy">%1$s</a>'|sprintf:$policyName}
        {/if}
        {* Modal Window *}
        <div class="modal fade" id="modal-privacy-policy" tabindex="-1" role="dialog" aria-labelledby="modal-title-privacy-policy" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title" id="modal-title-privacy-policy">{gt text=$policyName}</h4>
                    </div>
                    <div class="modal-body">
                        {if (!empty($customUrl))}
                            <iframe width="101%" frameborder="0" scrolling="auto" allowtransparency="true" src="{$policyUrl}"></iframe>
                        {else}
                            {include file="`$lang`/legal_text_privacypolicy.tpl"}
                        {/if}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">{gt text='Close'}</button>
                    </div>
                </div>
            </div>
        </div>
        {* /Modal Window *}
        <div class="form-group{if isset($fieldErrors.privacypolicy) && !empty($fieldErrors.privacypolicy)} has-error{/if}">
            <label class="col-sm-3 control-label" for="acceptpolicies_privacypolicy">{gt text='Privacy Policy'}&nbsp;<span class="required"></span></label>
            <div class="col-sm-9">
                <div class="checkbox">
                    <input type="checkbox" id="acceptpolicies_privacypolicy" name="acceptedpolicies_privacypolicy"{if $acceptedPolicies.privacyPolicy} checked="checked"{/if} value="1" required="required" />
                    <em class="help-text">{gt text='Check this box to indicate your acceptance of this site\'s %1$s.' tag1=$policyLink}</em>
                </div>
                <p id="acceptpolicies_privacypolicy_error" class="alert alert-danger{if !isset($fieldErrors.privacypolicy) || empty($fieldErrors.privacypolicy)} hidden{/if}">
                    {$fieldErrors.privacypolicy|default:''|safetext}
                </p>
            </div>
        </div>
    {/if}
    {if ($activePolicies.agePolicy)}
        {gt text='Terms of Use' assign='policyName'}
        {route name='zikulalegalmodule_user_termsofuse' assign='policyUrl'}
        {assign var='customUrl' value='Zikula\LegalModule\Constant::MODVAR_TERMS_URL'|constant}
        {assign var='customUrl' value=$modvars.$module.$customUrl}
        {if (!empty($customUrl))}
            {assign var='policyUrl' value=$customUrl}
            {assign var='termsOfUseLink' value='<a data-toggle="modal" data-target="#modal-terms-of-use-2" href="%1$s">%2$s</a>'|sprintf:$policyUrl:$policyName}
        {else}
            {assign var='termsOfUseLink' value='<a data-toggle="modal" data-target="#modal-terms-of-use-2">%1$s</a>'|sprintf:$policyName}
        {/if}
        {* Modal Window *}
        <div class="modal fade" id="modal-terms-of-use-2" tabindex="-1" role="dialog" aria-labelledby="modal-title-terms-of-use-2" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title" id="modal-title-terms-of-use-2">{gt text=$policyName}</h4>
                    </div>
                    <div class="modal-body">
                        {if (!empty($customUrl))}
                            <iframe width="101%" frameborder="0" scrolling="auto" allowtransparency="true" src="{$policyUrl}"></iframe>
                        {else}
                            {include file="`$lang`/legal_text_termsofuse.tpl"}
                        {/if}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">{gt text='Close'}</button>
                    </div>
                </div>
            </div>
        </div>
        {* /Modal Window *}
        {gt text='Privacy Policy' assign='policyName'}
        {route name='zikulalegalmodule_user_privacypolicy' assign='policyUrl'}
        {assign var='customUrl' value='Zikula\LegalModule\Constant::MODVAR_PRIVACY_URL'|constant}
        {assign var='customUrl' value=$modvars.$module.$customUrl}
        {if (!empty($customUrl))}
            {assign var='policyUrl' value=$customUrl}
            {assign var='privacyPolicyLink' value='<a data-toggle="modal" data-target="#modal-privacy-policy-2" href="%1$s">%2$s</a>'|sprintf:$policyUrl:$policyName}
        {else}
            {assign var='privacyPolicyLink' value='<a data-toggle="modal" data-target="#modal-privacy-policy-2">%1$s</a>'|sprintf:$policyName}
        {/if}
        {* Modal Window *}
        <div class="modal fade" id="modal-privacy-policy-2" tabindex="-1" role="dialog" aria-labelledby="modal-title-privacy-policy-2" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title" id="modal-title-privacy-policy-2">{gt text=$policyName}</h4>
                    </div>
                    <div class="modal-body">
                        {if (!empty($customUrl))}
                            <iframe width="101%" frameborder="0" scrolling="auto" allowtransparency="true" src="{$policyUrl}"></iframe>
                        {else}
                            {include file="`$lang`/legal_text_privacypolicy.tpl"}
                        {/if}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">{gt text='Close'}</button>
                    </div>
                </div>
            </div>
        </div>
        {* /Modal Window *}
        <div class="form-group{if ((isset($fieldErrors.agepolicy)) && (!empty($fieldErrors.agepolicy)))} has-error{/if}">
            <label class="col-sm-3 control-label" for="acceptpolicies_agepolicy">{gt text='Minimum Age'}&nbsp;<span class="required"></span></label>
            <div class="col-sm-9">
                <div class="checkbox">
                    <input type="checkbox" id="acceptpolicies_agepolicy" name="acceptedpolicies_agepolicy"{if ($acceptedPolicies.agePolicy)} checked="checked"{/if} value="1" required="required" />
                    <em class="help-text">{gt text='Check this box to indicate that you are %1$s years of age or older, in accordance with our minimum age requirement.' tag1=$modvars.$module.minimumAge|safetext}</em>
                </div>
                <p id="acceptpolicies_agepolicy_error" class="alert alert-danger{if !isset($fieldErrors.agepolicy) || empty($fieldErrors.agepolicy)} hidden{/if}">
                    {$fieldErrors.agepolicy|default:''|safetext}
                </p>
            </div>
        </div>
    {/if}
    {if ($activePolicies.tradeConditions)}
        {gt text='General Terms and Conditions of Trade' assign='policyName'}
        {route name='zikulalegalmodule_user_tradeconditions' assign='policyUrl'}
        {assign var='customUrl' value='Zikula\LegalModule\Constant::MODVAR_TRADECONDITIONS_URL'|constant}
        {assign var='customUrl' value=$modvars.$module.$customUrl}
        {if (!empty($customUrl))}
            {assign var='policyUrl' value=$customUrl}
            {assign var='policyLink' value='<a data-toggle="modal" data-target="#modal-general-terms-and-conditions-of-trade" href="%1$s">%2$s</a>'|sprintf:$policyUrl:$policyName}
        {else}
            {assign var='policyLink' value='<a data-toggle="modal" data-target="#modal-general-terms-and-conditions-of-trade">%1$s</a>'|sprintf:$policyName}
        {/if}
        {* Modal Window *}
        <div class="modal fade" id="modal-general-terms-and-conditions-of-trade" tabindex="-1" role="dialog" aria-labelledby="modal-title-general-terms-and-conditions-of-trade" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title" id="modal-title-general-terms-and-conditions-of-trade">{gt text=$policyName}</h4>
                    </div>
                    <div class="modal-body">
                        {if (!empty($customUrl))}
                            <iframe width="101%" frameborder="0" scrolling="auto" allowtransparency="true" src="{$policyUrl}"></iframe>
                        {else}
                            {include file="`$lang`/legal_text_tradeconditions.tpl"}
                        {/if}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">{gt text='Close'}</button>
                    </div>
                </div>
            </div>
        </div>
        {* /Modal Window *}
        <div class="form-group{if isset($fieldErrors.tradeconditions) && !empty($fieldErrors.tradeconditions)} has-error{/if}">
            <label class="col-sm-3 control-label" for="acceptpolicies_tradeconditions">{gt text='General Terms and Conditions of Trade'}&nbsp;<span class="required"></span></label>
            <div class="col-sm-9">
                <div class="checkbox">
                    <input type="checkbox" id="acceptpolicies_tradeconditions" name="acceptedpolicies_tradeconditions"{if $acceptedPolicies.tradeConditions} checked="checked"{/if} value="1" required="required" />
                    <em class="help-text">{gt text='Check this box to indicate your acceptance of this site\'s %1$s.' tag1=$policyLink}</em>
                </div>
                <p id="acceptpolicies_tradeconditions_error" class="alert alert-danger{if !isset($fieldErrors.tradeconditions) || empty($fieldErrors.tradeconditions)} hidden{/if}">
                    {$fieldErrors.tradeconditions|default:''|safetext}
                </p>
            </div>
        </div>
    {/if}
    {if ($activePolicies.cancellationRightPolicy)}
        {gt text='Cancellation Right Policy' assign='policyName'}
        {route name='zikulalegalmodule_user_cancellationrightpolicy' assign='policyUrl'}
        {assign var='customUrl' value='Zikula\LegalModule\Constant::MODVAR_CANCELLATIONRIGHTPOLICY_URL'|constant}
        {assign var='customUrl' value=$modvars.$module.$customUrl}
        {if (!empty($customUrl))}
            {assign var='policyUrl' value=$customUrl}
            {assign var='policyLink' value='<a data-toggle="modal" data-target="#modal-cancellation-right-policy" href="%1$s">%2$s</a>'|sprintf:$policyUrl:$policyName}
        {else}
            {assign var='policyLink' value='<a data-toggle="modal" data-target="#modal-cancellation-right-policy">%1$s</a>'|sprintf:$policyName}
        {/if}
        {* Modal Window *}
        <div class="modal fade" id="modal-cancellation-right-policy" tabindex="-1" role="dialog" aria-labelledby="modal-title-cancellation-right-policy" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                        <h4 class="modal-title" id="modal-title-cancellation-right-policy">{gt text=$policyName}</h4>
                    </div>
                    <div class="modal-body">
                        {if (!empty($customUrl))}
                            <iframe width="101%" frameborder="0" scrolling="auto" allowtransparency="true" src="{$policyUrl}"></iframe>
                        {else}
                            {include file="`$lang`/legal_text_cancellationrightpolicy.tpl"}
                        {/if}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">{gt text='Close'}</button>
                    </div>
                </div>
            </div>
        </div>
        {* /Modal Window *}
        <div class="form-group{if isset($fieldErrors.cancellationrightpolicy) && !empty($fieldErrors.cancellationrightpolicy)} has-error{/if}">
            <label class="col-sm-3 control-label" for="acceptpolicies_cancellationrightpolicy">{gt text='Cancellation Right Policy'}&nbsp;<span class="required"></span></label>
            <div class="col-sm-9">
                <div class="checkbox">
                    <input type="checkbox" id="acceptpolicies_cancellationrightpolicy" name="acceptedpolicies_cancellationrightpolicy"{if $acceptedPolicies.cancellationRightPolicy} checked="checked"{/if} value="1" required="required" />
                    <em class="help-text">{gt text='Check this box to indicate your acceptance of this site\'s %1$s.' tag1=$policyLink}</em>
                </div>
                <p id="acceptpolicies_cancellationrightpolicy_error" class="alert alert-danger{if !isset($fieldErrors.cancellationrightpolicy) || empty($fieldErrors.cancellationrightpolicy)} hidden{/if}">
                    {$fieldErrors.cancellationrightpolicy|default:''|safetext}
                </p>
            </div>
        </div>
    {/if}
</fieldset>
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        $('.modal-body iframe').css('height', $(window).height());
    });
</script>