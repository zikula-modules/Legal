{adminheader}
<h3>
    <span class="fa fa-wrench"></span>&nbsp;{gt text="Settings"}
</h3>

<p class="alert alert-danger">{gt text='<strong>Important Usage Note</strong>: The provided legal statements are samples only.
    They need to be adapted to your specific needs and locales. You will find the content of the statements in files in the
    "/modules/Legal/Resources/views/en" directory. These templates can be
    <a href="http://community.zikula.org/index.php?module=Wiki&tag=TemplateOverridng">overridden</a> by theme templates,
    or by global templates you would create in the "config/templates/legal/" directory (in the appropriate sub-directory
    for the language you are writing for).'}</p>
<form id="legal_config" class="form-horizontal" role="form" action="{modurl modname=$module type="admin" func="updateconfig"}" method="post" enctype="application/x-www-form-urlencoded">
    <div>
        <input type="hidden" name="csrftoken" value="{insert name='csrftoken'}" />
        <fieldset>
            <legend>{gt text="Legal document types"}</legend>
            <div class="form-group">
                {assign var='fieldName' value='Zikula\LegalModule\Constant::MODVAR_LEGALNOTICE_ACTIVE'|constant}
                <div class="col-lg-3 control-label">
                    <label for="legal_{$fieldName}">{gt text="Legal notice"}</label>
                </div>
                <div class="col-lg-9">
                    <div class="checkbox">
                        <input id="legal_{$fieldName}" name="{$fieldName}" type="checkbox" value="1"{if $modvars.$module.$fieldName == 1} checked="checked"{/if} />
                    </div>
                </div>
            </div>
            <div class="form-group">
                {assign var='fieldName' value='Zikula\LegalModule\Constant::MODVAR_TERMS_ACTIVE'|constant}
                <div class="col-lg-3 control-label">
                    <label for="legal_{$fieldName}">{gt text="Terms of use"}</label>
                </div>
                <div class="col-lg-9">
                    <div class="checkbox">
                        <input id="legal_{$fieldName}" name="{$fieldName}" type="checkbox" value="1"{if $modvars.$module.$fieldName == 1} checked="checked"{/if} />
                    </div>
                </div>
            </div>
            <div class="form-group">
                {assign var='fieldName' value='Zikula\LegalModule\Constant::MODVAR_PRIVACY_ACTIVE'|constant}
                <div class="col-lg-3 control-label">
                    <label for="legal_{$fieldName}">{gt text="Privacy policy"}</label>
                </div>
                <div class="col-lg-9">
                    <div class="checkbox">
                        <input id="legal_{$fieldName}" name="{$fieldName}" type="checkbox" value="1"{if $modvars.$module.$fieldName == 1} checked="checked"{/if} />
                    </div>
                </div>
            </div>
            <div class="form-group">
                {assign var='fieldName' value='Zikula\LegalModule\Constant::MODVAR_TRADECONDITIONS_ACTIVE'|constant}
                <div class="col-lg-3 control-label">
                    <label for="legal_{$fieldName}">{gt text="General Terms and Conditions of Trade"}</label>
                </div>
                <div class="col-lg-9">
                    <div class="checkbox">
                        <input id="legal_{$fieldName}" name="{$fieldName}" type="checkbox" value="1"{if $modvars.$module.$fieldName == 1} checked="checked"{/if} />
                    </div>
                </div>
            </div>
            <div class="form-group">
                {assign var='fieldName' value='Zikula\LegalModule\Constant::MODVAR_CANCELLATIONRIGHTPOLICY_ACTIVE'|constant}
                <div class="col-lg-3 control-label">
                    <label for="legal_{$fieldName}">{gt text="Cancellation Right Policy"}</label>
                </div>
                <div class="col-lg-9">
                    <div class="checkbox">
                        <input id="legal_{$fieldName}" name="{$fieldName}" type="checkbox" value="1"{if $modvars.$module.$fieldName == 1} checked="checked"{/if} />
                    </div>
                </div>
            </div>
            <div class="form-group">
                {assign var='fieldName' value='Zikula\LegalModule\Constant::MODVAR_ACCESSIBILITY_ACTIVE'|constant}
                <div class="col-lg-3 control-label">
                    <label for="legal_{$fieldName}">{gt text="Accessibility statement"}</label>
                </div>
                <div class="col-lg-9">
                    <div class="checkbox">
                        <input id="legal_{$fieldName}" name="{$fieldName}" type="checkbox" value="1"{if $modvars.$module.$fieldName == 1} checked="checked"{/if} />
                    </div>
                </div>
            </div>
        </fieldset>
        <fieldset>
            <legend>{gt text="Custom urls"}</legend>
            <p>{gt text='The following fields allow to reference any custom url. As soon as an url is given it will be used instead of the normal Legal templates. So you can now use any page you want for displaying and managing your legal data.'}</p>
            <div class="form-group">
                {assign var='fieldName' value='Zikula\LegalModule\Constant::MODVAR_LEGALNOTICE_URL'|constant}
                <div class="col-lg-3 control-label">
                    <label for="legal_{$fieldName}">{gt text="Legal notice"}</label>
                </div>
                <div class="col-lg-9">
                    <input id="legal_{$fieldName}" class="form-control" name="{$fieldName}" type="text" value="{$modvars.$module.$fieldName}" />
                </div>
            </div>
            <div class="form-group">
                {assign var='fieldName' value='Zikula\LegalModule\Constant::MODVAR_TERMS_URL'|constant}
                <div class="col-lg-3 control-label">
                    <label for="legal_{$fieldName}">{gt text="Terms of use"}</label>
                </div>
                <div class="col-lg-9">
                    <input id="legal_{$fieldName}" class="form-control" name="{$fieldName}" type="text" value="{$modvars.$module.$fieldName}" />
                </div>
            </div>
            <div class="form-group">
                {assign var='fieldName' value='Zikula\LegalModule\Constant::MODVAR_PRIVACY_URL'|constant}
                <div class="col-lg-3 control-label">
                    <label for="legal_{$fieldName}">{gt text="Privacy policy"}</label>
                </div>
                <div class="col-lg-9">
                    <input id="legal_{$fieldName}" class="form-control" name="{$fieldName}" type="text" value="{$modvars.$module.$fieldName}" />
                </div>
            </div>
            <div class="form-group">
                {assign var='fieldName' value='Zikula\LegalModule\Constant::MODVAR_TRADECONDITIONS_URL'|constant}
                <div class="col-lg-3 control-label">
                    <label for="legal_{$fieldName}">{gt text="General Terms and Conditions of Trade"}</label>
                </div>
                <div class="col-lg-9">
                    <input id="legal_{$fieldName}" class="form-control" name="{$fieldName}" type="text" value="{$modvars.$module.$fieldName}" />
                </div>
            </div>
            <div class="form-group">
                {assign var='fieldName' value='Zikula\LegalModule\Constant::MODVAR_CANCELLATIONRIGHTPOLICY_URL'|constant}
                <div class="col-lg-3 control-label">
                    <label for="legal_{$fieldName}">{gt text="Cancellation Right Policy"}</label>
                </div>
                <div class="col-lg-9">
                    <input id="legal_{$fieldName}" class="form-control" name="{$fieldName}" type="text" value="{$modvars.$module.$fieldName}" />
                </div>
            </div>
            <div class="form-group">
                {assign var='fieldName' value='Zikula\LegalModule\Constant::MODVAR_ACCESSIBILITY_URL'|constant}
                <div class="col-lg-3 control-label">
                    <label for="legal_{$fieldName}">{gt text="Accessibility statement"}</label>
                </div>
                <div class="col-lg-9">
                    <input id="legal_{$fieldName}" class="form-control" name="{$fieldName}" type="text" value="{$modvars.$module.$fieldName}" />
                </div>
            </div>
        </fieldset>
        <fieldset>
            <legend>{gt text="Further settings"}</legend>
            <div class="form-group">
                {assign var='fieldName' value='Zikula\LegalModule\Constant::MODVAR_MINIMUM_AGE'|constant}
                <div class="col-lg-3 control-label">
                    <label for="legal_{$fieldName}">{gt text="Minimum age permitted to register"}<span class="z-form-mandatory-flag">{gt text="*"}</span></label>
                </div>
                <div class="col-lg-9">
                    <input id="legal_{$fieldName}" class="form-control{if isset($errorFields.legal_minage)} z-form-error{/if}" type="text" name="{'Zikula\LegalModule\Constant::MODVAR_MINIMUM_AGE'|constant}" value="{$modvars.$module.$fieldName|safetext}" size="2" maxlength="2" />
                    <em class="z-formnote z-sub">{gt text="Enter a positive integer, or 0 for no age check."}</em>
                </div>
            </div>
            <div class="form-group">
                <div class="col-lg-3 control-label">
                    <label for="legal_resetagreement">{gt text="Reset user group's acceptance of 'Terms of use'"}</label>
                </div>
                <div class="col-lg-9">
                    <select id="legal_resetagreement" class="form-control" name="resetagreement">
                        {foreach item=group from=$groups}
                        <option value="{$group.gid|safetext}">{$group.name|safetext}</option>
                        {/foreach}
                    </select>
                    <p class="alert alert-info">{gt text="Notice: This setting resets the acceptance of the 'Terms of use' for all users in this group. Next time they want to log-in, they will have to acknowledge their acceptance of them again, and will not be able to log-in if they do not. This action does not affect the main administrator account. You can perform the same operation for individual users by visiting the Users manager in the site admin panel."}</p>
                </div>
            </div>
        </fieldset>
        <div class="col-lg-offset-3 col-lg-9">
            <button class="btn btn-success" type="submit" name="Save">{gt text="Save"}</button>
            <a class="btn btn-danger" href="{modurl modname=$module type='admin' func='main'}" title="{gt text="Cancel"}">{gt text="Cancel"}</a>
        </div>
    </div>
</form>

{adminfooter}
