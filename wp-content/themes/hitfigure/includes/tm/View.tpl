<span {if $id}id="{$id}"{/if} {$events->getAll()}>
{foreach from=$self item=child}{$child}{/foreach}
</span>