  <form name="{$form.name}" action="{$form.link}" method="post">
    <input type="hidden" name="{$form.action.name}" value="{$form.action.value}" />

    <div class="{if $is_intro == 1}intro{else}message{/if}">{$intro}</div>
    <table width="100%">
      <colgroup>
        <col width="200" /> 
        <col width="*" />
        <col width="300" />
      </colgroup>
      <tr>
        <td>{$backup.label}</td>
        <td>
          <select name="{$backup.name}">
            {foreach $backup.options option}
            <option value="{$option.value}"{if $option.selected == 1} selected="selected"{/if} >{$option.text}</option>
            {/foreach}
          </select>
        </td>
        <td>{$backup.hint}</td>
      </tr>
      <tr><td colspan="2">&nbsp;</td></tr>
      <tr>
        <td>&nbsp;</td>
        <td colspan="2"><input type="submit" value="{$form.btn.ok}" /></td>
      </tr>
    </table>
  </form>
</div>
