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
 *   @author          Ralf Hertsch (ralf.hertsch@phpmanufaktur.de)
 *   @author          Black Cat Development
 *   @copyright       2013, Black Cat Development
 *   @link            http://phpmanufaktur.de
 *   @link            http://blackcat-cms.org
 *   @license         http://www.gnu.org/licenses/gpl.html
 *   @category        CAT_Modules
 *   @package         syncData
 *
 */

if (defined('CAT_PATH'))
{
    if (defined('CAT_VERSION'))
        include(CAT_PATH . '/framework/class.secure.php');
}
elseif (file_exists($_SERVER['DOCUMENT_ROOT'] . '/framework/class.secure.php'))
{
    include($_SERVER['DOCUMENT_ROOT'] . '/framework/class.secure.php');
}
else
{
    $subs = explode('/', dirname($_SERVER['SCRIPT_NAME']));
    $dir  = $_SERVER['DOCUMENT_ROOT'];
    $inc  = false;
    foreach ($subs as $sub)
    {
        if (empty($sub))
            continue;
        $dir .= '/' . $sub;
        if (file_exists($dir . '/framework/class.secure.php'))
        {
            include($dir . '/framework/class.secure.php');
            $inc = true;
            break;
        }
    }
    if (!$inc)
        trigger_error(sprintf("[ <b>%s</b> ] Can't include class.secure.php!", $_SERVER['SCRIPT_NAME']), E_USER_ERROR);
}

if (!class_exists('dbconnectle'))
{
    // load dbConnect_LE from include directory
    require_once dirname(__FILE__) . '/dbconnect_le/include.php';
}

if (!class_exists('kitToolsLibrary'))
{
    // load embedded kitTools library
    require_once dirname(__FILE__) . '/class.tools.php';
}

global $kitTools;
if (!is_object($kitTools))
    $kitTools = new kitToolsLibrary();

require_once dirname(__FILE__) . '/class.syncdata.php';
require_once CAT_PATH . '/framework/functions.php';
require_once dirname(__FILE__) . '/class.interface.php';

class syncBackend
{

    const request_action = 'act';
    const request_file_backup_start = 'bus';
    const request_items = 'its';
    const request_backup = 'bak';
    const request_restore = 'rst';
    const request_restore_continue = 'rstc';
    const request_restore_process = 'rstp';
    const request_restore_replace_url = 'rstru';
    const request_restore_replace_prefix = 'rstrp';
    const request_restore_type = 'rstt';

    const action_about = 'abt';
    const action_config = 'cfg';
    const action_config_check = 'cfgc';
    const action_default = 'def';
    const action_backup = 'back';
    const action_backup_start = 'baks';
    const action_backup_start_new = 'baksn';
    const action_backup_continue = 'bakc';
    const action_process_backup = 'pb';
    const action_restore = 'rst';
    const action_restore_continue = 'rstc';
    const action_restore_info = 'rsti';
    const action_restore_start = 'rsts';
    const action_update_continue = 'updc';
    const action_update_start = 'upds';

    private $tab_navigation_array = array(
        self::action_backup  => 'Backup',
        self::action_restore => 'Restore',
        self::action_config  => 'Settings',
        self::action_about   => 'About'
    );
    private $tab_navigation_icon_array = array(
        self::action_backup => 'database',
        self::action_restore => 'share',
        self::action_config => 'tools',
        self::action_about => 'info'
    );
    private $headers = array(
        self::action_backup => 'Backup of data',
        self::action_restore => 'Restore',
        self::action_config => 'Settings',
        self::action_about => 'About'
    );

    const add_max_rows = 5;

    private $page_link = '';
    private $img_url = '';
    private $template_path = '';
    private $error = '';
    private $message = '';
    private $temp_path = '';
    private $max_execution_time = 30;
    private $limit_execution_time = 25;
    private $memory_limit = '256M';
    private $next_file = '';

    /**
     * Constructor
     */
    public function __construct()
    {
        global $dbSyncDataCfg, $parser;
        $this->page_link     = CAT_ADMIN_URL . '/admintools/tool.php?tool=syncData';
        $this->template_path = sanitize_path(dirname(__FILE__) . '/../templates/default/');
        $this->img_url       = CAT_URL . '/modules/syncData/images/';
        date_default_timezone_set(sync_cfg_time_zone);
        $this->temp_path = sanitize_path(CAT_PATH . '/temp/syncData/');
        if (!file_exists($this->temp_path)) {
            mkdir($this->temp_path, 0755, true);
            $interface->createAccessFiles($this->temp_path);
        }
        $this->memory_limit = $dbSyncDataCfg->getValue(dbSyncDataCfg::cfgMemoryLimit);
        ini_set("memory_limit", $this->memory_limit);
        $this->max_execution_time   = $dbSyncDataCfg->getValue(dbSyncDataCfg::cfgMaxExecutionTime);
        $this->limit_execution_time = $dbSyncDataCfg->getValue(dbSyncDataCfg::cfgLimitExecutionTime);
        set_time_limit($this->max_execution_time);
        $parser->setPath($this->template_path);
    } // __construct()

    /**
     * Action handler of the class
     *
     * @access public
     * @return string - dialog or message
     */
    public function action()
    {
        $html_allowed = array();
        foreach ($_REQUEST as $key => $value)
        {
            if (!in_array($key, $html_allowed))
            {
                $_REQUEST[$key] = $this->xssPrevent($value);
            }
        }
        isset($_REQUEST[self::request_action]) ? $action = $_REQUEST[self::request_action] : $action = self::action_default;

        switch ($action):
            case self::action_about:
                $this->show(self::action_about, $this->dlgAbout());
                break;
            case self::action_config:
                $this->show(self::action_config, $this->dlgConfig());
                break;
            case self::action_config_check:
                $this->show(self::action_config, $this->checkConfig());
                break;
            case self::action_process_backup:
                $this->show(self::action_backup, $this->processBackup());
                break;
            case self::action_backup_start:
                $this->show(self::action_backup, $this->dlgBackupStart());
                break;
            case self::action_backup_start_new:
                $this->show(self::action_backup, $this->backupStartNewArchive());
                break;
            case self::action_backup_continue:
                $this->show(self::action_backup, $this->backupContinue());
                break;
            case self::action_restore:
                $this->show(self::action_restore, $this->dlgRestore());
                break;
            case self::action_restore_info:
                $this->show(self::action_restore, $this->restoreInfo());
                break;
            case self::action_restore_start:
                $this->show(self::action_restore, $this->restoreStart());
                break;
            case self::action_restore_continue:
                $this->show(self::action_restore, $this->restoreContinue());
                break;
            case self::action_update_start:
                $this->show(self::action_backup, $this->updateStart());
                break;
            case self::action_update_continue:
                $this->show(self::action_backup, $this->updateContinue());
                break;
            case self::action_default:
            default:
                $this->show(self::action_backup, $this->dlgBackup());
                break;
        endswitch;
    } // end function action()

    /**
     * Set $this->error to $error
     *
     * @access public
     * @param  string  $error
     * @return void
     */
    public function setError($error)
    {
        global $admin;
        $debug       = debug_backtrace();
        $caller      = next($debug);
        $this->error = sprintf('[%s::%s - %s] %s', basename($caller['file']), $caller['function'], $caller['line'], $admin->lang->translate($error));
    } // end function setError()

    /**
     * Get Error from $this->error;
     *
     * @access public
     * @return string
     */
    public function getError()
    {
        return $this->error;
    } // end function getError()

    /**
     * Check if $this->error is empty
     *
     * @access public
     * @return boolean
     */
    public function isError()
    {
        return (bool) !empty($this->error);
    } // end function isError

    /**
     * Reset Error to empty String
     *
     * @access public
     */
    public function clearError()
    {
        $this->error = '';
    }   // end function clearError()

    /**
     * Set $this->message to $message
     *
     * @access public
     * @param  string  $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    } // end function setMessage()

    /**
     * Get Message from $this->message;
     *
     * @access public
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    } // getMessage()

    /**
     * Check if $this->message is empty
     *
     * @access public
     * @return boolean
     */
    public function isMessage()
    {
        return (bool) !empty($this->message);
    } // end function isMessage

    /**
     * Return Version of Module
     *
     * @access public
     * @return FLOAT
     */
    public function getVersion()
    {
        // read info.php into array
        $info_text = file(sanitize_path(dirname(__FILE__).'/../info.php'));
        if ($info_text == false)
        {
            return -1;
        }
        // walk through array
        foreach ($info_text as $item)
        {
            if (strpos($item, '$module_version') !== false)
            {
                // split string $module_version
                $value = explode('=', $item);
                // return floatval
                return floatval(preg_replace('([\'";,\(\)[:space:][:alpha:]])', '', $value[1]));
            }
        }
        return -1;
    } // end function getVersion()

    /**
     * Get the desired $template within the template path, fills in the
     * $template_data and return the template output
     *
     * @access public
     * @param  string  $template
     * @param  array   $template_data
     * @return mixed   template or FALSE on error
     */
    public function getTemplate($template, $template_data)
    {
        global $parser;
        $result = '';
        try
        {
            $result = $parser->get($template, $template_data);
        }
        catch (Exception $e)
        {
            $this->setError(sprintf(sync_error_template_error, $template, $e->getMessage()));
            return false;
        }
        return $result;
    } // end function getTemplate()


    /**
     * protect against XSS Cross Site Scripting
     *
     * @access public
     * @param  REFERENCE $_REQUEST Array
     * @return $request
     */
    public function xssPrevent(&$request)
    {
        if (is_string($request))
        {
            $request = html_entity_decode($request);
            $request = strip_tags($request);
            $request = trim($request);
            $request = stripslashes($request);
        }
        return $request;
    } // end function xssPrevent()

    /**
     * Ausgabe des formatierten Ergebnis mit Navigationsleiste
     *
     * @access public
     * @param  string  $action  - aktives Navigationselement
     * @param  string  $content - Inhalt
     * @return ECHO RESULT
     */
    public function show($action, $content)
    {
        global $admin;
        $navigation = array();
        $header     = '';
        foreach ($this->tab_navigation_array as $key => $value)
        {
            $navigation[] = array(
                'active' => ($key == $action) ? 1 : 0,
                'url'    => sprintf('%s&%s=%s', $this->page_link, self::request_action, $key),
                'text'   => $admin->lang->translate($value),
                'icon'   => $this->tab_navigation_icon_array[$key]
            );
            $header = ($key == $action) ? $key : $header;
        }
        $data = array(
            'navigation' => $navigation,
            'error'      => ($this->isError()) ? 1 : 0,
            'content'    => ($this->isError()) ? $this->getError() : $content,
            'header'     => $admin->lang->translate($this->headers[$header]),
        );
        echo $this->getTemplate('backend.body.lte', $data);
        echo $this->getError();
    } // end function show()

    /**
     * About Dialog
     *
     * @access public
     * @return string  dialog
     */
    public function dlgAbout()
    {
        $notes = file_get_contents(sanitize_path(dirname(__FILE__).'/notes.txt'));
        $notes = preg_replace( '/(Release\s*\d+\.\d+)/i', '<strong>$1</strong>', $notes );
        $notes = preg_replace( '/(\d{4}-\d{2}-\d{2})/', '<span style="font-style:italic">$1</span>', $notes );
        $notes = str_replace( 'fixed:', '<span style="color:#f00;">fixed:</span>', $notes );
        $notes = str_replace( 'added:', '<span style="color:#00f;">added:</span>', $notes );
        $notes = str_replace( 'changed:', '<span style="color:#600;">changed:</span>', $notes );
        $data = array(
            'version' => sprintf('%01.2f', $this->getVersion()),
            'img_url' => $this->img_url . '/syncData_logo.png',
            'release_notes' => $notes,
        );
        return $this->getTemplate('backend.about.lte', $data);
    } // end function dlgAbout()


    /**
     * Dialog zur Konfiguration und Anpassung von syncData
     *
     * @access public
     * @return string  dialog
     */
    public function dlgConfig()
    {
        global $dbSyncDataCfg;
        global $dbSyncDataArchive;
        global $admin;

        $SQL    = sprintf("SELECT * FROM %s WHERE NOT %s='%s' ORDER BY %s", $dbSyncDataCfg->getTableName(), dbSyncDataCfg::field_status, dbSyncDataCfg::status_deleted, dbSyncDataCfg::field_name);
        $config = array();
        if (!$dbSyncDataCfg->sqlExec($SQL, $config))
        {
            $this->setError($dbSyncDataCfg->getError());
            return false;
        }
        $count  = array();
        $header = array(
            'identifier' => $admin->lang->translate('Setting'),
            'value' => $admin->lang->translate('Value'),
            'description' => $admin->lang->translate('Explanation')
        );

        $items = array();
        // bestehende Eintraege auflisten
        foreach ($config as $entry)
        {
            $id      = $entry[dbSyncDataCfg::field_id];
            $count[] = $id;
            $value   = ($entry[dbSyncDataCfg::field_type] == dbSyncDataCfg::type_list) ? $dbSyncDataCfg->getValue($entry[dbSyncDataCfg::field_name]) : $entry[dbSyncDataCfg::field_value];
            if (isset($_REQUEST[dbSyncDataCfg::field_value . '_' . $id]))
                $value = $_REQUEST[dbSyncDataCfg::field_value . '_' . $id];
            if ($entry[dbSyncDataCfg::field_name] == dbSyncDataCfg::cfgServerArchiveID)
            {
                // Archiv IDs auslesen
                $is_value = $value;
                $value    = array();
                $value[]  = array(
                    'value' => '',
                    'selected' => ($is_value == '') ? 1 : 0,
                    'text' => '- select -'
                );
                $where    = array(
                    dbSyncDataArchives::field_status => dbSyncDataArchives::status_active,
                    dbSyncDataArchives::field_archive_number => 1
                );
                $archives = array();
                if (!$dbSyncDataArchive->sqlSelectRecord($where, $archives))
                {
                    $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbSyncDataArchive->getError()));
                    return false;
                }
                foreach ($archives as $archive)
                {
                    $value[] = array(
                        'value' => $archive[dbSyncDataArchives::field_archive_id],
                        'selected' => ($is_value == $archive[dbSyncDataArchives::field_archive_id]) ? 1 : 0,
                        'text' => sprintf('[ %s ] %s', $archive[dbSyncDataArchives::field_archive_id], $archive[dbSyncDataArchives::field_archive_name])
                    );
                }
            }
            else
            {
                $value = str_replace('"', '&quot;', stripslashes($value));
            }
            $items[] = array(
                'id' => $id,
                'identifier' => constant($entry[dbSyncDataCfg::field_label]),
                'value' => $value,
                'name' => sprintf('%s_%s', dbSyncDataCfg::field_value, $id),
                'description' => constant($entry[dbSyncDataCfg::field_description]),
                'type' => $dbSyncDataCfg->type_array[$entry[dbSyncDataCfg::field_type]],
                'field' => $entry[dbSyncDataCfg::field_name]
            );
        }
        $data = array(
            'form_name' => 'flex_table_cfg',
            'form_action' => $this->page_link,
            'action_name' => self::request_action,
            'action_value' => self::action_config_check,
            'items_name' => self::request_items,
            'items_value' => implode(",", $count),
            'head' => $admin->lang->translate('Settings'),
            'intro' => $this->isMessage() ? $this->getMessage() : sprintf($admin->lang->translate('<p>Edit the settings for <b>%s</b>.</p>'), 'syncData'),
            'is_message' => $this->isMessage() ? 1 : 0,
            'items' => $items,
            'btn_ok' => $admin->lang->translate('Apply'),
            'btn_abort' => $admin->lang->translate('Cancel'),
            'abort_location' => $this->page_link,
            'header' => $header
        );
        return $this->getTemplate('backend.config.lte', $data);
    } // dlgConfig()

    /**
     * Ueberprueft Aenderungen die im Dialog dlgConfig() vorgenommen wurden
     * und aktualisiert die entsprechenden Datensaetze.
     *
     * @return STR DIALOG dlgConfig()
     */
    public function checkConfig()
    {
        global $dbSyncDataCfg;
        $message = '';
        // ueberpruefen, ob ein Eintrag geaendert wurde
        if ((isset($_REQUEST[self::request_items])) && (!empty($_REQUEST[self::request_items])))
        {
            $ids = explode(",", $_REQUEST[self::request_items]);
            foreach ($ids as $id)
            {
                if (isset($_REQUEST[dbSyncDataCfg::field_value . '_' . $id]))
                {
                    $value                          = $_REQUEST[dbSyncDataCfg::field_value . '_' . $id];
                    $where                          = array();
                    $where[dbSyncDataCfg::field_id] = $id;
                    $config                         = array();
                    if (!$dbSyncDataCfg->sqlSelectRecord($where, $config))
                    {
                        $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbSyncDataCfg->getError()));
                        return false;
                    }
                    if (sizeof($config) < 1)
                    {
                        $this->setError(sprintf(sync_error_cfg_id, $id));
                        return false;
                    }
                    $config = $config[0];
                    if ($config[dbSyncDataCfg::field_value] != $value)
                    {
                        // Wert wurde geaendert
                        if (!$dbSyncDataCfg->setValue($value, $id) && $dbSyncDataCfg->isError())
                        {
                            $this->setError($dbSyncDataCfg->getError());
                            return false;
                        }
                        elseif ($dbSyncDataCfg->isMessage())
                        {
                            $message .= $dbSyncDataCfg->getMessage();
                        }
                        else
                        {
                            // Datensatz wurde aktualisiert
                            $message .= sprintf(sync_msg_cfg_id_updated, $config[dbSyncDataCfg::field_name]);
                        }
                    }
                    unset($_REQUEST[dbSyncDataCfg::field_value . '_' . $id]);
                }
            }
        }
        $this->setMessage($message);
        return $this->dlgConfig();
    } // checkConfig()


    /**
     * Dialog: select existing or new backup
     *
     * @return STR dialog
     */
    public function dlgBackup()
    {
        global $dbSyncDataArchive, $admin;
        $SQL      = sprintf("SELECT * FROM %s WHERE %s='1' AND %s='%s'", $dbSyncDataArchive->getTableName(), dbSyncDataArchives::field_archive_number, dbSyncDataArchives::field_status, dbSyncDataArchives::status_active);
        $archives = array();
        if (!$dbSyncDataArchive->sqlExec($SQL, $archives))
        {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbSyncDataArchive->getError()));
            return false;
        }
        $select_array   = array();
        $select_array[] = array(
            'value' => -1,
            'selected' => 1,
            'text' => $admin->lang->translate('- create new backup -'),
        );
        foreach ($archives as $archive)
        {
            $select_array[] = array(
                'value' => $archive[dbSyncDataArchives::field_archive_id],
                'selected' => 0,
                'text' => sprintf('%s - %s', date(sync_cfg_datetime_str, strtotime($archive[dbSyncDataArchives::field_archive_date])), $archive[dbSyncDataArchives::field_archive_name])
            );
        }

        $data = array(
            'form' => array(
                'name' => 'backup_select',
                'link' => $this->page_link,
                'action' => array(
                    'name' => self::request_action,
                    'value' => self::action_backup_start
                ),
                'btn' => array(
                    'ok' => $admin->lang->translate('Apply')
                )
            ),
            'backup' => array(
                'name' => self::request_backup,
                'label' => $admin->lang->translate('Select backup'),
                'hint' => $admin->lang->translate(''),
                'options' => $select_array
            ),
            'head' => $admin->lang->translate('Datensicherung'),
            'is_intro' => $this->isMessage() ? 0 : 1,
            'intro' => $this->isMessage() ? $this->getMessage() : $admin->lang->translate('<p>Create a new backup or select a backup which will be updated.</p>'),
        );
        return $this->getTemplate('backend.backup.select.lte', $data);
    } // dlgBackup()

    /**
     * Dialog zur Auswahl eines neuen Backup oder zur Aktualisierung eines
     * bestehenden Backup
     *
     * @return STR dialog
     */
    public function dlgBackupStart()
    {
        global $dbSyncDataArchive;
        global $dbSyncDataFile;
        global $kitTools;
        global $dbSyncDataCfg;
        global $admin;

        $archiv_id = isset($_REQUEST[self::request_backup]) ? $_REQUEST[self::request_backup] : -1;

        if ($archiv_id == -1)
        {
            // neues Archiv anlegen
            $select_array = array();
            foreach ($dbSyncDataArchive->backup_type_array as $type)
            {
                $select_array[] = array(
                    'value' => $type['key'],
                    'text' => $admin->lang->translate($type['value']),
                    'selected' => ($type['key'] == dbSyncDataArchives::backup_type_complete) ? 1 : 0
                );
            }
            $data = array(
                'form' => array(
                    'name' => 'backup_start',
                    'link' => $this->page_link,
                    'action' => array(
                        'name' => self::request_action,
                        'value' => self::action_backup_start_new
                    ),
                    'btn' => array(
                        'ok' => $admin->lang->translate('Apply')
                    )
                ),
                'backup_type' => array(
                    'name' => dbSyncDataArchives::field_backup_type,
                    'label' => $admin->lang->translate('Select backup type'),
                    'hint' => $admin->lang->translate('Choose type of backup'),
                    'options' => $select_array
                ),
                'archive_name' => array(
                    'name' => dbSyncDataArchives::field_archive_name,
                    'value' => '',
                    'label' => $admin->lang->translate('Name of the archive'),
                    'hint' => $admin->lang->translate('Give the archive a name'),
                ),
                'head' => $admin->lang->translate('Neue Datensicherung erstellen'),
                'is_intro' => $this->isMessage() ? 0 : 1,
                'intro' => $this->isMessage() ? $this->getMessage() : $admin->lang->translate('<p>Select the type of data backup and give the archive a name.</p>'),
                'text_process' => sprintf($admin->lang->translate('<p>The backup runs.</p><p>Please don´t close this window and <b>wait for the status message by syncData you will get after max. %s seconds!</b></p>'), $dbSyncDataCfg->getValue(dbSyncDataCfg::cfgLimitExecutionTime)),
                'img_url' => $this->img_url
            );
            return $this->getTemplate('backend.backup.new.lte', $data);
        }
        else
        {
            /**
             * The backup archive should be updated
             */

            // first step: read the archive informations
            $where   = array(
                dbSyncDataArchives::field_archive_id => $archiv_id
            );
            $archive = array();
            if (!$dbSyncDataArchive->sqlSelectRecord($where, $archive))
            {
                $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbSyncDataArchive->getError()));
                return false;
            }
            if (count($archive) < 1)
            {
                $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(sync_error_archive_id_invalid, $archiv_id)));
                return false;
            }
            $archive = $archive[0];

            // second step: gather the informations about the archived tables and files
            $SQL   = sprintf("SELECT COUNT(%s) AS count, SUM(%s) AS bytes FROM %s WHERE %s='%s' AND %s='%s' AND %s='%s'", dbSyncDataFiles::field_file_name, dbSyncDataFiles::field_file_size, $dbSyncDataFile->getTableName(), dbSyncDataFiles::field_archive_id, $archive[dbSyncDataArchives::field_archive_id], dbSyncDataFiles::field_archive_number, $archive[dbSyncDataFiles::field_archive_number], dbSyncDataFiles::field_status, dbSyncDataFiles::status_ok);
            $files = array();
            if (!$dbSyncDataFile->sqlExec($SQL, $files))
            {
                $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbSyncDataFile->getError()));
                return false;
            }
            if (count($files) < 1)
            {
                $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sync_error_file_list_invalid));
                return false;
            }
            $files = $files[0];

            $values = array(
                array(
                    'label' => $admin->lang->translate('Archive ID'),
                    'text' => $archive[dbSyncDataArchives::field_archive_id]
                ),
                array(
                    'label' => $admin->lang->translate('Archive number'),
                    'text' => $archive[dbSyncDataArchives::field_archive_number]
                ),
                array(
                    'label' => $admin->lang->translate('Archive type'),
                    'text' => $dbSyncDataArchive->backup_type_array_text[$archive[dbSyncDataArchives::field_archive_type]]
                ),
                array(
                    'label' => $admin->lang->translate('Total files'),
                    'text' => $files['count']
                ),
                array(
                    'label' => $admin->lang->translate('Total size'),
                    'text' => $kitTools->bytes2Str($files['bytes'])
                ),
                array(
                    'label' => $admin->lang->translate('Timestamp'),
                    'text' => date(sync_cfg_datetime_str, strtotime($archive[dbSyncDataArchives::field_timestamp]))
                )
            );
            $info   = array(
                'label' => $admin->lang->translate('Archive information'),
                'values' => $values
            );

            $data = array(
                'form' => array(
                    'name' => 'backup_update',
                    'link' => $this->page_link,
                    'action' => array(
                        'name' => self::request_action,
                        'value' => self::action_update_start
                    ),
                    'archive' => array(
                        'name' => dbSyncDataArchives::field_archive_id,
                        'value' => $archiv_id
                    ),
                    'btn' => array(
                        'ok' => $admin->lang->translate('Apply'),
                        'abort' => $admin->lang->translate('Cancel')
                    )
                ),
                'info' => $info,
                'archive_name' => array(
                    'name' => dbSyncDataArchives::field_archive_name,
                    'value' => '',
                    'label' => $admin->lang->translate('Name of the archive'),
                    'hint' => $admin->lang->translate('Give the archive a name'),
                ),
                'head' => $admin->lang->translate('Datensicherung aktualisieren'),
                'is_intro' => $this->isMessage() ? 0 : 1,
                'intro' => $this->isMessage() ? $this->getMessage() : $admin->lang->translate('<p>Check that the correct backup archive will be updated and give the update archive a name.</p>'),
                'text_process' => sprintf($admin->lang->translate('<p>The backup runs.</p><p>Please don´t close this window and <b>wait for the status message by syncData you will get after max. %s seconds!</b></p>'), $dbSyncDataCfg->getValue(dbSyncDataCfg::cfgLimitExecutionTime)),
                'img_url' => $this->img_url
            );
            return $this->getTemplate('backend.backup.update.lte', $data);
        }
    } // dlgBackupStart()


    /**
     * Legt ein neues Backup Archiv an und startet die Datensicherung
     *
     * @return STR Dialog zum Fortsetzen/Beenden oder BOOL FALSE on error
     */
    public function backupStartNewArchive()
    {
        global $interface,$admin;

        $backup_name = (isset($_REQUEST[dbSyncDataArchives::field_archive_name]) && !empty($_REQUEST[dbSyncDataArchives::field_archive_name]))
                     ? $_REQUEST[dbSyncDataArchives::field_archive_name]
                     : sprintf($admin->lang->translate('backup of data from %s'), date(sync_cfg_datetime_str));
        $backup_type = (isset($_REQUEST[dbSyncDataArchives::field_backup_type])) ? $_REQUEST[dbSyncDataArchives::field_backup_type] : dbSyncDataArchives::backup_type_complete;

        $job_id = -1;
        $status = $interface->backupStart($backup_name, $backup_type, $job_id);
        if ($interface->isError())
        {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $interface->getError()));
            return false;
        }

        if ($status == dbSyncDataJobs::status_time_out)
        {
            return $this->messageBackupInterrupt($job_id);
        }
        elseif ($status == dbSyncDataJobs::status_finished)
        {
            return $this->messageBackupFinished($job_id);
        }
        else
        {
            // unknown status ...
            $this->setError(sprintf('[%s %s] %s', __METHOD__, __LINE__, sync_error_status_unknown));
            return false;
        }
    } // backupStartNewArchive()

    /**
     * Generate and show a message that the backup is interrupted,
     * shows the actual state of backup
     *
     * @param INT $job_id
     * @return STR message dialog
     */
    public function messageBackupInterrupt($job_id)
    {
        global $dbSyncDataJob;
        global $dbSyncDataFile;
        global $kitTools;
        global $dbSyncDataCfg;
        global $admin;

        $where = array(
            dbSyncDataJobs::field_id => $job_id
        );
        $job   = array();
        if (!$dbSyncDataJob->sqlSelectRecord($where, $job))
        {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbSyncDataJob->getError()));
            return false;
        }
        $job = $job[0];

        // Anzahl und Umfang der bisher gesicherten Dateien ermitteln
        $SQL   = sprintf("SELECT COUNT(%s) AS count, SUM(%s) AS bytes FROM %s WHERE %s='%s' AND %s='%s' AND %s='%s'", dbSyncDataFiles::field_file_name, dbSyncDataFiles::field_file_size, $dbSyncDataFile->getTableName(), dbSyncDataFiles::field_archive_id, $job[dbSyncDataJobs::field_archive_id], //$archive_id,
            dbSyncDataFiles::field_status, dbSyncDataFiles::status_ok, dbSyncDataFiles::field_action, dbSyncDataFiles::action_add);
        $files = array();
        if (!$dbSyncDataFile->sqlExec($SQL, $files))
        {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbSyncDataFile->getError()));
            return false;
        }
        $auto_exec_msec = $dbSyncDataCfg->getValue(dbSyncDataCfg::cfgAutoExecMSec);
        $auto_exec      = $auto_exec_msec > 0 ? sprintf($admin->lang->translate('<p style="color:red;"><em>AutoExec is active. The process will continue automatically in %d milliseconds.</em></p>'), $auto_exec_msec) : '';
        $info           = sprintf($admin->lang->translate('<p>The update isn´t complete because not all files could be secured within the maximum execution time for PHP scripts from <b>%s seconds</b>.</p><p>Until now, <b>%s</b> files updated with a circumference of <b>%s</b>.</p><p>Please click "Continue ..." to proceed the update.</p>%s'), $this->max_execution_time, $files[0]['count'], $kitTools->bytes2Str($files[0]['bytes']), $auto_exec);
        $data           = array(
            'form' => array(
                'name' => 'backup_continue',
                'link' => $this->page_link,
                'action' => array(
                    'name' => self::request_action,
                    'value' => self::action_backup_continue
                ),
                'btn' => array(
                    'abort' => $admin->lang->translate('Cancel'),
                    'ok' => $admin->lang->translate('Continue ...')
                )
            ),
            'head' => $admin->lang->translate('Datensicherung fortsetzen'),
            'is_intro' => $this->isMessage() ? 0 : 1,
            'intro' => $this->isMessage() ? $this->getMessage() : $info,
            'job' => array(
                'name' => dbSyncDataJobs::field_id,
                'value' => $job_id
            ),
            'text_process' => sprintf($admin->lang->translate('<p>The backup runs.</p><p>Please don´t close this window and <b>wait for the status message by syncData you will get after max. %s seconds!</b></p>'), $dbSyncDataCfg->getValue(dbSyncDataCfg::cfgLimitExecutionTime)),
            'img_url' => $this->img_url,
            'auto_exec_msec' => $auto_exec_msec
        );
        // Statusmeldung ausgeben
        return $this->getTemplate('backend.backup.interrupt.lte', $data);
    } // messageBackupInterrupt()

    /**
     * Generate and show a message that the backup is finished.
     * Shows the main stats of the backup
     *
     * @param INT $job_id
     * @return STR message dialog
     */
    public function messageBackupFinished($job_id)
    {
        global $dbSyncDataJob;
        global $dbSyncDataFile;
        global $kitTools;
        global $interface;
        global $dbSyncDataArchive;
        global $admin;

        $where = array(
            dbSyncDataJobs::field_id => $job_id
        );
        $job   = array();
        if (!$dbSyncDataJob->sqlSelectRecord($where, $job))
        {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbSyncDataJob->getError()));
            return false;
        }
        if (count($job) < 1)
        {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(sync_error_job_id_invalid, $job_id)));
            return false;
        }
        $job = $job[0];

        $where   = array(
            dbSyncDataArchives::field_archive_id => $job[dbSyncDataJobs::field_archive_id],
            dbSyncDataArchives::field_archive_number => $job[dbSyncDataJobs::field_archive_number]
        );
        $archive = array();
        if (!$dbSyncDataArchive->sqlSelectRecord($where, $archive))
        {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbSyncDataArchive->getError()));
            return false;
        }
        if (count($archive) < 1)
        {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(sync_error_archive_id_invalid, $job[dbSyncDataJobs::field_archive_id])));
            return false;
        }
        $archive = $archive[0];

        // Anzahl und Umfang der bisher gesicherten Dateien ermitteln
        $SQL   = sprintf("SELECT COUNT(%s), SUM(%s) FROM %s WHERE %s='%s' AND %s='%s' AND %s='%s'", dbSyncDataFiles::field_file_name, dbSyncDataFiles::field_file_size, $dbSyncDataFile->getTableName(), dbSyncDataFiles::field_archive_id, $job[dbSyncDataJobs::field_archive_id], dbSyncDataFiles::field_status, dbSyncDataFiles::status_ok, dbSyncDataFiles::field_action, dbSyncDataFiles::action_add);
        $files = array();
        if (!$dbSyncDataFile->sqlExec($SQL, $files))
        {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbSyncDataFile->getError()));
            return false;
        }

        // Meldung zusammenstellen
        $info = sprintf($admin->lang->translate('<p>The backup was completed successfully.</p><p>There were <span class="sync_data_highlight">%s</span> files backed up with a circumference of <span class="sync_data_highlight">%s</span>.</p><p>See the full archive:<br /><a href="%s">%s</a>.'), $files[0][sprintf('COUNT(%s)', dbSyncDataFiles::field_file_name)], $kitTools->bytes2Str($files[0][sprintf('SUM(%s)', dbSyncDataFiles::field_file_size)]), str_replace(CAT_PATH, CAT_URL, $interface->getBackupPath() . $archive[dbSyncDataArchives::field_archive_name] . '.zip'), str_replace(CAT_PATH, CAT_URL, $interface->getBackupPath() . $archive[dbSyncDataArchives::field_archive_name] . '.zip'));
        $data = array(
            'form' => array(
                'name' => 'backup_continue',
                'link' => $this->page_link,
                'action' => array(
                    'name' => self::request_action,
                    'value' => self::action_default
                ),
                'btn' => array(
                    'ok' => $admin->lang->translate('Apply')
                )
            ),
            'head' => $admin->lang->translate('Datensicherung beendet'),
            'is_intro' => $this->isMessage() ? 0 : 1,
            'intro' => $this->isMessage() ? $this->getMessage() : $info,
            'job' => array(
                'name' => dbSyncDataJobs::field_id,
                'value' => $job_id
            )
        );
        // Statusmeldung ausgeben
        return $this->getTemplate('backend.backup.message.lte', $data);

    } // messageBackupFinished()


    /*
     * Nimmt das Backup nach einer Unterbrechung wieder auf
     *
     * @return STR Dialog bzw. Statusmeldung
     */
    public function backupContinue()
    {
        global $interface;
        global $admin;

        $job_id = isset($_REQUEST[dbSyncDataJobs::field_id]) ? $_REQUEST[dbSyncDataJobs::field_id] : -1;

        if ($job_id < 1)
        {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(sync_error_job_id_invalid, $job_id)));
            return false;
        }

        $status = $interface->backupContinue($job_id);
        if ($interface->isError())
        {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $interface->getError()));
            return false;
        }
        if ($status == dbSyncDataJobs::status_time_out)
        {
            return $this->messageBackupInterrupt($job_id);
        }
        elseif ($status == dbSyncDataJobs::status_finished)
        {
            return $this->messageBackupFinished($job_id);
        }
        else
        {
            // in allen anderen Faellen ist nichts zu tun
            $this->setMessage($admin->lang->translate('<p>There is nothing to do - task completed.</p>'));
            $data = array(
                'form' => array(
                    'name' => 'backup_stop',
                    'link' => $this->page_link,
                    'action' => array(
                        'name' => self::request_action,
                        'value' => self::action_default
                    ),
                    'btn' => array(
                        'abort' => $admin->lang->translate('Cancel'),
                        'ok' => $admin->lang->translate('Apply')
                    )
                ),
                'head' => $admin->lang->translate('Datensicherung fortsetzen'),
                'is_intro' => 0, // Meldung anzeigen
                'intro' => $this->getMessage(),
                'job' => array(
                    'name' => dbSyncDataJobs::field_id,
                    'value' => $job_id
                )
            );
            // Statusmeldung ausgeben
            return $this->getTemplate('backend.backup.message.lte', $data);
        }
    } // backupContinue()


    /**
     * Dialog zur Auswahl des Backup Archiv, das fuer einen Restore verwendet
     * werden soll
     *
     * @access public
     * @return string  dialog
     */
    public function dlgRestore()
    {
        global $interface, $admin;

        if (!file_exists($interface->getBackupPath()))
        {
            if (!mkdir($interface->getBackupPath(), 0755, true))
            {
                $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(sync_error_mkdir, $interface->getBackupPath())));
                return false;
            }
            else {
                $interface->createAccessFiles($interface->getBackupPath());
            }
        }
        $arcs     = $interface->directoryTree($interface->getBackupPath());
        $archives = array();
        foreach ($arcs as $arc)
        {
            if (is_file($arc) && (pathinfo($arc, PATHINFO_EXTENSION) == 'zip'))
                $archives[] = $arc;
        }

        $select_array   = array();
        $select_array[] = array(
            'value'    => -1,
            'selected' => 1,
            'text'     => $admin->lang->translate('- select restore -'),
        );

        foreach ($archives as $archive)
        {
            $select_array[] = array(
                'value'    => str_replace(CAT_PATH, '', $archive),
                'selected' => 0,
                'text'     => basename($archive)
            );
        }

        if (count($archives) < 1)
        {
            // Mitteilung: kein Archiv gefunden!
            $dir = str_replace(CAT_PATH, '', $interface->getBackupPath());
            $this->setMessage(sprintf($admin->lang->translate('<p>No backups were found in the directory <span class="sync_data_highlight">%s</span>, which can be used for a restore.</p><p></p>Transfer the archive files manually via FTP to the directory <span class="sync_data_highlight">%s</span> and you call this dialogue again.</p>'), $dir, $dir));
        }

        $data = array(
            'form' => array(
                'name' => 'restore_select',
                'link' => $this->page_link,
                'action' => array(
                    'name'  => self::request_action,
                    'value' => self::action_restore_info
                ),
                'btn' => array(
                    'ok' => $admin->lang->translate('Apply')
                )
            ),
            'restore' => array(
                'name'    => self::request_restore,
                'label'   => $admin->lang->translate('Choose a restore!'),
                'hint'    => $admin->lang->translate(''),
                'options' => $select_array
            ),
            'head'     => $admin->lang->translate('Start restore'),
            'is_intro' => $this->isMessage() ? 0 : 1,
            'intro'    => $this->isMessage() ? $this->getMessage() : $admin->lang->translate('<p>Select the backup from which will be used for data recovery.</p>'),
        );

        return $this->getTemplate('backend.restore.start.lte', $data);
    } // dlgRestore()

    /**
     * Prueft das angegebene Archiv und startet einen Dialog zum Festlegen
     * der Parameter fuer den Restore
     *
     * @return STR dialog
     */
    public function restoreInfo()
    {
        global $dbSyncDataJob;
        global $kitTools;
        global $interface;
        global $dbSyncDataCfg;

        $backup_archive = (isset($_REQUEST[self::request_restore])) ? $_REQUEST[self::request_restore] : -1;

        if ($backup_archive == -1)
        {
            // kein gueltiges Archiv angegeben, Meldung setzen und zurueck zum Auswahldialog
            $this->setMessage(sync_msg_no_backup_file_for_process);
            return $this->dlgRestore();
        }
        // get the content of syncData.ini into the $ini_data array
        $ini_data = array();
        if (!$interface->restoreInfo($backup_archive, $ini_data))
        {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $interface->getError()));
            return false;
        }

        // existiert die Dateiliste im /temp Verzeichnis?
        if (false === ($list = unserialize(file_get_contents($this->temp_path . syncDataInterface::archive_list))))
        {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(sync_error_file_read, $this->temp_path . syncDataInterface::archive_list)));
            return false;
        }

        // pruefen ob Dateien wiederhergestellt werden solle
        $restore_info   = $interface->array_search($list, 'filename', 'files/', true);
        $restore_files  = (count($restore_info) > 0) ? true : false;
        $restore_info   = $interface->array_search($list, 'filename', 'sql/', true);
        $restore_tables = (count($restore_info) > 0) ? true : false;

        // Werte setzen
        $values = array(
            array(
                'label' => $admin->lang->translate('Archive ID'),
                'text' => $ini_data[syncDataInterface::section_general][dbSyncDataJobs::field_archive_id]
            ),
            array(
                'label' => $admin->lang->translate('Archive number'),
                'text' => $ini_data[syncDataInterface::section_general][dbSyncDataJobs::field_archive_number]
            ),
            array(
                'label' => $admin->lang->translate('Archive type'),
                'text' => $dbSyncDataJob->job_type_array[$ini_data[syncDataInterface::section_general][dbSyncDataJobs::field_type]]
            ),
            array(
                'label' => $admin->lang->translate('Total files'),
                'text' => $ini_data[syncDataInterface::section_general]['total_files']
            ),
            array(
                'label' => $admin->lang->translate('Total size'),
                'text' => $kitTools->bytes2Str($ini_data[syncDataInterface::section_general]['total_size'])
            ),
            array(
                'label' => sync_label_wb_url,
                'text' => $ini_data[syncDataInterface::section_general]['used_wb_url']
            ),
            array(
                'label' => sync_label_status,
                'text' => $ini_data[syncDataInterface::section_general][dbSyncDataJobs::field_last_message]
            ),
            array(
                'label' => $admin->lang->translate('Timestamp'),
                'text' => date(sync_cfg_datetime_str, strtotime($ini_data[syncDataInterface::section_general][dbSyncDataJobs::field_timestamp]))
            )
        );
        $info   = array(
            'label' => $admin->lang->translate('Archive information'),
            'values' => $values
        );

        $restore = array(
            'select' => array(
                'label' => sync_label_restore,
                'select' => array(
                    array(
                        'name' => dbSyncDataJobs::field_type,
                        'value' => dbSyncDataJobs::type_restore_mysql,
                        'text' => sync_label_tables,
                        'checked' => 1,
                        'enabled' => ($restore_tables && ($ini_data[syncDataInterface::section_general][dbSyncDataJobs::field_type] == dbSyncDataJobs::type_backup_complete) || ($ini_data[syncDataInterface::section_general][dbSyncDataJobs::field_type] == dbSyncDataJobs::type_backup_mysql)) ? 1 : 0
                    ),
                    array(
                        'name' => dbSyncDataJobs::field_type,
                        'value' => dbSyncDataJobs::type_restore_files,
                        'text' => sync_label_files,
                        'checked' => 1,
                        'enabled' => ($restore_files && ($ini_data[syncDataInterface::section_general][dbSyncDataJobs::field_type] == dbSyncDataJobs::type_backup_complete) || ($ini_data[syncDataInterface::section_general][dbSyncDataJobs::field_type] == dbSyncDataJobs::type_backup_files)) ? 1 : 0
                    )
                )
            ),
            'mode' => array(
                'label' => sync_label_restore_mode,
                'select' => array(
                    array(
                        'name' => dbSyncDataJobs::field_restore_mode,
                        'value' => dbSyncDataJobs::mode_changed_binary,
                        'text' => sync_label_restore_mode_binary,
                        'checked' => 0
                    ),
                    array(
                        'name' => dbSyncDataJobs::field_restore_mode,
                        'value' => dbSyncDataJobs::mode_changed_date_size,
                        'text' => sync_label_restore_mode_time_size,
                        'checked' => 1
                    ),
                    array(
                        'name' => dbSyncDataJobs::field_restore_mode,
                        'value' => dbSyncDataJobs::mode_replace_all,
                        'text' => sync_label_restore_mode_replace_all,
                        'checked' => 0
                    )
                )
            ),
            'replace' => array(
                'url' => array(
                    'label' => sync_label_restore_replace,
                    'name' => dbSyncDataJobs::field_replace_wb_url, //self::request_restore_replace_url,
                    'value' => 1,
                    'text' => sync_label_restore_replace_url,
                    'checked' => 1
                ),
                'prefix' => array(
                    'label' => sync_label_restore_replace,
                    'name' => dbSyncDataJobs::field_replace_table_prefix, //self::request_restore_replace_prefix,
                    'value' => 1,
                    'text' => sync_label_restore_replace_prefix,
                    'checked' => 1
                )
            ),
            'ignore' => array(
                'config' => array(
                    'label' => sync_label_restore_ignore,
                    'name' => dbSyncDataJobs::field_ignore_config,
                    'value' => 1,
                    'text' => sync_label_restore_ignore_config,
                    'checked' => 1
                ),
                'htaccess' => array(
                    'label' => sync_label_restore_ignore,
                    'name' => dbSyncDataJobs::field_ignore_htaccess,
                    'value' => 1,
                    'text' => sync_label_restore_ignore_htaccess,
                    'checked' => 1
                )
            ),
            'delete' => array(
                'tables' => array(
                    'label' => sync_label_restore_delete,
                    'name' => dbSyncDataJobs::field_delete_tables,
                    'value' => 1,
                    'text' => sync_label_restore_delete_tables,
                    'checked' => 0,
                    'enabled' => 1
                ),
                'files' => array(
                    'label' => sync_label_restore_delete,
                    'name' => dbSyncDataJobs::field_delete_files,
                    'value' => 1,
                    'text' => sync_label_restore_delete_files,
                    'checked' => 0,
                    'enabled' => 1
                )
            )
        );

        $data = array(
            'form' => array(
                'name' => 'restore_info',
                'link' => $this->page_link,
                'action' => array(
                    'name' => self::request_action,
                    'value' => self::action_restore_start
                ),
                'restore' => array(
                    'name' => self::request_restore,
                    'value' => $backup_archive
                ),
                'btn' => array(
                    'ok' => sync_btn_start,
                    'abort' => $admin->lang->translate('Cancel')
                )
            ),
            'info' => $info,
            'restore' => $restore,
            'head' => $admin->lang->translate('Start restore'),
            'is_intro' => $this->isMessage() ? 0 : 1,
            'intro' => $this->isMessage() ? $this->getMessage() : sync_intro_restore_info,
            'text_process' => sprintf(sync_msg_restore_running, $dbSyncDataCfg->getValue(dbSyncDataCfg::cfgLimitExecutionTime)),
            'img_url' => $this->img_url
        );

        return $this->getTemplate('backend.restore.archive.info.lte', $data);
    } // restoreInfo()

    /**
     * Startet die Datenwiederherstellung
     */
    public function restoreStart()
    {
        global $interface;

        $backup_archive = (isset($_REQUEST[self::request_restore])) ? $_REQUEST[self::request_restore] : -1;

        // Backup Archiv angegeben?
        if ($backup_archive == -1)
        {
            // kein gueltiges Archiv angegeben, Meldung setzen und zurueck zum Auswahldialog
            $this - set_error(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sync_error_backup_archive_invalid));
            return false;
        }

        // gettting the params for restoring
        $replace_prefix  = isset($_REQUEST[dbSyncDataJobs::field_replace_table_prefix]) ? true : false;
        $replace_url     = isset($_REQUEST[dbSyncDataJobs::field_replace_wb_url]) ? true : false;
        $restore_mode    = isset($_REQUEST[dbSyncDataJobs::field_restore_mode]) ? $_REQUEST[dbSyncDataJobs::field_restore_mode] : dbSyncDataJobs::mode_changed_date_size;
        $restore_type    = $_REQUEST[dbSyncDataJobs::field_type];
        $ignore_config   = isset($_REQUEST[dbSyncDataJobs::field_ignore_config]) ? true : false;
        $ignore_htaccess = isset($_REQUEST[dbSyncDataJobs::field_ignore_htaccess]) ? true : false;
        $delete_files    = isset($_REQUEST[dbSyncDataJobs::field_delete_files]) ? true : false;
        $delete_tables   = isset($_REQUEST[dbSyncDataJobs::field_delete_tables]) ? true : false;

        $job_id = -1;
        $status = $interface->restoreStart($backup_archive, $replace_prefix, $replace_url, $restore_type, $restore_mode, $ignore_config, $ignore_htaccess, $delete_files, $delete_tables, $job_id);
        if ($interface->isError())
        {
            // error executing interface
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $interface->getError()));
            return false;
        }

        if ($status == dbSyncDataJobs::status_time_out)
        {
            // interrupt restore
            return $this->messageRestoreInterrupt($job_id);
        }
        elseif ($status == dbSyncDataJobs::status_finished)
        {
            // finish restore
            return $this->messageRestoreFinished($job_id);
        }
        else
        {
            // unknown status ...
            $this->setError(sprintf('[%s %s] %s', __METHOD__, __LINE__, sync_error_status_unknown));
            return false;
        }
    } // restoreStart()

    public function restoreContinue()
    {
        global $interface;

        $job_id = isset($_REQUEST[dbSyncDataJobs::field_id]) ? $_REQUEST[dbSyncDataJobs::field_id] : -1;

        if ($job_id < 1)
        {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sync_error_job_id_invalid));
            return false;
        }

        $status = $interface->restoreContinue($job_id);
        if ($interface->isError())
        {
            // error executing interface
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $interface->getError()));
            return false;
        }

        if ($status == dbSyncDataJobs::status_time_out)
        {
            // interrupt restore
            return $this->messageRestoreInterrupt($job_id);
        }
        elseif ($status == dbSyncDataJobs::status_finished)
        {
            // finish restore
            return $this->messageRestoreFinished($job_id);
        }
        else
        {
            // unknown status ...
            $this->setError(sprintf('[%s %s] %s', __METHOD__, __LINE__, sync_error_status_unknown));
            return false;
        }
    } // restoreContinue()

    /**
     * Prompt message: restoring process is interrupted
     *
     * @param INT $job_id
     * @return STR message dialog
     */
    public function messageRestoreInterrupt($job_id)
    {
        global $dbSyncDataJob;
        global $kitTools;
        global $dbSyncDataProtocol;
        global $dbSyncDataCfg;
        global $admin;

        $where = array(
            dbSyncDataJobs::field_id => $job_id
        );
        $job   = array();
        if (!$dbSyncDataJob->sqlSelectRecord($where, $job))
        {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbSyncDataJob->getError()));
            return false;
        }
        $job = $job[0];

        // walk through tables and files which are added, deleted or replaced
        $check_array  = array(
            dbSyncDataProtocol::action_mysql_add,
            dbSyncDataProtocol::action_mysql_delete,
            dbSyncDataProtocol::action_mysql_replace,
            dbSyncDataProtocol::action_file_add,
            dbSyncDataProtocol::action_file_delete,
            dbSyncDataProtocol::action_file_replace
        );
        $result_array = array();
        foreach ($check_array as $action)
        {
            $SQL    = sprintf("SELECT COUNT(%s) AS count, SUM(%s) AS bytes FROM %s WHERE %s='%s' AND %s='%s' AND %s='%s'", dbSyncDataProtocol::field_file, dbSyncDataProtocol::field_size, $dbSyncDataProtocol->getTableName(), dbSyncDataProtocol::field_job_id, $job_id, dbSyncDataProtocol::field_action, $action, dbSyncDataProtocol::field_status, dbSyncDataProtocol::status_ok);
            $result = array();
            if (!$dbSyncDataProtocol->sqlExec($SQL, $result))
            {
                $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbSyncDataProtocol->getError()));
                return false;
            }
            $result_array[$action]['count'] = isset($result[0]['bytes']) ? $result[0]['count'] : 0;
            $result_array[$action]['bytes'] = isset($result[0]['bytes']) ? $result[0]['bytes'] : 0;
        }

        $auto_exec_msec = $dbSyncDataCfg->getValue(dbSyncDataCfg::cfgAutoExecMSec);
        $auto_exec      = $auto_exec_msec > 0 ? sprintf($admin->lang->translate('<p style="color:red;"><em>AutoExec is active. The process will continue automatically in %d milliseconds.</em></p>'), $auto_exec_msec) : '';

        $info = sprintf(sync_msg_restore_interrupted, $this->max_execution_time, $result_array[dbSyncDataProtocol::action_mysql_delete]['count'], $kitTools->bytes2Str($result_array[dbSyncDataProtocol::action_mysql_delete]['bytes']), $result_array[dbSyncDataProtocol::action_mysql_add]['count'], $kitTools->bytes2Str($result_array[dbSyncDataProtocol::action_mysql_add]['bytes']), $result_array[dbSyncDataProtocol::action_mysql_replace]['count'], $kitTools->bytes2Str($result_array[dbSyncDataProtocol::action_mysql_replace]['bytes']), $result_array[dbSyncDataProtocol::action_file_delete]['count'], $kitTools->bytes2Str($result_array[dbSyncDataProtocol::action_file_delete]['bytes']), $result_array[dbSyncDataProtocol::action_file_add]['count'], $kitTools->bytes2Str($result_array[dbSyncDataProtocol::action_file_add]['bytes']), $result_array[dbSyncDataProtocol::action_file_replace]['count'], $kitTools->bytes2Str($result_array[dbSyncDataProtocol::action_file_replace]['bytes']), $auto_exec);

        $data = array(
            'form' => array(
                'name' => 'restore_continue',
                'link' => $this->page_link,
                'action' => array(
                    'name' => self::request_action,
                    'value' => self::action_restore_continue
                ),
                'btn' => array(
                    'abort' => $admin->lang->translate('Cancel'),
                    'ok' => $admin->lang->translate('Continue ...')
                )
            ),
            'head' => $admin->lang->translate('Continue the restore ...'),
            'is_intro' => $this->isMessage() ? 0 : 1,
            'intro' => $this->isMessage() ? $this->getMessage() : $info,
            'job' => array(
                'name' => dbSyncDataJobs::field_id,
                'value' => $job_id
            ),
            'text_process' => sprintf(sync_msg_restore_running, $dbSyncDataCfg->getValue(dbSyncDataCfg::cfgLimitExecutionTime)),
            'img_url' => $this->img_url,
            'auto_exec_msec' => $auto_exec_msec
        );
        // Statusmeldung ausgeben
        return $this->getTemplate('backend.restore.interrupt.lte', $data);
    } // messageRestoreInterrupt()

    /**
     * Prompt message: restoring process is finished
     *
     * @param INT $job_id
     * @return STR message dialog
     */
    public function messageRestoreFinished($job_id)
    {
        global $dbSyncDataJob;
        global $kitTools;
        global $dbSyncDataProtocol;

        $where = array(
            dbSyncDataJobs::field_id => $job_id
        );
        $job   = array();
        if (!$dbSyncDataJob->sqlSelectRecord($where, $job))
        {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbSyncDataJob->getError()));
            return false;
        }
        $job = $job[0];

        // walk through tables and files which are added, deleted or replaced
        $check_array  = array(
            dbSyncDataProtocol::action_mysql_add,
            dbSyncDataProtocol::action_mysql_delete,
            dbSyncDataProtocol::action_mysql_replace,
            dbSyncDataProtocol::action_file_add,
            dbSyncDataProtocol::action_file_delete,
            dbSyncDataProtocol::action_file_replace
        );
        $result_array = array();
        foreach ($check_array as $action)
        {
            $SQL    = sprintf("SELECT COUNT(%s) AS count, SUM(%s) AS bytes FROM %s WHERE %s='%s' AND %s='%s' AND %s='%s'", dbSyncDataProtocol::field_file, dbSyncDataProtocol::field_size, $dbSyncDataProtocol->getTableName(), dbSyncDataProtocol::field_job_id, $job_id, dbSyncDataProtocol::field_action, $action, dbSyncDataProtocol::field_status, dbSyncDataProtocol::status_ok);
            $result = array();
            if (!$dbSyncDataProtocol->sqlExec($SQL, $result))
            {
                $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbSyncDataProtocol->getError()));
                return false;
            }
            $result_array[$action]['count'] = isset($result[0]['bytes']) ? $result[0]['count'] : 0;
            $result_array[$action]['bytes'] = isset($result[0]['bytes']) ? $result[0]['bytes'] : 0;
        }

        $info = sprintf(sync_msg_restore_finished, $result_array[dbSyncDataProtocol::action_mysql_delete]['count'], $kitTools->bytes2Str($result_array[dbSyncDataProtocol::action_mysql_delete]['bytes']), $result_array[dbSyncDataProtocol::action_mysql_add]['count'], $kitTools->bytes2Str($result_array[dbSyncDataProtocol::action_mysql_add]['bytes']), $result_array[dbSyncDataProtocol::action_mysql_replace]['count'], $kitTools->bytes2Str($result_array[dbSyncDataProtocol::action_mysql_replace]['bytes']), $result_array[dbSyncDataProtocol::action_file_delete]['count'], $kitTools->bytes2Str($result_array[dbSyncDataProtocol::action_file_delete]['bytes']), $result_array[dbSyncDataProtocol::action_file_add]['count'], $kitTools->bytes2Str($result_array[dbSyncDataProtocol::action_file_add]['bytes']), $result_array[dbSyncDataProtocol::action_file_replace]['count'], $kitTools->bytes2Str($result_array[dbSyncDataProtocol::action_file_replace]['bytes']));

        $data = array(
            'form' => array(
                'name' => 'restore_finished',
                'link' => $this->page_link,
                'action' => array(
                    'name' => self::request_action,
                    'value' => self::action_default
                ),
                'btn' => array(
                    'ok' => $admin->lang->translate('Apply')
                )
            ),
            'head' => $admin->lang->translate('Restore finished!'),
            'is_intro' => $this->isMessage() ? 0 : 1,
            'intro' => $this->isMessage() ? $this->getMessage() : $info,
            'job' => array(
                'name' => dbSyncDataJobs::field_id,
                'value' => $job_id
            )
        );
        // Statusmeldung ausgeben
        return $this->getTemplate('backend.restore.message.lte', $data);
    } // messageRestoreFinished()

    /**
     * Start the process of updating an existing backup.
     * Gather the informations and call the interface for processing
     *
     * @return STR|BOOL dialog or FALSE on error
     */
    public function updateStart()
    {
        global $interface;

        $archive_id  = isset($_REQUEST[dbSyncDataArchives::field_archive_id]) ? $_REQUEST[dbSyncDataArchives::field_archive_id] : -1;
        $update_name = isset($_REQUEST[dbSyncDataArchives::field_archive_name]) ? $_REQUEST[dbSyncDataArchives::field_archive_name] : '';

        $job_id = -1;
        $status = $interface->updateStart($archive_id, $update_name, $job_id);
        if ($interface->isError())
        {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $interface->getError()));
            return false;
        }

        if ($status == dbSyncDataJobs::status_time_out)
        {
            return $this->messageUpdateInterrupt($job_id);
        }
        elseif ($status == dbSyncDataJobs::status_finished)
        {
            return $this->messageUpdateFinished($job_id);
        }
        else
        {
            // unknown status ...
            $this->setError(sprintf('[%s %s] %s', __METHOD__, __LINE__, sync_error_status_unknown));
            return false;
        }
    } // updateStart()

    /**
     * Continue the update Process after an interrupt
     *
     * @return STR|BOOL dialog or FALSE on error
     */
    public function updateContinue()
    {
        global $interface;
        global $admin;

        $job_id = isset($_REQUEST[dbSyncDataJobs::field_id]) ? $_REQUEST[dbSyncDataJobs::field_id] : -1;

        if ($job_id < 1)
        {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(sync_error_job_id_invalid, $job_id)));
            return false;
        }

        $status = $interface->updateContinue($job_id);
        if ($interface->isError())
        {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $interface->getError()));
            return false;
        }
        if ($status == dbSyncDataJobs::status_time_out)
        {
            return $this->messageUpdateInterrupt($job_id);
        }
        elseif ($status == dbSyncDataJobs::status_finished)
        {
            return $this->messageUpdateFinished($job_id);
        }
        else
        {
            // in allen anderen Faellen ist nichts zu tun
            $this->setMessage($admin->lang->translate('<p>There is nothing to do - task completed.</p>'));
            $data = array(
                'form' => array(
                    'name' => 'update_stop',
                    'link' => $this->page_link,
                    'action' => array(
                        'name' => self::request_action,
                        'value' => self::action_default
                    ),
                    'btn' => array(
                        'abort' => $admin->lang->translate('Cancel'),
                        'ok' => $admin->lang->translate('Apply')
                    )
                ),
                'head' => $admin->lang->translate('Continue the update ...'),
                'is_intro' => 0, // Meldung anzeigen
                'intro' => $this->getMessage(),
                'job' => array(
                    'name' => dbSyncDataJobs::field_id,
                    'value' => $job_id
                )
            );
            // Statusmeldung ausgeben
            return $this->getTemplate('backend.backup.message.lte', $data);
        }
    } // updateContinue()

    /**
     * Return a message that the update process is interrupted.
     * Shows some statistics and additional informations.
     *
     * @param INT $job_id
     * @return STR message dialog
     */
    public function messageUpdateInterrupt($job_id)
    {
        global $dbSyncDataJob;
        global $dbSyncDataFile;
        global $kitTools;
        global $dbSyncDataCfg;
        global $admin;

        $where = array(
            dbSyncDataJobs::field_id => $job_id
        );
        $job   = array();
        if (!$dbSyncDataJob->sqlSelectRecord($where, $job))
        {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbSyncDataJob->getError()));
            return false;
        }
        $job = $job[0];

        // Anzahl und Umfang der bisher gesicherten Dateien ermitteln
        $SQL   = sprintf("SELECT COUNT(%s) AS count, SUM(%s) AS bytes FROM %s WHERE %s='%s' AND %s='%s' AND %s='%s' AND (%s='%s' OR %s='%s')", dbSyncDataFiles::field_file_name, dbSyncDataFiles::field_file_size, $dbSyncDataFile->getTableName(), dbSyncDataFiles::field_archive_id, $job[dbSyncDataJobs::field_archive_id], dbSyncDataFiles::field_archive_number, $job[dbSyncDataJobs::field_archive_number], dbSyncDataFiles::field_status, dbSyncDataFiles::status_ok, dbSyncDataFiles::field_action, dbSyncDataFiles::action_add, dbSyncDataFiles::field_action, dbSyncDataFiles::action_replace);
        $files = array();
        if (!$dbSyncDataFile->sqlExec($SQL, $files))
        {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbSyncDataFile->getError()));
            return false;
        }

        $auto_exec_msec = $dbSyncDataCfg->getValue(dbSyncDataCfg::cfgAutoExecMSec);
        $auto_exec      = $auto_exec_msec > 0 ? sprintf($admin->lang->translate('<p style="color:red;"><em>AutoExec is active. The process will continue automatically in %d milliseconds.</em></p>'), $auto_exec_msec) : '';

        $info = sprintf($admin->lang->translate('<p>The update isn´t complete because not all files could be secured within the maximum execution time for PHP scripts from <b>%s seconds</b>.</p><p>Until now, <b>%s</b> files updated with a circumference of <b>%s</b>.</p><p>Please click "Continue ..." to proceed the update.</p>%s'), $this->max_execution_time, $files[0]['count'], $kitTools->bytes2Str($files[0]['bytes']), $auto_exec);
        $data = array(
            'form' => array(
                'name' => 'update_continue',
                'link' => $this->page_link,
                'action' => array(
                    'name' => self::request_action,
                    'value' => self::action_update_continue
                ),
                'btn' => array(
                    'abort' => $admin->lang->translate('Cancel'),
                    'ok' => $admin->lang->translate('Continue ...')
                )
            ),
            'head' => $admin->lang->translate('Continue the update ...'),
            'is_intro' => $this->isMessage() ? 0 : 1,
            'intro' => $this->isMessage() ? $this->getMessage() : $info,
            'job' => array(
                'name' => dbSyncDataJobs::field_id,
                'value' => $job_id
            ),
            'text_process' => sprintf(sync_msg_update_running, $dbSyncDataCfg->getValue(dbSyncDataCfg::cfgLimitExecutionTime)),
            'img_url' => $this->img_url,
            'auto_exec_msec' => $auto_exec_msec
        );
        // Statusmeldung ausgeben
        return $this->getTemplate('backend.backup.interrupt.lte', $data);
    } // messageUpdateInterrupt()

    /**
     * Return a message that the update process is finished.
     * Shows some statistics and additional informations.
     *
     * @param INT $job_id
     * @return STR message dialog
     */
    public function messageUpdateFinished($job_id)
    {
        global $dbSyncDataJob;
        global $dbSyncDataFile;
        global $kitTools;
        global $interface;
        global $dbSyncDataArchive;
        global $admin;

        $where = array(
            dbSyncDataJobs::field_id => $job_id
        );
        $job   = array();
        if (!$dbSyncDataJob->sqlSelectRecord($where, $job))
        {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbSyncDataJob->getError()));
            return false;
        }
        if (count($job) < 1)
        {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(sync_error_job_id_invalid, $job_id)));
            return false;
        }
        $job = $job[0];

        $where   = array(
            dbSyncDataArchives::field_archive_id => $job[dbSyncDataJobs::field_archive_id],
            dbSyncDataArchives::field_archive_number => $job[dbSyncDataJobs::field_archive_number]
        );
        $archive = array();
        if (!$dbSyncDataArchive->sqlSelectRecord($where, $archive))
        {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbSyncDataArchive->getError()));
            return false;
        }
        if (count($archive) < 1)
        {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, sprintf(sync_error_archive_id_invalid, $job[dbSyncDataJobs::field_archive_id])));
            return false;
        }
        $archive = $archive[0];

        // Anzahl und Umfang der bisher gesicherten Dateien ermitteln
        $SQL   = sprintf("SELECT COUNT(%s) AS count, SUM(%s) AS bytes FROM %s WHERE %s='%s' AND %s='%s' AND %s='%s' AND (%s='%s' OR %s='%s')", dbSyncDataFiles::field_file_name, dbSyncDataFiles::field_file_size, $dbSyncDataFile->getTableName(), dbSyncDataFiles::field_archive_id, $job[dbSyncDataJobs::field_archive_id], dbSyncDataFiles::field_archive_number, $job[dbSyncDataJobs::field_archive_number], dbSyncDataFiles::field_status, dbSyncDataFiles::status_ok, dbSyncDataFiles::field_action, dbSyncDataFiles::action_add, dbSyncDataFiles::field_action, dbSyncDataFiles::action_replace);
        $files = array();
        if (!$dbSyncDataFile->sqlExec($SQL, $files))
        {
            $this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbSyncDataFile->getError()));
            return false;
        }

        // Meldung zusammenstellen
        $info = sprintf($admin->lang->translate('<p>The backup was completed successfully.</p><p>There were <span class="sync_data_highlight">%s</span> files backed up with a circumference of <span class="sync_data_highlight">%s</span>.</p><p>See the full archive:<br /><a href="%s">%s</a>.'), $files[0]['count'], $kitTools->bytes2Str($files[0]['bytes']), str_replace(CAT_PATH, CAT_URL, $interface->getBackupPath() . $archive[dbSyncDataArchives::field_archive_name]), str_replace(CAT_PATH, CAT_URL, $interface->getBackupPath() . $archive[dbSyncDataArchives::field_archive_name]));
        $data = array(
            'form' => array(
                'name' => 'update_finished',
                'link' => $this->page_link,
                'action' => array(
                    'name' => self::request_action,
                    'value' => self::action_default
                ),
                'btn' => array(
                    'ok' => $admin->lang->translate('Apply')
                )
            ),
            'head' => $admin->lang->translate('Update finished'),
            'is_intro' => $this->isMessage() ? 0 : 1,
            'intro' => $this->isMessage() ? $this->getMessage() : $info,
            'job' => array(
                'name' => dbSyncDataJobs::field_id,
                'value' => $job_id
            )
        );
        // Statusmeldung ausgeben
        return $this->getTemplate('backend.backup.message.lte', $data);
    } // messageUpdateFinished()

} // class syncBackend

?>