    <div id="fc_lists_overview">
		<ul class="fc_group_list fc_settings_list" id="fc_list_overview" style="max-height: 451px;">
            {foreach $navigation nav}
            <li class="fc_setting_item fc_border fc_gradient1 fc_gradient_hover icon-{$nav.icon}{if $nav.active == 1} fc_active{/if}">
                <a href="{$nav.url}">{$nav.text}</a>
            </li>
            {/foreach}
		</ul>
	</div>
    <p class="submit_settings fc_gradient1">
		<strong>{$header}</strong>
	</p>

    <div class="fc_all_forms">
        {$content}
    </div>