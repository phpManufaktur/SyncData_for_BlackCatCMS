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
	if (!$inc) trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
}

// ----- default config for Germany -----
define('sync_cfg_time_zone', 'Europe/Berlin');

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

define('sync_desc_cfg_auto_exec_msec',			'Die Wartezeit in Millisekunden, bis syncData einen unterbrochenen Prozess automatisch fortsetzt. Beträgt der Wert <b>0</b>, wird die automatische Fortsetzung ausgeschaltet. Der Standardwert ist <b>5000</b> Millisekunden.');
define('sync_desc_cfg_filemtime_diff_allowed',	'Erlaubte Abweichung beim <b>filemtime()</b> Vergleich in Sekunden, der Standardwert ist 1 Sekunde.');
define('sync_desc_cfg_limit_execution_time',	'Limit der Ausführungsdauer in Sekunden. Bei Erreichen des Wertes bricht das Script die Ausführung ab, um ein Überschreiten der <b>maximalen Ausführungsdauer</b> zu verhindern.');
define('sync_desc_cfg_max_execution_time',		'Maximale Ausführungsdauer der Scripts in Sekunden. Der Standardwert beträgt 30 Sekunden');
define('sync_desc_cfg_memory_limit',			'Maximaler Speicher (RAM), der syncData für die Ausführung der Scripts zur Verfügung steht. Die Angabe erfolgt in <b>Bytes</b> als Integer Wert oder als <a href="http://it.php.net/manual/de/faq.using.php#faq.using.shorthandbytes" target="_blank">abgekürzter Byte-Wert</a>, z.B. "256M".');
define('sync_desc_cfg_ignore_directories',		'Verzeichnisse, die von syncData grundsätzlich ignoriert werden sollen.');
define('sync_desc_cfg_ignore_file_extensions',	'Dateien mit den angegebenen Endungen werden von syncData grundsätzlich ignoriert. Trennen Sie die Einträge mit einem Komma.');
define('sync_desc_cfg_ignore_tables',			'MySQL Tabellen, die von syncData grundsätzlich ignoriert werden sollen. Achten Sie darauf, dass Sie die Tabellen <b>ohne TABLE_PREFIX</b> (lep_, wb_ o.ä.) angeben.');
define('sync_desc_cfg_server_active',			'Geben Sie diese syncData Installation als Server frei, wenn sich andere syncData Clients mit dieser Installation synchronisieren sollen.<br />0 = Server AUS, 1 = Server EIN');
define('sync_desc_cfg_server_archive_id',		'Wählen Sie die <b>ID</b> des Sicherungsarchiv aus, das für eine Synchronisation verwendet werden soll.');
define('sync_desc_cfg_server_url',				'Wenn Sie diese syncData Installation als <b>Client</b> verwenden, geben Sie hier die vollständige URL des syncData <b>Server</b> an.');




$LANG = array(
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

    // intro
    '<p>Create a new backup or select a backup which will be updated.</p>' => '<p>Erstellen Sie ein neues Backup oder wählen Sie ein Backup aus, das aktualisiert werden soll.</p>',
    '<p>Select the type of data backup and give the archive a name.</p>' => '<p>Wählen Sie die Art der Datensicherung aus und geben Sie dem Archiv einen Namen.</p>',
    '<p>Check that the correct backup archive will be updated and give the update archive a name.</p>' => '<p>Prüfen Sie, ob das richtige Backup Archiv aktualisiert wird und geben Sie dem Update Archiv einen Namen.</p>',
    '<p>Edit the settings for <span class="sync_data_highlight">%s</span>.</p>' => '<p>Bearbeiten Sie die Einstellungen für <span class="sync_data_highlight">%s</span>.</p>',
    '<p>Select the backup from which will be used for data recovery.</p>' => '<p>Wählen Sie die Datensicherung aus, die für die Herstellung von Daten verwendet werden soll.</p>',
    '<p>Please check! Is the selected backup of data right one -  should it be restored?</p><p>Define the settings for restore and then start the process.</p>' => '<p>Bitte prüfen Sie, ob es sich um die gewünschte Datensicherung handelt.</p><p>Legen Sie die Art der Rücksicherung fest und starten Sie danach den Restore.</p>',
    '<p>Edit the settings for <b>%s</b>.</p>' => '<p>Bearbeiten Sie die Einstellungen für <b>%s</b>.</p>',
    // message
    '<p>The backup runs.</p><p>Please don´t close this window and <b>wait for the status message by syncData you will get after max. %s seconds!</b></p>'
        => '<p>Die Datensicherung wird ausgeführt.</p><p>Schliessen Sie dieses Fenster nicht und <b>warten Sie die Statusmeldung durch syncData nach max. %s Sekunden ab</b>.</p>',
    '<p>No backups were found in the directory <span class="sync_data_highlight">%s</span>, which can be used for a restore.</p><p></p>Transfer the archive files manually via FTP to the directory <span class="sync_data_highlight">%s</span> and you call this dialogue again.</p>'
        => '<p>Im Verzeichnis <span class="sync_data_highlight">%s</span> wurden keine Backups gefunden, die für eine Rücksicherung verwendet werden können.</p><p></p>Übertragen Sie die Archivdateien, die verwendet werden sollen, per FTP in das Verzeichnis <span class="sync_data_highlight">%s</span> und rufen Sie diesen Dialog anschließend erneut auf.</p>',

    '- create new backup -' => '- neues Backup erstellen -',
    'backup of data from %s' => 'Datensicherung vom %s',
    '- select restore -' => '- Rücksicherung auswählen -',
    '- undefined -' => '- nicht definiert -',
    'update from %s' => 'Aktualisierung vom %s',



);