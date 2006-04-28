<?php

/**
 * $Id$
 *
 * Copyright (c) 2006 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; using version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * -------------------------------------------------------------------------
 *
 * You can contact the copyright owner regarding licensing via the contact
 * details that can be found on the KnowledgeTree web site:
 *
 *         http://www.ktdms.com/
 */

error_reporting(E_ALL);

function get_php_setting($val) {
    $r =  (ini_get($val) == '1' ? 1 : 0);
    return $r ? 'ON' : 'OFF';
}

function boolSetting($name, $setting, $preferred, $red = true, $message = "") {
    $current = get_php_setting($setting);
    $ret = sprintf('<tr><td>%s (%s)</td><td>%s</td><td>', $name, $setting, $preferred);
    if ($current == $preferred) {
        $ret .= sprintf('<font color="green"><b>%s</b></font>', $current);
    } else {
        if ($red === true) {
            $ret .= sprintf('<font color="red"><b>%s</b></font>', $current);
        } else {
            $ret .= sprintf('<font color="orange"><b>%s</b></font>', $current);
        }
        if ($message) {
            $ret .= ' (' . $message . ')';
        }
    }
    $ret .= "</td></tr>\n";
    return $ret;
}

function stringSetting($name, $setting, $preferred, $red = true, $message = "") {
    $current = ini_get($setting);
    $ret = sprintf('<tr><td>%s (%s)</td><td>%s</td><td>', $name, $setting, $preferred);
    if ($current == $preferred) {
        $ret .= sprintf('<font color="green"><b>%s</b></font>', $current);
    } else {
        if ($red === true) {
            $ret .= sprintf('<font color="red"><b>%s</b></font>', $current);
        } else {
            $ret .= sprintf('<font color="orange"><b>%s</b></font>', $current);
        }
        if ($message) {
            $ret .= ' (' . $message . ')';
        }
    }
    $ret .= "</td></tr>\n";
    return $ret;
}

function emptySetting($name, $setting) {
    $current = ini_get($setting);
    $ret = sprintf('<tr><td>%s (%s)</td><td>unset</td><td>', $name, $setting);
    if (($current === false) or ($current === "")) {
        $ret .= sprintf('<font color="green"><b>unset</b></font>');
    } else {
        $ret .= sprintf('<font color="red"><b>Set: %s</b></font>', $current);
    }
    $ret .= "</td></tr>\n";
    return $ret;
}

function writablePath($name, $path) {
    $ret = sprintf('<tr><td>%s (%s)</td><td>', $name, $path);
    if (is_writable('../' . $path)) {
        $ret .= sprintf('<font color="green"><b>Writeable</b></font>');
    } else {
        $ret .= sprintf('<font color="red"><b>Unwriteable</b></font>');
    }
    return $ret;
}

function prettySizeToActualSize($pretty) {
    if (strtoupper(substr($pretty, strlen($pretty) - 1)) == 'G') {
        return (int)substr($pretty, 0, strlen($pretty)) * 1024 * 1024 * 1024;
    }
    if (strtoupper(substr($pretty, strlen($pretty) - 1)) == 'M') {
        return (int)substr($pretty, 0, strlen($pretty)) * 1024 * 1024;
    }
    if (strtoupper(substr($pretty, strlen($pretty) - 1)) == 'K') {
        return (int)substr($pretty, 0, strlen($pretty)) * 1024 * 1024;
    }
    return (int)$pretty;
}

function prettySize($v) {
    $v = (float)$v;
    foreach (array('B', 'K', 'M', 'G') as $unit) {
        if ($v < 1024) {
            return $v . $unit;
        }
        $v = $v / 1024;
    }
}

function get_php_int_setting($val) {
    $r = ini_get($val);
    if ($r === false) {
        return $r;
    }
    return prettySizeToActualSize($r);
}

function bigEnough($name, $setting, $preferred, $bytes = false, $red = true) {
    $current = get_php_int_setting($setting);
    if ($bytes === true) {
        $ret = sprintf('<tr><td>%s (%s)</td><td>%s</td><td>', $name, $setting, prettySize($preferred));
    } else {
        $ret = sprintf('<tr><td>%s (%s)</td><td>%s</td><td>', $name, $setting, $preferred);
    }

    if ($current === false) {
        $ret .= '<font color="green"><b>unset</b></font>';
    } else if ($current >= $preferred) {
        if ($bytes === true) {
            $ret .= sprintf('<font color="green"><b>%s</b></font>', prettySize($current));
        } else {
            $ret .= sprintf('<font color="green"><b>%s</b></font>', $current);
        }
    } else {
        if ($bytes === true) {
            $ret .= sprintf('<font color="red"><b>%s</b></font>', prettySize($current));
        } else {
            $ret .= sprintf('<font color="red"><b>%s</b></font>', $current);
        }
    }

    $ret .= "</td></tr>\n";
    return $ret;
}

function haveExtension($ext) {
    if (extension_loaded($ext)) {
        return true;
    }

    // According to PEAR.php:
    // if either returns true dl() will produce a FATAL error, stop that
    if ((ini_get('enable_dl') != 1) || (ini_get('safe_mode') == 1)) {
        return false;
    }

    $libfileext = '.so';
    $libraryprefix = '';
    if (substr(PHP_OS, 0, 3) == "WIN") {
        $libfileext = '.dll';
        $libraryprefix = 'php_';
    }
    @dl(sprintf("%s%s%s", $libraryprefix, $ext, $libfileext));
    return extension_loaded($ext);
}


function must_extension_loaded($ext, $message = "") {
    if (haveExtension($ext)) {
        return '<b><font color="green">Available</font></b>';
    }
    if ($message) {
        return '<b><font color="red">Unavailable</font></b> (' .  $message . ')';
    }
    return '<b><font color="red">Unavailable</font></b>';
}
function can_extension_loaded($ext, $message = "") {
    if (haveExtension($ext)) {
        return '<b><font color="green">Available</font></b>';
    }
    if ($message) {
    return '<b><font color="orange">Unavailable</font></b> (' . $message . ')';
    }
    return '<b><font color="orange">Unavailable</font></b>';
}

$phpversion4 = phpversion() < '4' ? '<b><font color="red">No</font></b> <small>(You need at least PHP 4)</small>' : '<b><font color="green">Yes</font></b>';
$phpversion43 = phpversion() < '4.3' ? '<b><font color="orange">No</font></b> <small>(PHP 4.3 is recommended)</small>' : '<b><font color="green">Yes</font></b>';
$phpversion5 = phpversion() >= '5' ? '<b><font color="red">No</font></b> <small>(KnowledgeTree does not yet work with PHP5)</small>' : '<b><font color="green">Yes</font></b>';

function running_user() {
    if (substr(PHP_OS, 0, 3) == "WIN") {
        return null;
    }
    if (extension_loaded("posix")) {
        $uid = posix_getuid();
        $userdetails = posix_getpwuid($uid);
        return $userdetails['name'];
    }
    if (file_exists('/usr/bin/whoami')) {
        return exec('/usr/bin/whoami');
    }
    if (file_exists('/usr/bin/id')) {
        return exec('/usr/bin/id -nu');
    }
    return null;
}

function htaccess() {
    if (array_key_exists('kt_htaccess_worked', $_SERVER)) {
        return '<p><strong><font color="green">Your web server is set up to use the .htaccess files.</font></strong></p>';
    }
    return '<p><strong><font color="red">Your web server is NOT set up to use the .htaccess files.</font></strong></p>';
}

?>
<html>
  <head>
    <title>KnowledgeTree Checkup</title>
    <style>
th { text-align: left; }
td { vertical-align: top; }
    </style>
  </head>

  <body>

<h1>KnowledgeTree Checkup</h1>

<p>This checkup allows you to check that your environment is ready to
support a KnowledgeTree installation, and that you can proceed to
configure your system.  Red items are things to fix.  Orange items means
you may not be having the ultimate experience unless the support is
added.  Green items means you're ready to go in this area.  You can
check back here to see if anything has changed in your environment if
you have any problems.</p>

<h2>.htaccess file</h2>

<p>You can let KnowledgeTree manage the PHP settings that apply to the
KnowledgeTree application (it won't affect your other applications) by
configuring your web server to use the .htaccess files that come with
KnowledgeTree.  This will ensure that the settings for KnowledgeTree
(detailed below) are set up for optimal, reliable performance.</p>

<?=htaccess()?>

<h2>PHP version and extensions</h2>

<p>This relates to your PHP installation environment - which version of
PHP you are running, and which modules are available.</p>

<table width="100%">
  <tbody>
    <tr>
      <th>PHP version 4.0 or above</th>
      <td><?=$phpversion4?></td>
    </tr>
    <tr>
      <th>PHP version 4.3 or above</th>
      <td><?=$phpversion43?></td>
    </tr>
    <tr>
      <th>PHP version below 5</th>
      <td><?=$phpversion5?></td>
    </tr>
    <tr>
      <th>Session support</th>
      <td><?=must_extension_loaded('session');?></td>
    </tr>
    <tr>
      <th>MySQL support</th>
      <td><?=must_extension_loaded('mysql');?></td>
    </tr>
    <tr>
      <th>Gettext support</th>
      <td><?=can_extension_loaded('gettext', "Only needed for using non-English languages");?></td>
    </tr>
    <tr>
      <th>Fileinfo support</th>
      <td><?=can_extension_loaded('fileinfo', "Provides better file identification support - not necessary if you use file extensions");?></td>
    </tr>
  </tbody>
</table>

<h2>PHP configuration</h2>

<p>This relates to the configuration of PHP on your system.</p>

<h3>Recommended settings</h3>

<table width="50%">
  <thead>
    <tr>
      <th>Configuration option</th>
      <th>Recommended value</th>
      <th>Current value</th>
    </tr>
  </thead>
  <tbody>
<?=boolSetting('Safe Mode','safe_mode','OFF')?>
<?=boolSetting('Display Errors','display_errors','ON', false, "Will be set correctly anyway.")?>
<?=boolSetting('Display Startup Errors','display_startup_errors','ON', false, "Will be set correctly anyway.")?>
<?=boolSetting('File Uploads','file_uploads','ON')?>
<?=boolSetting('Magic Quotes GPC','magic_quotes_gpc','OFF', false, "Quotes will be removed; not optimal")?>
<?=boolSetting('Magic Quotes Runtime','magic_quotes_runtime','OFF')?>
<?=boolSetting('Register Globals','register_globals','OFF', false, "Globals will be removed; not optimal, may be a security risk")?>
<?=boolSetting('Output Buffering','output_buffering','OFF')?>
<?=boolSetting('Session auto start','session.auto_start','OFF')?>
<?=emptySetting('Automatic prepend file','auto_prepend_file')?>
<?=emptySetting('Automatic append file','auto_append_file')?>
<?=emptySetting('Open base directory','open_basedir')?>
<?=stringSetting('Default MIME type', 'default_mimetype', 'text/html')?>
  </tbody>
</table>

<h3>Limits</h3>

<table width="50%">
  <thead>
    <tr>
      <th>Configuration option</th>
      <th>Recommended value</th>
      <th>Current value</th>
    </tr>
  </thead>
  <tbody>
<?=bigEnough('Maximum POST size', 'post_max_size', 32 * 1024 * 1024, true)?>
<?=bigEnough('Maximum upload size', 'upload_max_filesize', 32 * 1024 * 1024, true)?>
<?=bigEnough('Memory limit', 'memory_limit', 32 * 1024 * 1024, true)?>
<?=""; # bigEnough('Maximum execution time', 'max_execution_time', 30)?>
<?=""; # bigEnough('Maximum input time', 'max_input_time', 60)?>
  <tbody>
</table>


<h3>Paths</h3>
<table width="50%">
  <tbody>
    <tr>
      <td class="item">
      Session save path
      </td>
      <td align="left">
      <b><?php echo (($sp=ini_get('session.save_path'))?$sp:'Not set'); ?></b>,
      <?php echo is_writable( $sp ) ? '<b><font color="green">Writeable</font></b>' : '<b><font color="red">Unwriteable</font></b>';?>
      </td>
    </tr>
    <tr>
      <td class="item">
      Upload temporary path
      </td>
      <td align="left">
      <b><?php echo (($sp=ini_get('upload_tmp_dir'))?$sp:'Not set'); ?></b>
      <?php if ($sp) { echo ', ' . is_writable( $sp ) ? '<b><font color="green">Writeable</font></b>' : '<b><font color="red">Unwriteable</font></b>';} ?>
      </td>
    </tr>
  </tbody>
</table>

<h2>Filesystem</h2>

<table width="50%">
  <tbody>
<?php
$username = running_user();
if (is_null($username)) {
    $message = "You are on a system that does not make user details available, and so no advice is possible on the correct ownership of the <b>log</b> and <b>Documents</b> directories.";
} else {
    $message = 'KnowledgeTree will be run as the <b><font color="orange">' . $username . '</font></b> system user, and must be able to write to the <b>log</b> and <b>Documents</b> directories.';
}
?>
<tr>
<td width="33%">General</td>
<td><?=$message?></td>
</tr>
  </tbody>
</table>

<h2>Post-installation checkup</h2>

<p>Once you have installed, check the <a href="postcheckup.php">Post-installation checkup</a>.</p>
  
  </body>
</html>
