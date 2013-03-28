<?php

/**
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 3 of the License, or (at
 *   your option) any later version.
 *
 *   This program is distributed in the hope that it will be useful, but
 *   WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 *   General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with this program; if not, see <http://www.gnu.org/licenses/>.
 *
 *   @author          Black Cat Development
 *   @copyright       2013, Black Cat Development
 *   @link            http://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Modules
 *   @package         syncData
 *
 */

if (defined('CAT_PATH')) {
	if (defined('CAT_VERSION')) include(CAT_PATH.'/framework/class.secure.php');
} elseif (file_exists($_SERVER['DOCUMENT_ROOT'].'/framework/class.secure.php')) {
	include($_SERVER['DOCUMENT_ROOT'].'/framework/class.secure.php');
} else {
	$subs = explode('/', dirname($_SERVER['SCRIPT_NAME']));	$dir = $_SERVER['DOCUMENT_ROOT'];
	$inc = false;
	foreach ($subs as $sub) {
		if (empty($sub)) continue; $dir .= '/'.$sub;
		if (file_exists($dir.'/framework/class.secure.php')) {
			include($dir.'/framework/class.secure.php'); $inc = true;	break;
		}
	}
	if (!$inc) trigger_error(sprintf("[ %s ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
}

// ----- default config for Germany -----
define('sync_cfg_time_zone'                     , 'Europe/Berlin');
define('sync_cfg_datetime_str'                  , 'd.m.Y H:i');

define('sync_label_wb_path'                     , 'WB_PATH');
define('sync_label_wb_url'                      , 'WB_URL');


define('sync_label_cfg_auto_exec_msec',			'AutoExec in Millisekunden');
define('sync_label_cfg_filemtime_diff_allowed',	'Erlaubte Zeitdifferenz');
define('sync_label_cfg_ignore_directories',		'Ignorierte Verzeichnisse');
define('sync_label_cfg_ignore_file_extensions',	'Ignorierte Dateiendungen');
define('sync_label_cfg_ignore_tables',			'Ignorierte MySQL Tabellen');
define('sync_label_cfg_limit_execution_time',	'Limit Script Ausführungsdauer');
define('sync_label_cfg_memory_limit',			'Speicherbegrenzung');
define('sync_label_cfg_max_execution_time',		'Max. Script Ausführungsdauer');
define('sync_label_cfg_server_active',			'syncData Server');
define('sync_label_cfg_server_archive_id',		'Archiv ID für die Synchronisation');
define('sync_label_cfg_server_url',				'syncData Server URL');

define('sync_desc_cfg_auto_exec_msec',			'Die Wartezeit in Millisekunden, bis syncData einen unterbrochenen Prozess automatisch fortsetzt. Beträgt der Wert <span class="sync_data_highlight">0</span>, wird die automatische Fortsetzung ausgeschaltet. Der Standardwert ist <span class="sync_data_highlight">5000</span> Millisekunden.');
define('sync_desc_cfg_filemtime_diff_allowed',	'Erlaubte Abweichung beim <span class="sync_data_highlight">filemtime()</span> Vergleich in Sekunden, der Standardwert ist 1 Sekunde.');
define('sync_desc_cfg_limit_execution_time',	'Limit der Ausführungsdauer in Sekunden. Bei Erreichen des Wertes bricht das Script die Ausführung ab, um ein Überschreiten der <span class="sync_data_highlight">maximalen Ausführungsdauer</span> zu verhindern.');
define('sync_desc_cfg_max_execution_time',		'Maximale Ausführungsdauer der Scripts in Sekunden. Der Standardwert beträgt 30 Sekunden');
define('sync_desc_cfg_memory_limit',			'Maximaler Speicher (RAM), der syncData für die Ausführung der Scripts zur Verfügung steht. Die Angabe erfolgt in <span class="sync_data_highlight">Bytes</span> als Integer Wert oder als <a href="http://it.php.net/manual/de/faq.using.php#faq.using.shorthandbytes" target="_blank">abgekürzter Byte-Wert</a>, z.B. "256M".');
define('sync_desc_cfg_ignore_directories',		'Verzeichnisse, die von syncData grundsätzlich ignoriert werden sollen.');
define('sync_desc_cfg_ignore_file_extensions',	'Dateien mit den angegebenen Endungen werden von syncData grundsätzlich ignoriert. Trennen Sie die Einträge mit einem Komma.');
define('sync_desc_cfg_ignore_tables',			'MySQL Tabellen, die von syncData grundsätzlich ignoriert werden sollen. Achten Sie darauf, dass Sie die Tabellen <span class="sync_data_highlight">ohne TABLE_PREFIX</span> (lep_, wb_ o.ä.) angeben.');
define('sync_desc_cfg_server_active',			'Geben Sie diese syncData Installation als Server frei, wenn sich andere syncData Clients mit dieser Installation synchronisieren sollen.<br />0 = Server AUS, 1 = Server EIN');
define('sync_desc_cfg_server_archive_id',		'Wählen Sie die <span class="sync_data_highlight">ID</span> des Sicherungsarchiv aus, das für eine Synchronisation verwendet werden soll.');
define('sync_desc_cfg_server_url',				'Wenn Sie diese syncData Installation als <span class="sync_data_highlight">Client</span> verwenden, geben Sie hier die vollständige URL des syncData <span class="sync_data_highlight">Server</span> an.');




$LANG = array(
    // installation
    'The Droplets for %s were successfully installed. You will find further informations about the use of Droplets in the dokumentation!'
        => 'Die Droplets fuer %s wurden erfolgreich installiert. Informationen zur Verwendung der Droplets finden Sie in der Dokumentation!',
    'The installation of the Droplets is unfortunately failed for %s - Error message: %s'
        => 'Die Installation der Droplets fuer %s ist leider fehlgeschlagen, Fehlermeldung: %s',
    // sync types
    'complete (database and files)' => 'Vollständig (Datenbank und Dateien)',
    'only database (MySQL)' => 'nur die MySQL Datenbank',
    'only files' => 'nur die Dateien',
    // tabs
    'About' => 'Über',
    'Backup' => 'Sichern',
    'Settings' => 'Einstellungen',
    'Restore' => 'Restaurieren',
    // buttons
    'Cancel' => 'Abbruch',
    'Apply' => 'Übernehmen',
    'Continue ...' => 'Fortsetzen ...',
    'Start ...' => 'Starten ...',
    // Label
    'Select backup' => 'Backup auswählen',
    'Select backup type' => 'Sicherungstyp auswählen',
    'Name of the archive' => 'Name für das Archiv',
    'Choose a restore!' => 'Rücksicherung auswählen',
    'Archive ID' => 'Archiv ID',
    'Archive information' => 'Archiv Information',
    'Archive number' => 'Archiv Nummer',
    'Archive type' => 'Archiv Typ',
    'Status' => 'Status',
    'Restore' => 'Restaurieren',
    'Delete' => 'Löschen',
    'delete existing files which are not included in the archive' => 'vorhandene Dateien löschen, die nicht im Archiv enthalten sind',
    'delete existing tables which are not included in the archive' => 'vorhandene Tabellen löschen, die nicht im Archiv enthalten sind',
    'Ignore' => 'Ignorieren',
    'config.php' => 'config.php',
    '.htaccess' => '.htaccess',
    'Mode' => 'Modus',
    'replace changed tables and files (check date & size)' => 'geänderte Tabellen und Dateien ersetzen (Datum & Größe prüfen)',
    'replace changed tables and files (binary comparison, <i>very slow!</i>)' => 'geänderte Tabellen und Dateien ersetzen (binärer Vergleich, <i>sehr langsam!</i>)',
    'replace all tables and files' => 'alle Tabellen und Dateien ersetzen',
    'Search & Replace' => 'Suchen & Ersetzen',
    'update TABLE_PREFIX in MySQL tables' => 'TABLE_PREFIX in MySQL Tabellen aktualisieren',
    'update Base URL in MySQL tables' => 'Basis-URL in MySQL Tabellen aktualisieren',
    'Choose a restore!' => 'Rücksicherung auswählen',
    'MySQL tables' => 'MySQL Tabellen',
    'Files' => 'Dateien',

    // header
    'Backup of data' => 'Datensicherung',
    'Continue the backup of data ...' => 'Datensicherung fortsetzen',
    'Backup of data finished!' => 'Datensicherung beendet',
    'Create a new backup of data' => 'Neue Datensicherung erstellen',
    'Update the backup of data' => 'Datensicherung aktualisieren',
    'Start restore' => 'Rücksicherung durchführen',
    'Continue the restore ...' => 'Rücksicherung fortsetzen',
    'Restore finished!' => 'Rücksicherung beendet',
    'Settings' => 'Einstellungen',
    'Explanation' => 'Erläuterung',
    'Setting' => 'Einstellung',
    'Value' => 'Wert',
    'Continue the update ...' => 'Aktualisierung fortsetzen',
    'Update finished' => 'Aktualisierung beendet',
    // hint
    'Choose type of backup' => 'Wählen Sie den gewünschten Backup-Typ',
    'Give the archive a name' => 'Geben Sie dem Archiv einen Namen',

    // errors
    '<p>The system got no backup of data that could be recovered.</p>'
        => '<p>Es wurde keine Datensicherung übergeben, die wiederhergestellt werden kann.</p>',
    '<p>The directory <span class="sync_data_highlight">%s</span> could not be created!</p>'
        => '<p>Das Verzeichnis <span class="sync_data_highlight">%s</span> konnte nicht angelegt werden!</p>',
    '<p>The archive <span class="sync_data_highlight">%s</span> is not a valid syncData archive - missing file <span class="sync_data_highlight">sync_data.ini</span>!</p>'
        => '<p>Das Archiv <span class="sync_data_highlight">%s</span> ist kein gültiges syncData Archiv, die Datei <span class="sync_data_highlight">sync_data.ini</span> fehlt.</p>',
    '<p>The archive <span class="sync_data_highlight">%s</span> is not a valid syncData archive - missing file <span class="sync_data_highlight">sync_data.ini</span>!</p>'
        => '<p>Das Archiv <span class="sync_data_highlight">%s</span> ist kein gültiges syncData Archiv, die Datei <span class="sync_data_highlight">sync_data.ini</span> fehlt.</p>',
    '<p>Error writing file <span class="sync_data_highlight">%s</span>.</p>'
        => '<p>Fehler beim Schreiben in die Datei <span class="sync_data_highlight">%s</span>.</p>',
    "<p>The file <span class=\"sync_data_highlight\">%s</span> doesn't exist!</p>"
        => '<p>Die Datei <span class="sync_data_highlight">%s</span> existiert nicht!</p>',
    '<p>The file <span class="sync_data_highlight">%s</span> couldn\'t be read!</p>'
        => '<p>Die Datei <span class="sync_data_highlight">%s</span> konnte nicht gelesen werden!</p>',
    '<p>There was no valid backup archive specified!</p>'
        => '<p>Es wurde kein gültiges Backup Archiv angegeben!</p>',
    '<p>Unknown status. Please contact the support.</p>'
        => '<p>Unbekannter Status. Bitte informieren Sie den Support.</p>',
    '<p>Can not find a job with the synData ID <span class="sync_data_highlight">%s</span>!</p>'
        => '<p>Es wurde kein syncData Job mit der ID <span class="sync_data_highlight">%s</span> gefunden!</p>',
    '<p>The file list contains no files for a restore!</p>'
        => '<p>Die Dateiliste enthält keine Dateien für ein Restore!</p>',
    '<p>The file list doesn\'t contain MySQL files!</p>'
        => '<p>Die Dateiliste enthält keine MySQL Dateien!</p>',

    // intro
    '<p>Create a new backup or select a backup which will be updated.</p>' => '<p>Erstellen Sie ein neues Backup oder wählen Sie ein Backup aus, das aktualisiert werden soll.</p>',
    '<p>Select the type of data backup and give the archive a name.</p>' => '<p>Wählen Sie die Art der Datensicherung aus und geben Sie dem Archiv einen Namen.</p>',
    '<p>Check that the correct backup archive will be updated and give the update archive a name.</p>' => '<p>Prüfen Sie, ob das richtige Backup Archiv aktualisiert wird und geben Sie dem Update Archiv einen Namen.</p>',
    '<p>Edit the settings for <span class="sync_data_highlight">%s</span>.</p>' => '<p>Bearbeiten Sie die Einstellungen für <span class="sync_data_highlight">%s</span>.</p>',
    '<p>Select the backup from which will be used for data recovery.</p>' => '<p>Wählen Sie die Datensicherung aus, die für die Herstellung von Daten verwendet werden soll.</p>',
    '<p>Please check! Is the selected backup of data right one -  should it be restored?</p><p>Define the settings for restore and then start the process.</p>' => '<p>Bitte prüfen Sie, ob es sich um die gewünschte Datensicherung handelt.</p><p>Legen Sie die Art der Rücksicherung fest und starten Sie danach den Restore.</p>',
    '<p>Edit the settings for <span class="sync_data_highlight">%s</span>.</p>' => '<p>Bearbeiten Sie die Einstellungen für <span class="sync_data_highlight">%s</span>.</p>',

    // message
    '<p>There is nothing to do - task completed.</p>' => '<p>Es gibt nichts zu tun, Aktion beendet.</p>',
    '<p>The backup runs.</p><p>Please don\'t close this window and <span class="sync_data_highlight">wait for the status message by syncData you will get after max. %s seconds!</span></p>'
        => '<p>Die Datensicherung wird ausgeführt.</p><p>Schliessen Sie dieses Fenster nicht und <span class="sync_data_highlight">warten Sie die Statusmeldung durch syncData nach max. %s Sekunden ab</span>.</p>',
    '<p>No backups were found in the directory <span class="sync_data_highlight">%s</span>, which can be used for a restore.</p><p></p>Transfer the archive files manually via FTP to the directory <span class="sync_data_highlight">%s</span> and you call this dialogue again.</p>'
        => '<p>Im Verzeichnis <span class="sync_data_highlight">%s</span> wurden keine Backups gefunden, die für eine Rücksicherung verwendet werden können.</p><p></p>Übertragen Sie die Archivdateien, die verwendet werden sollen, per FTP in das Verzeichnis <span class="sync_data_highlight">%s</span> und rufen Sie diesen Dialog anschließend erneut auf.</p>',
    '<p style="color:red;"><em>AutoExec is active. The process will continue automatically in %d milliseconds.</em></p>'
        => '<p style="color:red;"><em>AutoExec ist aktiv, der Vorgang wird in %d Millisekunden automatisch fortgesetzt.</em></p>',
    '<p>The update isn\'t complete because not all files could be secured within the maximum execution time for PHP scripts from <span class="sync_data_highlight">%s seconds</span>.</p><p>Until now, <span class="sync_data_highlight">%s</span> files updated with a circumference of <span class="sync_data_highlight">%s</span>.</p><p>Please click "Continue ..." to proceed the update.</p>%s'
        => '<p>Die Aktualisierung konnte nicht abgeschlossen werden, da nicht alle Dateien innerhalb der maximalen Ausführungszeit für PHP Scripte von <span class="sync_data_highlight">%s Sekunden</span> gesichert werden konnten.</p><p>Bis jetzt wurden <span class="sync_data_highlight">%s</span> Dateien mit einem Umfang von <span class="sync_data_highlight">%s</span> aktualisiert.</p><p>Bitte klicken Sie auf "Fortsetzen ..." um die Aktualisierung fortzusetzen.</p>%s',
    '<p>The backup was completed successfully.</p><p>There were <span class="sync_data_highlight">%s</span> files backed up with a circumference of <span class="sync_data_highlight">%s</span>.</p><p>See the full archive:<br /><a href="%s">%s</a>.'
        => '<p>Die Datensicherung wurde erfolgreich abgeschlossen.</p><p>Es wurden <span class="sync_data_highlight">%s</span> Dateien mit einem Umfang von <span class="sync_data_highlight">%s</span> gesichert.</p><p>Sie finden das vollständige Archiv unter:<br /><a href="%s">%s</a>.',
    '<p>The data restore runs.</p><p>Please don\'t close this window and <span class="sync_data_highlight">wait for the status message by syncData you will get after max. %s seconds!</span></p>'
        => '<p>Die Datenwiederherstellung wird ausgeführt.</p><p>Schliessen Sie dieses Fenster nicht und <span class="sync_data_highlight">warten Sie die Statusmeldung durch syncData nach max. %s Sekunden ab</span>.</p>',
    '<p>The data restore is complete.</p><p>tables:<br /><ul><li>deleted: %d (%s)</li><li>added: %d (%s)</li><li>changed: %d (%s)</li></ul></p><p>files:<br /><ul><li>deleted: %d (%s)</li><li>added: %d (%s)</li><li>changed: %d (%s)</li></ul></p>'
        => '<p>Die Datenwiederherstellung ist abgeschlossen.</p><p>Tabellen:<br /><ul><li>gelöscht: %d (%s)</li><li>hinzugefügt: %d (%s)</li><li>geändert: %d (%s)</li></ul></p><p>Dateien:<br /><ul><li>gelöscht: %d (%s)</li><li>hinzugefügt: %d (%s)</li><li>geändert: %d (%s)</li></ul></p>',

// ----- protocol -----
    'The file %s was added.' => 'Die Datei %s wurde hinzugefügt.',
    'The file %s was deleted.' => 'Die Datei %s wurde gelöscht.',
    'The file %s has been replaced.' => 'Die Datei %s wurde ersetzt.',
    'The table %s was added.' => 'Die Tabelle %s wurde hinzugefügt.',
    'The table %s has been deleted' => 'Die Tabelle %s wurde gelöscht',
    'The table %s has been ignored.' => 'Die Tabelle %s wurde ignoriert.',
    'The table %s has been replaced.' => 'Die Tabelle %s wurde ersetzt.',


    '- create new backup -' => '- neues Backup erstellen -',
    'backup of data from %s' => 'Datensicherung vom %s',
    '- select restore -' => '- Rücksicherung auswählen -',
    '- undefined -' => '- nicht definiert -',
    'update from %s' => 'Aktualisierung vom %s',



);