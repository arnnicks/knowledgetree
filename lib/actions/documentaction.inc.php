<?php
/**
 * $Id$
 *
 * KnowledgeTree Community Edition
 * Document Management Made Simple
 * Copyright (C) 2008, 2009, 2010 KnowledgeTree Inc.
 *
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
 * Contributor( s): ______________________________________
 *
 */

require_once(KT_LIB_DIR . '/actions/actionregistry.inc.php');
require_once(KT_LIB_DIR . '/workflow/workflowutil.inc.php');
require_once(KT_LIB_DIR . '/dispatcher.inc.php');
require_once(KT_LIB_DIR . '/browse/browseutil.inc.php');
require_once(KT_LIB_DIR . "/util/sanitize.inc");
require_once(KT_LIB_DIR . "/users/shareduserutil.inc.php");

/**
 * Base class for document actions within KnowledgeTree
 *
 * @author KnowledgeTree Team
 * @package KTDocumentActions
 */

class KTDocumentAction extends KTStandardDispatcher {
    public $sName;
    public $sDescription;

    public $_sShowPermission = 'ktcore.permissions.read';
    public $_sDisablePermission;
    public $bAllowInAdminMode = false;
    public $sHelpPage = 'ktcore/browse.html';

    public $sSection = 'view_details';
    /** Shared user mutators to deal with bypassing permissions */
	public $bShowIfReadShared = false;
	public $bShowIfWriteShared = false;

    /**
 	 * The _bMutator variable determines whether the action described by the class is considered a mutator.
     * Mutators may not act on Immutable documents unless overridden in the code
     * (e.g. by administrator action or permission)
     *
     * To be set in child class.
     *
     * Set this to false if you want an action to be available for immutable documents,
     * true if you want the action prevented for immutable documents.
 	 *
 	 * @access public
 	 * @var boolean
 	 */
    var $_bMutator = false;
    var $_bMutationAllowedByAdmin = true;

    var $sIconClass = '';
    var $sParentBtn = false;
    var $sBtnPosition = 'above';
    var $btnOrder = 5;

    function KTDocumentAction($oDocument = null, $oUser = null, $oPlugin = null) {
        $this->oDocument =& $oDocument;
        $this->oUser =& $oUser;
        $this->oPlugin =& $oPlugin;
        $this->oConfig =& KTConfig::getSingleton();
        $this->aBreadcrumbs = array(
            array('action' => 'browse', 'name' => _kt('Browse')),
        );
        $this->persistParams('fDocumentId');

        parent::KTStandardDispatcher();
    }

    function setDocument(&$oDocument) {
        $this->oDocument =& $oDocument;
    }

    function setUser(&$oUser) {
        $this->oUser =& $oUser;
    }

    function _show() {
    	// If this is a shared user the object permissions are different.
    	if (SharedUserUtil::isSharedUser()) {
    		return $this->shareduser_show();
    	}

        if (is_null($this->_sShowPermission)) {
            return true;
        }

        $folder = Folder::get($this->oDocument->getFolderId());

        if ($this->_bMutator && $this->oDocument->getImmutable()) {
            if ($this->_bMutationAllowedByAdmin === true) {
                if (!KTBrowseUtil::inAdminMode($this->oUser, $folder)) {
                    return false;
                }
            } else {
                return false;
            }
        }

        if ($this->_bAdminAlwaysAvailable && $this->hasAdminAccess()) {
            return true;
        }

        $permission =& KTPermission::getByName($this->_sShowPermission);
        if (PEAR::isError($permission)) {
            return true;
        }
        if (!KTWorkflowUtil::actionEnabledForDocument($this->oDocument, $this->sName)) {
            return false;
        }
        // be nasty in archive/delete status.
        $status = $this->oDocument->getStatusID();
        if (($status == DELETED) || ($status == ARCHIVED)) { return false; }

        if ($this->bAllowInAdminMode) {
            // check if this user is in admin mode
            if (KTBrowseUtil::inAdminMode($this->oUser, $folder)) {
                return true;
            }
        }

        return KTPermissionUtil::userHasPermissionOnItem($this->oUser, $permission, $this->oDocument);
    }

    // TODO May not be the best name?
    private function hasAdminAccess()
    {
        return Permission::userIsSystemAdministrator($this->oUser->getId())
            || Permission::isUnitAdministratorForFolder($this->oUser, $this->oDocument->getFolderId());
    }

    function getURL() {
        $oKTConfig =& KTConfig::getSingleton();
        $sExt = '.php';
        if (KTUtil::arrayGet($_SERVER, 'kt_no_extensions')) {
            $sExt = '';
        }
        if ($oKTConfig->get('KnowledgeTree/pathInfoSupport')) {
            return sprintf('%s/action%s/%s?fDocumentId=%d', $GLOBALS['KTRootUrl'], $sExt, $this->sName, $this->oDocument->getID());
        } else {
            return sprintf('%s/action%s?kt_path_info=%s&fDocumentId=%d', $GLOBALS['KTRootUrl'], $sExt, $this->sName, $this->oDocument->getID());
        }
    }

    function getOnClick()
    {
        return '';
    }

    function getInfo() {
        $check = $this->_show();
        if ($check === false) {
            $check = 'disabled';
        }

        $icon = $this->sIconClass;
        if ($check === 'disabled') {
            $url = '#';
            $onClick = '';
            //$icon = $this->sIconClass . ' disabled';
        } else {
            $url = $this->getURL();
            $onClick = $this->getOnClick();
        }

        $aInfo = array(
            'description' => $this->sDescription,
            'name' => $this->getDisplayName(),
            'ns' => $this->sName,
            'url' => $url,
            'onclick' => $onClick,
            'icon_class' => $icon,
            'parent_btn' => $this->sParentBtn,
            'btn_position' => $this->sBtnPosition,
            'btn_order' =>$this->btnOrder,
            'status' => $check
        );

        $aInfo = $this->customiseInfo($aInfo);
        return $aInfo;
    }

    function getName() {
        return $this->sName;
    }

    function getDisplayName() {
        // Should be overridden by the i18nised display name
        // This is here solely for backwards compatibility
        return $this->sDisplayName;
    }

    function getDescription() {
        return $this->sDescription;
    }

    function getButton(){
        return false;
    }

    function customiseInfo($aInfo) {
        return $aInfo;
    }

    function check() {
        $this->oDocument =& $this->oValidator->validateDocument($_REQUEST['fDocumentId']);

        if (!$this->_show()) { return false; }

        $aOptions = array('final' => false,
              'documentaction' => 'viewDocument',
              'folderaction' => 'browse',
        );

        $crumbs = KTBrowseUtil::breadcrumbsForDocument($this->oDocument, $aOptions);
        $this->aBreadcrumbs = kt_array_merge($this->aBreadcrumbs, $crumbs);

    	$actions = KTDocumentActionUtil::getDocumentActionsForDocument($this->oDocument, $this->oUser, 'documentinfo');
        $oPortlet = new KTActionPortlet(sprintf(_kt('Info')));
	    $oPortlet->setActions($actions, $this->sName);
	    $this->oPage->addPortlet($oPortlet);

    	$actions = KTDocumentActionUtil::getDocumentActionsForDocument($this->oDocument, $this->oUser);
        $oPortlet = new KTActionPortlet(sprintf(_kt('Actions')));
	    $oPortlet->setActions($actions, $this->sName);

	    $this->oPage->addPortlet($oPortlet);
	    $this->oPage->setSecondaryTitle($this->oDocument->getName());

        return true;
    }

    function do_main() {
        return _kt('Dispatcher component of action not implemented.');
    }

    /**
     * Check permissions on document for shared user
     *
     * @return unknown
     */
    function shareduser_show()
    {
		// Shared user would not have admin mode
		// Shared user would not be admin
		// Shared user permissions are stored in shared_content table
		// Check if deleted or archived document
        $status = $this->oDocument->getStatusID();
        if (($status == DELETED) || ($status == ARCHIVED)) { return false; }
        // Check if actions displays for both users
		if ($this->bShowIfReadShared && $this->bShowIfWriteShared) {
		    return true;
		}
		// Check if action does not have to be displayed
		else if (!$this->bShowIfReadShared && !$this->bShowIfWriteShared) {
		    return false;
		}
		// Check if action needs to be hidden
		else if (!$this->bShowIfReadShared && $this->getPermission() == 1) {
		    return true;
		}

		return false;
    }

    /**
     * Set the shared object permission
     *
     */
    function getPermission()
    {
		$iUserId = $this->oUser->getID();
		$iDocumentId = $this->oDocument->getID();
		$iFolderId = $this->oDocument->getFolderID();
		return SharedContent::getPermissions($iUserId, $iDocumentId, null, 'document');
    }

    function userHasDocumentReadPermission($oDocument)
    {
    	if(SharedUserUtil::isSharedUser())
    	{
    		$res = $this->getPermission();
    		if($res == 1) return true; elseif ($res == 0) return false; else return false;
    	}
    	else
    	{
    		return Permission::userHasDocumentReadPermission($oDocument);
    	}
    }

    public function do_reason() {
        $oTemplate = $this->oValidator->validateTemplate('ktcore/document/reason');
        $aTemplateData = array(
              'documentId' => $this->oDocument->getId(),
              'formAction' => $this->sName,
              'filename' => $this->oDocument->getFilename(),
              'action' => $this->getReasonAction(),
              'actionName' => $this->getDisplayName(),
              'descriptiveText' => $this->getReasonDescriptiveText(),
        );

        return $oTemplate->render($aTemplateData);
    }
}

class JavascriptDocumentAction extends KTDocumentAction
{
	/**
	 * This is an array of js files to be included for this action
	 *
	 * @var array
	 */
	var $js_paths = array();
	/**
	 * This is custom javascript that should be included
	 *
	 * @var array
	 */
	var $js = array();
	/**
	 * Indicates if a custom function should be provided, or if the function is part of an existing js file.
	 * If true
	 *
	 * @var boolean
	 */
	var $function_provided_by_action = true;

	/**
	 * Set the function name if you have a custom name you want to provide.
	 *
	 * @var string
	 */
	var $function_name = null;

	 function JavascriptDocumentAction($oDocument = null, $oUser = null, $oPlugin = null)
	 {
	 	parent::KTDocumentAction($oDocument, $oUser, $oPlugin);
	 	$this->js_initialise();
	 }

	function js_initialise()
	{
		// this will be overridden
	}

	function js_include($js)
	{
		$this->js[] = $js;
	}

	function js_include_file($path)
	{
		global $AjaxDocumentJSPaths;

		if (!isset($AjaxDocumentJSPaths))
		{
			$AjaxDocumentJSPaths = array();
		}

		if (!in_array($AjaxDocumentJSPaths))
		{
			$AjaxDocumentJSPaths[] = $path;
			$this->js_paths [] = $path;
		}
	}

	function customiseInfo($aInfo)
	{
		$js = '';
		foreach($this->js_paths as $path)
		{
			$js .= "<script language=\"javascript\" src=\"$path\"></script>\n";
		}

		$js .= '<script language="javascript">'. "\n";
		foreach($this->js as $js2)
		{
			$js .= $js2 . "\n";
		}

		$js .= $this->getScript() . '</script>'. "\n";

		$js .= '<div onclick="' . $this->getScriptActivation() . '"><a>' . $this->getDisplayName() . '</a></div>'. "\n";


		$aInfo['js'] = $js;

        return $aInfo;
    }

    function getScript()
    {
    	if ($this->function_provided_by_action === false)
    	{
    		return '';
    	}
    	return "function " . $this->getScriptActivation() . '{'.$this->getFunctionScript().'}';
    }

	function getFunctionScript()
	{
		return 'alert(\''. $this->getDisplayName()  .' not implemented!\');';
	}

    function getScriptActivation()
    {
    	if (!is_null($this->function_name))
    	{
    		return $this->function_name;
    	}

    	global $AjaxDocumentActions;
    	$class = get_class($this);
    	return 'js' .  $class. 'Dispatcher()';
    }
}

class KTDocumentActionUtil {
    function getDocumentActionInfo($slot = 'documentaction') {
        $oRegistry =& KTActionRegistry::getSingleton();
        return $oRegistry->getActions($slot);
    }

    static public function &getDocumentActionsForDocument(&$oDocument, $oUser, $slot = 'documentaction') {
        $aObjects = array();
        $actions = KTDocumentActionUtil::getDocumentActionInfo($slot);
        foreach ($actions as $aAction) {
            list($sClassName, $sPath, $sPlugin) = $aAction;
            $oRegistry =& KTPluginRegistry::getSingleton();
            $oPlugin =& $oRegistry->getPlugin($sPlugin);
            if (!empty($sPath)) {
                require_once($sPath);
            }
            $aObjects[] = new $sClassName($oDocument, $oUser, $oPlugin);
        }
        return $aObjects;
    }

    function getAllDocumentActions($slot = 'documentaction') {
        $aObjects = array();
        $oDocument = null;
        $oUser = null;
        foreach (KTDocumentActionUtil::getDocumentActionInfo($slot) as $aAction) {
            list($sClassName, $sPath, $sName, $sPlugin) = $aAction;
            $oRegistry =& KTPluginRegistry::getSingleton();
            $oPlugin =& $oRegistry->getPlugin($sPlugin);
            if (!empty($sPath)) {
                require_once($sPath);
            }
            $aObjects[] = new $sClassName($oDocument, $oUser, $oPlugin);
        }
        return $aObjects;
    }

    function getDocumentActionsByNames($aNames, $slot = 'documentaction', $oDocument = null, $oUser = null) {
        $aObjects = array();
        foreach (KTDocumentActionUtil::getDocumentActionInfo($slot) as $aAction) {
            list($sClassName, $sPath, $sName, $sPlugin) = $aAction;
            $oRegistry =& KTPluginRegistry::getSingleton();
            $oPlugin =& $oRegistry->getPlugin($sPlugin);
            if (!in_array($sName, $aNames)) {
                continue;
            }
            if (!empty($sPath)) {
                require_once($sPath);
            }
            $aObjects[] = new $sClassName($oDocument, $oUser, $oPlugin);
        }
        return $aObjects;
    }
}

?>
