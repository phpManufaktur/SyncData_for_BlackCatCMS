{*
 * syncData
 *
 * @author Ralf Hertsch (ralf.hertsch@phpmanufaktur.de)
 * @link http://phpmanufaktur.de
 * @copyright 2011
 * @license GNU GPL (http://www.gnu.org/licenses/gpl.html)
 * @version $Id: backend.about.lte 15 2011-08-26 14:01:12Z phpmanufaktur $
 *}
<div class="about">
  {if isset($img_url)}
  <div class="about_logo">
    <img src="{$img_url}" alt="syncData Logo" title="syncData (c) 2011 phpManufaktur" />
  </div>
  {/if}
  <div class="about_text">
    <h2>&nbsp;</h2>
    <p><strong>Release {$version}</strong> - &copy 2011 by phpManufaktur, Ralf Hertsch (Berlin)</p>
    <p class="about_address"><strong>phpManufaktur</strong><br />Ralf Hertsch<br />Stockholmer Str. 31<br />13359 Berlin</p>
    <p class="about_contact">
      <a href="http://phpmanufaktur.de" target="_blank">http://phpManufaktur.de</a><br />
      <a href="mailto:ralf.hertsch@phpmanufaktur.de">ralf.hertsch@phpManufaktur.de</a><br />
      phone +49 (0)30 68813647
    </p>
    <div class="about_release">
      <p><strong>History:</strong></p>
      <pre>{$release_notes}</pre>
    </div>
  </div>
</div>