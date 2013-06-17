{*
 * syncData
 *
 * @author Ralf Hertsch (ralf.hertsch@phpmanufaktur.de)
 * @link http://phpmanufaktur.de
 * @copyright 2011
 * @license GNU GPL (http://www.gnu.org/licenses/gpl.html)
 * @version $Id: backend.restore.archive.info.lte 15 2011-08-26 14:01:12Z phpmanufaktur $
 *}
<div id="sync_data_restore">
  {if isset($text_process)}
  <div id="process"> 
    <div id="process_left">
      <img src="{$img_url}inProgress.gif" widht="36" height="36" alt="running..." />
    </div>
    <div id="process_right">
      {$text_process}
    </div>
  </div>
  {/if}
  <div id="restore_form">
  <form name="{$form.name}" action="{$form.link}" method="post" onsubmit="document.getElementById('process').style.display='block'; document.getElementById('restore_form').style.display='none';return true;">
    <input type="hidden" name="{$form.action.name}" value="{$form.action.value}" />
    <input type="hidden" name="{$form.restore.name}" value="{$form.restore.value}" />
    <h2>{$head}</h2>
    <div id="intro" class="{if $is_intro == 1}intro{else}message{/if}">{$intro}</div>
    <table width="100%">
      <colgroup>
        <col width="200" /> 
        <col width="*" />
        <col width="200" />
      </colgroup>
      <tr>
        <td>{$info.label}</td>
        <td colspan="2">
          <table width="100%">
            <colgroup>
              <col width="30%" />
              <col width="70%" />
            </colgroup>
            {foreach $info.values value}
            <tr>
              <td>{$value.label}</td>
              <td>{$value.text}</td>
            </tr>
            {/foreach}
          </table>
        </td>
      </tr>
      <tr><td colspan="3">&nbsp;</td></tr>
      {$i=0}{foreach $restore.select.select value}
      <tr>
        <td>{if $i == 0}{$restore.select.label}{/if}{$i=$i+1}</td>
        <td><input type="checkbox" name="{$value.name}[]" value="{$value.value}" {if $value.enabled == 1}{if $value.checked == 1} checked="checked"{/if}{else} disabled="disabled"{/if} /> {$value.text}</td>
        <td> </td>
      </tr>
      {/foreach}
      {$i=0}{foreach $restore.mode.select value}
      <tr>
        <td>{if $i == 0}{$restore.mode.label}{/if}{$i=$i+1}</td>
        <td><input type="radio" name="{$value.name}" value="{$value.value}" {if $value.checked == 1} checked="checked"{/if} /> {$value.text}</td>
        <td> </td>
      </tr>
      {/foreach}
      {$i=0}{foreach $restore.replace replace}
      <tr>
        <td>{if $i == 0}{$replace.label}{$i=1}{/if}</td>
        <td><input type="checkbox" name="{$replace.name}" value="1" {if $replace.checked == 1} checked="checked"{/if} /> {$replace.text}</td>
        <td> </td>
      </tr>
      {/foreach}
      {$i=0}{foreach $restore.ignore ignore}
      <tr>
        <td>{if $i == 0}{$ignore.label}{$i=1}{/if}</td>
        <td><input type="checkbox" name="{$ignore.name}" value="1" {if $ignore.checked == 1} checked="checked"{/if} /> {$ignore.text}</td>
        <td> </td>
      </tr>
      {/foreach}
      {$i=0}{foreach $restore.delete delete}
      <tr>
        <td>{if $i == 0}{$delete.label}{$i=1}{/if}</td>
        <td><input type="checkbox" name="{$delete.name}" value="1" {if $delete.enabled == 1}{if $delete.checked == 1} checked="checked"{/if}{else} disabled="disabled"{/if} /> {$delete.text}</td>
        <td> </td>
      </tr>
      {/foreach}
      <tr><td colspan="3">&nbsp;</td></tr>
      <tr>
        <td>&nbsp;</td>
        <td colspan="2">
          <input type="submit" value="{$form.btn.ok}" />&nbsp;
          <input type="button" value="{$form.btn.abort}" onclick="javascript: window.location = '{$form.link}'; return false;" />
        </td>
      </tr>
    </table>
  </form>
  </div>
</div>
