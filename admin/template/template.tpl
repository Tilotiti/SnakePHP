{if $message}
    <h4 class="alert_{$message.type}">{$message.text}</h4>
{/if}
{include file="`$page->get('template')`.tpl"}