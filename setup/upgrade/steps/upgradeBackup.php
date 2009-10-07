<?php
/**
* Complete Step Controller. 
*
* KnowledgeTree Community Edition
* Document Management Made Simple
* Copyright(C) 2008,2009 KnowledgeTree Inc.
* Portions copyright The Jam Warehouse Software(Pty) Limited
*
* This program is free software; you can redistribute it and/or modify it under
* the terms of the GNU General Public License version 3 as published by the
* Free Software Foundation.
*
* This program is distributed in the hope that it will be useful, but WITHOUT
* ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more
* details.
*
* You should have received a copy of the GNU General Public License
* along with this program.  If not, see <http://www.gnu.org/licenses/>.
*
* You can contact KnowledgeTree Inc., PO Box 7775 #87847, San Francisco,
* California 94120-7775, or email info@knowledgetree.com.
*
* The interactive user interfaces in modified source and object code versions
* of this program must display Appropriate Legal Notices, as required under
* Section 5 of the GNU General Public License version 3.
*
* In accordance with Section 7(b) of the GNU General Public License version 3,
* these Appropriate Legal Notices must retain the display of the "Powered by
* KnowledgeTree" logo and retain the original copyright notice. If the display of the
* logo is not reasonably feasible for technical reasons, the Appropriate Legal Notices
* must display the words "Powered by KnowledgeTree" and retain the original
* copyright notice.
*
* @copyright 2008-2009, KnowledgeTree Inc.
* @license GNU General Public License version 3
* @author KnowledgeTree Team
* @package Upgrader
* @version Version 0.1
*/

require '../../config/dmsDefaults.php';

class upgradeBackup extends Step {

    /**
	* Reference to Database object
	*
	* @author KnowledgeTree Team
	* @access private
	* @var object
	*/	
    private $_dbhandler = null;

    private $privileges_check = 'tick';
    private $database_check = 'tick';
    protected $silent = true;
    
    protected $util = null;
    
    public function __construct() {
    	$this->temp_variables = array("step_name"=>"backup", "silent"=>$this->silent);
        $this->_dbhandler = new UpgradedbUtil();
    	$this->util = new UpgradeUtil();
    }

    function doStep() {
        parent::doStep();
        if(!$this->inStep("backup")) {
            $this->doRun();
            return 'landing';
        }
        if($this->next()) {
            if ($this->doRun()) {
                return 'next';
            }
        }
        else if ($this->confirm()) {
            if ($this->doRun('confirm')) {
                return 'confirm';
            }
        }
        else if ($this->backupNow()) {
            if ($this->doRun('backupNow')) {
                return 'next';
            }
            else {
                return 'error';
            }
        }
        else if($this->previous()) {
            return 'previous';
        }
        
        $this->doRun();
        return 'landing';
    }
    
    function backupNow()
    {
        return isset($_POST['BackupNow']);
    }
    
    function doRun($action = null) {
        $this->temp_variables['action'] = $action;
        
        if (is_null($action) || ($action == 'confirm')) {
            $this->temp_variables['title'] = 'Confirm Backup';
            $this->backupConfirm();
        }
        else {
            $this->temp_variables['title'] = 'Backup In Progress';
            $this->backup();
        }
        $this->storeSilent();// Set silent mode variables
        
        return true;
    }
    
    /**
     * Set all silent mode varibles
     *
     */
    private function storeSilent() {
    }
    
    // these belong in a shared lib
    function set_state($value)
{
    $_SESSION['state'] = $value;
}
function check_state($value, $state='Home')
{
    if ($_SESSION['state'] != $value)
    {
        ?>
            <script type="text/javascript">
            document.location="?go=<?php echo $state;?>";
            </script>
            <?php
            exit;
    }
}
    
    private function backup() {
//        $this->check_state(1);
//        $this->set_state(2);
        $targetfile=$_SESSION['backupFile'];
        $stmt = $this->create_backup_stmt($targetfile);
        $dir = $stmt['dir'];
    
        if (is_file($dir . '/mysqladmin') || is_file($dir . '/mysqladmin.exe'))
        {
            ob_flush();
            flush();
    ?>
            The backup is now underway. Please wait till it completes.
    <?php
    
            ob_flush();
            flush();
            $curdir=getcwd();
            chdir($dir);
            ob_flush();
            flush();
    
            $handle = popen($stmt['cmd'], 'r');
            $read = fread($handle, 10240);
            pclose($handle);
            $_SESSION['backupOutput']=$read;
            $dir=$this->resolveTempDir();
            $_SESSION['backupFile'] =   $stmt['target'];
    
            if (OS_UNIX) {
                chmod($stmt['target'],0600);
            }
    
            if (is_file($stmt['target']) && filesize($stmt['target']) > 0) {
                $_SESSION['backupStatus'] = true;
            }
            else {
                $_SESSION['backupStatus'] = false;
            }
        }
        else
        {
    ?>
    <P>
        The <i>mysqldump</i> utility was not found in the <?php echo $dir;?> subdirectory.
    
    &nbsp;&nbsp; &nbsp; &nbsp;  <input type=button value="back" onclick="javascript:do_start('welcome')">
    <?php
        }
    }
    
    private function backupDone() {
//        $this->check_state(2);
//        $this->set_state(3);
        title('Backup Status');
        $status = $_SESSION['backupStatus'];
        $filename=$_SESSION['backupFile'];
    
        if ($status)
        {
            $stmt=create_restore_stmt($filename);
    ?>
            The backup file <nobr><i>"<?php echo $filename;?>"</i></nobr> has been created.
            <P> It appears as though the <font color=green>backup has been successful</font>.
            <P>
            <?php
                if ($stmt['dir'] != '')
                {
            ?>
                    Manually, you would do the following to restore the backup:
                    <P>
                    <table bgcolor="lightgrey">
                    <tr>
                        <td>
                            <nobr>cd <?php echo $stmt['dir'];?></nobr>
                            <br/>
            <?php
                }
                else
                {
            ?>
                The mysql backup utility could not be found automatically. Please edit the config.ini and update the backup/mysql Directory entry.
                    <P>
                    If you need to restore from this backup, you should be able to use the following statements:
                    <P>
                    <table bgcolor="lightgrey">
                    <tr>
                        <td>
            <?php
                }
            ?>
                            <nobr><?php echo $stmt['display'];?></nobr>
                    </table>
    
    <?php
        }
        else
        {
    ?>
    It appears as though <font color=red>the backup process has failed</font>.<P></P> Unfortunately, it is difficult to diagnose these problems automatically
    and would recommend that you try to do the backup process manually.
    <P>
    We appologise for the inconvenience.
    <P>
    <table bgcolor="lightgrey">
    <tr>
    <td>
    <?php echo $_SESSION['backupOutput'];?>
    </table>
    <?php
    
        }
    ?>
    <br/>
    
    &nbsp;&nbsp; &nbsp; &nbsp;  <input type=button value="back" onclick="javascript:do_start('welcome')">
    <?php
        if ($status)
        {
            ?>
    &nbsp;&nbsp; &nbsp; &nbsp;  <input type=button value="next" onclick="javascript:do_start('UpgradeConfirm')">
    
    <?php
        }
}

function create_backup_stmt($targetfile=null)
{
    $oKTConfig =& KTConfig::getSingleton();

    $adminUser = $oKTConfig->get('db/dbAdminUser');
    $adminPwd = $oKTConfig->get('db/dbAdminPass');
    $dbHost = $oKTConfig->get('db/dbHost');
    $dbName = $oKTConfig->get('db/dbName');

    $dbPort = trim($oKTConfig->get('db/dbPort'));
    if (empty($dbPort) || $dbPort=='default') $dbPort = get_cfg_var('mysql.default_port');
    if (empty($dbPort)) $dbPort='3306';
    $dbSocket = trim($oKTConfig->get('db/dbSocket'));
    if (empty($dbSocket) || $dbSocket=='default') $dbSocket = get_cfg_var('mysql.default_socket');
    if (empty($dbSocket)) $dbSocket='../tmp/mysql.sock';

    $date=date('Y-m-d-H-i-s');

    $dir=$this->resolveMysqlDir();

    $info['dir']=$dir;

    $prefix='';
    if (OS_UNIX)
    {
        $prefix .= "./";
    }

    if (@stat($dbSocket) !== false)
    {
        $mechanism="--socket=\"$dbSocket\"";
    }
    else
    {
        $mechanism="--port=\"$dbPort\"";
    }

    $tmpdir=$this->resolveTempDir();

    if (is_null($targetfile))
    {
        $targetfile="$tmpdir/kt-backup-$date.sql";
    }

    $stmt = $prefix . "mysqldump --user=\"$adminUser\" -p $mechanism \"$dbName\" > \"$targetfile\"";
    $info['display']=$stmt;
    $info['target']=$targetfile;


    $stmt  = $prefix. "mysqldump --user=\"$adminUser\" --password=\"$adminPwd\" $mechanism \"$dbName\" > \"$targetfile\"";
    $info['cmd']=$stmt;
    return $info;
}

function resolveMysqlDir()
{
    // possibly detect existing installations:

    if (OS_UNIX)
    {
        $dirs = array('/opt/mysql/bin','/usr/local/mysql/bin');
        $mysqlname ='mysql';
    }
    else
    {
        $dirs = explode(';', $_SERVER['PATH']);
        $dirs[] ='c:/Program Files/MySQL/MySQL Server 5.0/bin';
        $dirs[] = 'c:/program files/ktdms/mysql/bin';
        $mysqlname ='mysql.exe';
    }

    $oKTConfig =& KTConfig::getSingleton();
    $mysqldir = $oKTConfig->get('backup/mysqlDirectory',$mysqldir);
    $dirs[] = $mysqldir;

    if (strpos(__FILE__,'knowledgeTree') !== false && strpos(__FILE__,'ktdms') != false)
    {
        $dirs [] = realpath(dirname($FILE) . '/../../mysql/bin');
    }

    foreach($dirs as $dir)
    {
        if (is_file($dir . '/' . $mysqlname))
        {
            return $dir;
        }
    }

    return '';
}

function resolveTempDir()
{
    if (OS_UNIX) {
        $dir='/tmp/kt-db-backup';
    }
    else {
        $dir='c:/kt-db-backup';
    }
    $oKTConfig =& KTConfig::getSingleton();
    $dir = $oKTConfig->get('backup/backupDirectory',$dir);

    if (!is_dir($dir)) {
            mkdir($dir);
    }
    return $dir;
}


function backupConfirm()
{
    $stmt = $this->create_backup_stmt();
    $_SESSION['backupFile'] = $stmt['target'];

    $dir = $stmt['dir'];
    $this->temp_variables['dir'] = $dir;
    $this->temp_variables['display'] = $stmt['display'];
}



}
?>