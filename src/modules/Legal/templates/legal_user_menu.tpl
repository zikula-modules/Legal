{if $templatetitle|default:'' eq ''}
    {gt text='Legal information' assign='templatetitle'}
{/if}
{pagesetvar name='title' value=$templatetitle}

<h2>{$templatetitle}</h2>

{modulelinks modname=$module type='user'}

{insert name='getstatusmsg'}
