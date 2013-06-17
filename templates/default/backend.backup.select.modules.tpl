  <form name="{$form.name}" action="{$form.link}" method="post">
    <input type="hidden" name="{$form.action.name}" value="{$form.action.value}" />

    <div class="{if $is_intro == 1}intro{else}message{/if}">{$intro}<br />
    {translate('Modules printed in grey text color are marked as "part of the CMS Bundle" in the database, so they are treated as part of the bundle and not marked by default.')}</div>

    <table class="table_backend">
      <thead>
        <tr>
          <th style="width:34%;">{translate('Modules')}</th>
          <th style="width:33%;">{translate('Languages')}</th>
          <th style="width:33%;">{translate('Templates')}</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>
            {foreach $modules mod}
            <input type="checkbox" name="modules[]" id="module_{$mod.name}" value="{$mod.directory}"{if $mod.bundled == 'N'} checked="checked"{/if} />&nbsp;<span {if $mod.bundled == 'Y'} class="greyed"{/if}>{$mod.name}</span><br />
            {/foreach}
          </td>
          <td>
            {foreach $languages mod}
            <input type="checkbox" name="languages[]" id="language_{$mod.name}" value="{$mod.directory}"{if $mod.bundled == 'N'} checked="checked"{/if} />&nbsp;<span {if $mod.bundled == 'Y'} class="greyed"{/if}>{$mod.name}</span><br />
            {/foreach}
          </td>
          <td>
            {foreach $templates mod}
            <input type="checkbox" name="templates[]" id="template_{$mod.name}" value="{$mod.directory}"{if $mod.bundled == 'N'} checked="checked"{/if} />&nbsp;<span {if $mod.bundled == 'Y'} class="greyed"{/if}>{$mod.name}</span><br />
            {/foreach}
          </td>
        </tr>
        <tr>
          <td colspan="3"><input type="submit" value="{$form.btn.ok}" /></td>
        </tr>
      </tbody>
    </table>
  </form>
</div>
