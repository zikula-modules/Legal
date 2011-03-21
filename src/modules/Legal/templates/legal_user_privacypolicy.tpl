{configgetvar name='sitename' assign='sitename'}
{assign var='templatetitle' value='Privacy policy for '|cat:$sitename}
{include file=legal_user_menu.tpl}
{include file=$languageCode|cat:'/legal_text_privacypolicy.tpl'}