<?php
/**
 * syncData
 * 
 * @author Ralf Hertsch (ralf.hertsch@phpmanufaktur.de)
 * @link http://phpmanufaktur.de
 * @copyright 2011
 * @license GNU GPL (http://www.gnu.org/licenses/gpl.html)
 * @version $Id$
 */ 

define('sync_label_cfg_auto_exec_msec',			'AutoExec in milliseconds');
define('sync_label_cfg_filemtime_diff_allowed',	'Allowed time difference');
define('sync_label_cfg_ignore_directories',		'Ignored directories');
define('sync_label_cfg_ignore_file_extensions',	'Ignored file extensions');
define('sync_label_cfg_ignore_tables',			'Ignored MySQL tables');
define('sync_label_cfg_limit_execution_time',	'Limit of execution time (script)');
define('sync_label_cfg_memory_limit',			'Memory limit');
define('sync_label_cfg_max_execution_time',		'Max execution time (script)');
define('sync_label_cfg_server_active',			'syncData Server');
define('sync_label_cfg_server_archive_id',		'Archive ID for synchronization');
define('sync_label_cfg_server_url',				'syncData Server URL');

define('sync_desc_cfg_auto_exec_msec',			'The waiting time in milliseconds until syncData an interrupted process automatically continues. If the value is <b>0</b>, the automatic continuation switched off. The default value is <b>5000</b> milliseconds.');
define('sync_desc_cfg_filemtime_diff_allowed',	'The tolerance for <b>filemtime()</b> comparison in seconds. The default value is 1 second.');
define('sync_desc_cfg_limit_execution_time',	'The limit of script execution in seconds. At reaching the value script execution will be stopped to avoid the maximum execution time.');
define('sync_desc_cfg_max_execution_time',		'Maximum execution time of scripts in seconds. The default value is 30 seconds.');
define('sync_desc_cfg_memory_limit',			'Maximum memory (RAM) for syncData, that can be used for the execution of scripts. The values are stated in <b>bytes</b>, as integer value or <a href="http://it.php.net/manual/de/faq.using.php#faq.using.shorthandbytes" target="_blank">abbreviated byte value</a>, for example "256M".');
define('sync_desc_cfg_ignore_directories',		'Directories which are to be absolutly ignored by syncdata.');
define('sync_desc_cfg_ignore_file_extensions',	'Files with specified extensions are ignored by syncData principle. Separate entries with a comma.');
define('sync_desc_cfg_ignore_tables',			'MySQL tables that are to be absolutly ignored by syncData. Make sure that you use only tables <b>without TABLE_PREFIX</b> (lep_, wb_ etc.)!');
define('sync_desc_cfg_server_active',			'If you share this installation of syncData as a server, you are able to sync other syncData clients to this server.<br />0 = Server OFF, 1 = Server ON');
define('sync_desc_cfg_server_archive_id',		'Choose the <b>ID</b> from the backup archive which should be used for synchronization.');
define('sync_desc_cfg_server_url', 				'If you use this syncData installation as a <b>client</b>, enter the full URL of the syncData <b>server</b>.');
