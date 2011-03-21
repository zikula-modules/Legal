<span class="{$class|default:'z-menuitem-title'}">{if ($policies.termsofuse || $policies.privacypolicy || $policies.accessibilitystatement)}{$start|default:'['}{/if}
    {if $policies.termsofuse}<a href="{modurl modname='Legal::MODNAME'|constant type='User' func='termsOfUse'}">{gt text='Terms of use'}</a>{/if}
    {if $policies.termsofuse && ($policies.privacypolicy || $policies.accessibilitystatement)}{$seperator|default:'|'}{/if}
    {if $policies.privacypolicy}<a href="{modurl modname='Legal::MODNAME'|constant type='User' func='privacyPolicy'}">{gt text='Privacy policy'}</a>{/if}
    {if ($policies.termsofuse || $policies.privacypolicy) && $policies.accessibilitystatement}{$seperator|default:'|'}{/if}
    {if $policies.accessibilitystatement}<a href="{modurl modname='Legal::MODNAME'|constant type='User' func='accessibilityStatement'}">{gt text='Accessibility statement'}</a>{/if}
    {if ($policies.termsofuse || $policies.privacypolicy || $policies.accessibilitystatement)}{$end|default:']'}{/if}
</span>