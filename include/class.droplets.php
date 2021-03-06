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

if (!class_exists('dbconnectle')) {
		// load dbConnect_LE from include directory
	require_once dirname(__FILE__).'/dbconnect_le/include.php';
}

class dbDroplets extends dbConnectLE { 

	const field_id							= 'id';
	const field_name						= 'name';
	const field_code						= 'code';
	const field_description			= 'description';
	const field_modified_when		= 'modified_when';
	const field_modified_by			= 'modified_by';
	const field_active					= 'active';
	const field_comments				= 'comments';
	
	public function __construct() {
		parent::__construct();
		$this->setTableName('mod_droplets');
		$this->addFieldDefinition(self::field_id, "INT(11) NOT NULL AUTO_INCREMENT", true);
		$this->addFieldDefinition(self::field_name, "VARCHAR(32) NOT NULL DEFAULT ''");
		$this->addFieldDefinition(self::field_code, "TEXT NOT NULL DEFAULT ''", false, false, true);
		$this->addFieldDefinition(self::field_description, "TEXT NOT NULL DEFAULT ''");
		$this->addFieldDefinition(self::field_modified_when, "INT(11) NOT NULL DEFAULT '0'");
		$this->addFieldDefinition(self::field_modified_by, "INT(11) NOT NULL DEFAULT '0'");
		$this->addFieldDefinition(self::field_active, "INT(11) NOT NULL DEFAULT '0'");
		$this->addFieldDefinition(self::field_comments, "TEXT NOT NULL DEFAULT ''");
		$this->checkFieldDefinitions();	
	} // __construct()
	
} // class dbDroplets


class checkDroplets {
	
	var $droplet_path	= '';
	var $error = '';
	
	public function __construct() {
		$this->droplet_path = CAT_PATH . '/modules/syncData/droplets/' ;
	} // __construct()
		
	/**
    * Set $this->error to $error
    * 
    * @param STR $error
    */
  public function setError($error) {
    $this->error = $error;
  } // setError()

  /**
    * Get Error from $this->error;
    * 
    * @return STR $this->error
    */
  public function getError() {
    return $this->error;
  } // getError()

  /**
    * Check if $this->error is empty
    * 
    * @return BOOL
    */
  public function isError() {
    return (bool) !empty($this->error);
  } // isError
	
	public function insertDropletsIntoTable() {
		global $admin;
		// Read droplets from directory
		$folder = opendir($this->droplet_path.'.'); 
		$names = array();
		while (false !== ($file = readdir($folder))) {
			if (basename(strtolower($file)) != 'index.php') {
				$ext = strtolower(substr($file,-4));
				if ($ext	==	".php") {
					$names[count($names)] = $file; 
				}
			}
		}
		closedir($folder);
		// init droplets
		$dbDroplets = new dbDroplets();
		if (!$dbDroplets->sqlTableExists()) {
			// Droplets not installed!
			return false;
		}
		// walk through array
		foreach ($names as $dropfile) {
			//$droplet = addslashes($this->getDropletCodeFromFile($dropfile));
			$droplet = $this->getDropletCodeFromFile($dropfile);
			if ($droplet != "") {
				// get droplet name
				$name = substr($dropfile,0,-4);
				$where = array();
				$where[dbDroplets::field_name] = $name;
				$result = array();
				if (!$dbDroplets->sqlSelectRecord($where, $result)) {
					// error exec query
					$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbDroplets->getError()));
					return false;
				}
				if (sizeof($result) < 1) {
					// insert this droplet into table
					$description = "Example Droplet";
					$comments = "Example Droplet";
					$cArray = explode("\n",$droplet);
					if (substr($cArray[0],0,3) == "//:") {
						// extract description
						$description = trim(substr($cArray[0],3));
						array_shift($cArray);
					}
					if (substr($cArray[0],0,3) == "//:") {
						// extract comment
						$comments = trim(substr($cArray[0],3));
						array_shift($cArray);
					}
					$data = array();
					$data[dbDroplets::field_name] = $name;
					$code = implode("\r\n", $cArray);
					$data[dbDroplets::field_code] = $code;
					$data[dbDroplets::field_description] = $description;
					$data[dbDroplets::field_comments] = $comments;
					$data[dbDroplets::field_active] = 1;
					$data[dbDroplets::field_modified_by] = $admin->get_user_id();
					$data[dbDroplets::field_modified_when] = time();
					if (!$dbDroplets->sqlInsertRecord($data)) {
						// error exec query
						$this->setError(sprintf('[%s - %s] %s', __METHOD__, __LINE__, $dbDroplets->getError()));
						return false;
					}
				}				
			}  
		}
		return true;
	} // insertDropletsIntoTable()
	
	public function getDropletCodeFromFile($dropletfile) {
		$data = "";
		$filename = $this->droplet_path.$dropletfile;
		if (file_exists($filename)) {
			$filehandle = fopen ($filename, "r");
			$data = fread ($filehandle, filesize ($filename));
			fclose($filehandle);
		}	
		return $data;
	} // getDropletCodeFromFile()
	
} // checkDroplets


?>