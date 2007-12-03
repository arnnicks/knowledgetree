<?php
/**
 * $Id$
 *
 * KnowledgeTree Open Source Edition
 * Document Management Made Simple
 * Copyright (C) 2004 - 2007 The Jam Warehouse Software (Pty) Limited
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
 * You can contact The Jam Warehouse Software (Pty) Limited, Unit 1, Tramber Place,
 * Blake Street, Observatory, 7925 South Africa. or email info@knowledgetree.com.
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
 *
 * -------------------------------------------------------------------------
 *
 * KT3 Template Base
 *
 * Represents core UI logic, including how sub-components interact with
 * the overall page.
 *
 * For the meaning of each of the variables and functions, see inline.
 *
 */

require_once(KT_LIB_DIR . "/plugins/pluginregistry.inc.php");
require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/session/control.inc");
require_once(KT_DIR . '/search2/search/search.inc.php');

class KTPage {
    var $hide_section = false;
	var $secondary_title = null;

    /** resources are "filename"->1 to allow subcomponents to require items. */
    var $js_resources = Array();
    var $css_resources = Array();
    var $theme_css_resources = Array();
	var $ie_only_css = Array();
	var $theme_ie_only_css = Array();
    var $js_standalone = Array();
    var $css_standalone = Array();

	/** context-relevant information */
	var $errStack = Array();
    var $infoStack = Array();
	var $portlets = Array();
	var $show_portlets = true;

    /** miscellaneous items */
    var $title = '';
    var $systemName = APP_NAME;
    var $systemURL = 'http://www.knowledgetree.com/';
    var $breadcrumbs = false;
    var $breadcrumbDetails = false;
    var $breadcrumbSection = false;
    var $menu = null;
    var $userMenu = null;
    var $helpPage = null;

    /** the "component".  Used to set the page header (see documentation for explanation). */
    var $componentLabel = 'Browse Documents';
    var $componentClass = 'browse_collections';

    /** $contents is the center of the page.  In KT < 3, this was CentralPayload. */
    var $contents = '';

    var $template = "kt3/standard_page";

    var $contentType = 'text/html';
    var $charset = 'UTF-8';

    var $content_class;

    /* further initialisation */
    function KTPage() {
        global $default;
        $oConfig = KTConfig::getSingleton();

        /* default css files initialisation */
        $aCSS = Array(
           "resources/css/kt-framing.css",
           "resources/css/kt-contenttypes.css",
           "resources/css/kt-headings.css"
        );
        $this->requireCSSResources($aCSS);

        if($oConfig->get('ui/morphEnabled') == '1'){
        	$morphTheme = $oConfig->get('ui/morphTo');
        	$this->requireThemeCSSResource('skins/kts_'.$oConfig->get('ui/morphTo').'/kt-morph.css');
        	$this->requireThemeCSSResource('skins/kts_'.$oConfig->get('ui/morphTo').'/kt-ie-morph.css', true);
        }
        // IE only
        $this->requireCSSResource("resources/css/kt-ie-icons.css", true);

        /* default js files initialisation */
        $aJS = Array();

		$aJS[] = 'thirdpartyjs/yui/yahoo/yahoo.js';
		$aJS[] = 'thirdpartyjs/yui/utilities/utilities.js';
        /*
        $aJS[] = 'thirdpartyjs/MochiKit/MochiKit.js';
	$aJS[] = 'thirdpartyjs/MochiKit/New.js';
	$aJS[] = 'thirdpartyjs/MochiKit/DragAndDrop.js';
	$aJS[] = 'thirdpartyjs/MochiKit/Sortable.js';
	$aJS[] = 'thirdpartyjs/MochiKit/Style.js';
        */
		$aJS[] = 'thirdpartyjs/MochiKit/MochiKitPacked.js';
        $aJS[] = 'resources/js/kt-utility.js';
        $aJS[] = 'presentation/i18nJavascript.php';
        $aJS[] = 'thirdpartyjs/curvycorners/rounded_corners.inc.js';
        $aJS[] = 'resources/js/loader.js';
        $aJS[] = 'thirdpartyjs/yui/tools/tools.js';
        $aJS[] = 'thirdpartyjs/yui/connection/connection.js';



        //$aJS[] = 'thirdpartyjs/MochiKit/.js';
        //$aJS[] = 'resources/js/translate.js';

        $this->requireJSResources($aJS);

        // this is horrid, but necessary.
		$this->requireJSStandalone('addLoadEvent(partial(initDeleteProtection, "' . _kt('Are you sure you wish to delete this item?') . '"));');

        /* menu initialisation*/
        // FIXME:  how do we want to handle the menu?
        $this->initMenu();

        /* portlet initialisation */
        $this->show_portlets = true;
        /* breadcrumbs */
    }

	// initiliase the menu.
    function initMenu() {
    	// FIXME:  we lost the getDefaultAction stuff - do we care?
    	// note that key == action. this is _important_, since we crossmatch the breadcrumbs against this for "active"
    	$this->menu = array();
    	$this->menu['dashboard'] = array('label' => _kt("Dashboard"), 'url' => 'dashboard.php');
		$this->menu['browse'] = array('label' => _kt("Browse Documents"), 'url' => 'browse.php');
		$this->menu['administration'] = array('label' => _kt("DMS Administration"), 'url' => 'admin.php');
    	/*
    	$this->menu = array("dashboard" => $this->_actionHelper(array("name" => _kt("Dashboard"), "action" => "dashboard", "active" => 0)),
    			    "browse" => $this->_actionHelper(array("name" => _kt("Browse Documents"), "action" => "browse", "active" => 0)),
    			    "administration" => $this->_actionHelper(array("name" => _kt("DMS Administration"), "action" => "administration", "active" => 0)),);
    	*/
    }


    function setTitle($sTitle) {
	$this->title = $sTitle;
    }

    /* javascript handling */
    // require that the specified JS file is referenced.
    function requireJSResource($sResourceURL) {
	$this->js_resources[$sResourceURL] = 1; // use the keys to prevent multiple copies.
    }

    // require that the specified JS files are referenced.
    function requireJSResources($aResourceURLs) {
        foreach ($aResourceURLs as $sResourceURL) {
            $this->js_resources[$sResourceURL] = 1;
        }
    }

    // list the distinct js resources.
    function getJSResources() {
        return array_keys($this->js_resources);
    }

    function requireJSStandalone($sJavascript) {
        $this->js_standalone[$sJavascript] = 1; // use the keys to prevent multiple copies.
    }
    // list the distinct js resources.
    function getJSStandalone() {
        return array_keys($this->js_standalone);
    }

    /* css handling */
    // require that the specified CSS file is referenced.
    function requireCSSResource($sResourceURL, $ieOnly = false) {
        if ($ieOnly !== true) {
            $this->css_resources[$sResourceURL] = 1; // use the keys to prevent multiple copies.
		} else {
		    $this->ie_only_css[$sResourceURL] = 1;
		}
    }

    // require that the specified CSS file is referenced.
    function requireThemeCSSResource($sResourceURL, $ieOnly = false) {
        if ($ieOnly !== true) {
            $this->theme_css_resources[$sResourceURL] = 1; // use the keys to prevent multiple copies.
		} else {
		    $this->theme_ie_only_css[$sResourceURL] = 1;
		}
    }

    // require that the specified CSS files are referenced.
    function requireCSSResources($aResourceURLs) {
        foreach ($aResourceURLs as $sResourceURL) {
            $this->css_resources[$sResourceURL] = 1;
        }
    }

    // list the distinct CSS resources.
    function getCSSResources() {
        return array_keys($this->css_resources);
    }

    // list the distinct CSS resources.
    function getThemeCSSResources() {
        return array_keys($this->theme_css_resources);
    }

	function getCSSResourcesForIE() {
        return array_keys($this->ie_only_css);
    }

    function getThemeCSSResourcesForIE() {
        return array_keys($this->theme_ie_only_css);
    }

    function requireCSSStandalone($sCSS) {
        $this->css_standalone[$sCSS] = 1;
    }

    function getCSSStandalone() {
        return array_keys($this->css_standalone);
    }

    function setPageContents($contents) { $this->contents = $contents; }
    function setShowPortlets($bShow) { $this->show_portlets = $bShow; }

    /* set the breadcrumbs.  the first item is the area name.
       the rest are breadcrumbs. */
    function setBreadcrumbs($aBreadcrumbs) {
        $breadLength = count($aBreadcrumbs);
        if ($breadLength != 0) {
            $this->breadcrumbSection = $this->_actionhelper($aBreadcrumbs[0]);
	    // handle the menu
	    if (($aBreadcrumbs[0]["action"]) && ($this->menu[$aBreadcrumbs[0]["action"]])) {
		$this->menu[$aBreadcrumbs[0]["action"]]["active"] = 1;
	    }
        }
        if ($breadLength > 1) {
            $this->breadcrumbs = array_map(array(&$this, "_actionhelper"), array_slice($aBreadcrumbs, 1));
        }
    }

    function setBreadcrumbDetails($sBreadcrumbDetails) { $this->breadcrumbDetails = $sBreadcrumbDetails; }
	function setUser($oUser) { $this->user = $oUser; }

    function setContentClass($sClass) { $this->content_class = $sClass; }

    // FIXME refactor setSection to be generic, not an if-else.
    // assume this is admin for now.
    function setSection($sSection) {
	    if ($sSection == 'administration') {
			$this->componentLabel = _kt('DMS Administration');
			$this->componentClass = 'administration';
			$this->menu['administration']['active'] = 1;
		} else if ($sSection == 'dashboard') {
		    $this->componentLabel = _kt('Dashboard');
            $this->componentClass = 'dashboard';
		} else if ($sSection == 'browse') {
		    $this->componentLabel = _kt('Browse Documents');
            $this->componentClass = 'browse_collections';
		} else if ($sSection == 'view_details') {
		    $this->componentLabel = _kt('Document Details');
            $this->componentClass = 'document_details';
		} else if ($sSection == 'search') {
		    $this->componentLabel = _kt('Search');
            $this->componentClass = 'search';
		} else if ($sSection == 'preferences') {
		    $this->componentLabel = _kt('Preferences');
            $this->componentClass = 'preferences';
	    } else {
			$this->componentLabel = _kt('Dashboard');
			$this->componentClass = 'dashboard';
		}

	}

	function addError($sError) { array_push($this->errStack, $sError); }
	function addInfo($sInfo) { array_push($this->infoStack, $sInfo); }

	/** no-one cares what a portlet is, but it should be renderable, and have its ->title member set. */
	function addPortlet($oPortlet) {
	    array_push($this->portlets, $oPortlet);
	}

	/* LEGACY */
	var $deprecationWarning = "Legacy UI API: ";
	function setCentralPayload($sCentral) {
	    $this->contents = $sCentral;
		$this->addError($this->deprecationWarning . "called <strong>setCentralPayload</strong>");
	}

	function setOnloadJavascript($appendix) { $this->addError($this->deprecationWarning . "called <strong>setOnloadJavascript (no-act)</strong>"); }
	function setDHtmlScrolling($appendix) { $this->addError($this->deprecationWarning . "called <strong>setDHTMLScrolling (no-act)</strong>"); }
	function setFormAction($appendix) { $this->addError($this->deprecationWarning . "called <strong>setFormAction (no-act)</strong>"); }
	function setSubmitMethod($appendix) { $this->addError($this->deprecationWarning . "called <strong>setSubmitMethod (no-act)</strong>"); }
	function setHasRequiredFields($appendix) { $this->addError($this->deprecationWarning . "called <strong>setHasRequiredFields (no-act)</strong>"); }
	function setAdditionalJavascript($appendix) { $this->addError($this->deprecationWarning . "called <strong>setAdditionalJavascript (no-act)</strong>"); }

	function hideSection() { $this->hide_section = true; }
	function setSecondaryTitle($sSecondary) { $this->secondary_title = $sSecondary; }

    /* final render call. */
    function render() {
	global $default;
        $oConfig = KTConfig::getSingleton();

        if (empty($this->contents)) {
            $this->contents = "";
        }

        if (is_string($this->contents) && (trim($this->contents) === "")) {
            $this->addError(_kt("This page did not produce any content"));
            $this->contents = "";
        }

	if (!is_string($this->contents)) {
	    $this->contents = $this->contents->render();
	}

	// if we have no portlets, make the ui a tad nicer.
	if (empty($this->portlets)) {
	    $this->show_portlets = false;
	}

	if (empty($this->title)) {
	    if (!empty($this->breadcrumbDetails)) {
		$this->title = $this->breadcrumbDetails;
	    } else if (!empty($this->breadcrumbs)) {
		$this->title = array_slice($this->breadcrumbs, -1);
		$this->title = $this->title[0]['label'];
	    } else if (!empty($this->breadcrumbSection)) {
		$this->title = $this->breadcrumbSection['label'];
	    } else {
		$this->title = $this->componentLabel;
	    }
	}

	$this->userMenu = array();
	if (!(PEAR::isError($this->user) || is_null($this->user) || $this->user->isAnonymous())) {
	    if ($oConfig->get("user_prefs/restrictPreferences", false) && !Permission::userIsSystemAdministrator($this->user->getId())) {
		$this->userMenu = array("logout" => $this->_actionHelper(array("name" => _kt("Logout"), "action" => "logout", "active" => 0)),);
	    } else {
		$this->userMenu = array("preferences" => $this->_actionHelper(array("name" => _kt("Preferences"), "action" => "preferences", "active" => 0)),
					"aboutkt" => $this->_actionhelper(array("name" => _kt("About"), "action" => "aboutkt", "active" => 0)),
					"logout" => $this->_actionHelper(array("name" => _kt("Logout"), "action" => "logout", "active" => 0)),);
	    }
	} else {
	    $this->userMenu = array("login" => $this->_actionHelper(array("name" => _kt("Login"), "action" => "login")),);
	}

	// FIXME we need a more complete solution to navigation restriction
	if (!is_null($this->menu['administration']) && !is_null($this->user)) {
	    if (!Permission::userIsSystemAdministrator($this->user->getId())) {
		unset($this->menu['administration']);
	    }
	}

	$sContentType = 'Content-type: ' . $this->contentType;
	if(!empty($this->charset)) {
	    $sContentType .= '; charset=' . $this->charset;
	};


	header($sContentType);

	$savedSearches = SearchHelper::getSavedSearches($_SESSION['userID']);

        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate($this->template);
        $aTemplateData = array(
        			"page" => $this,
			       	"systemversion" => $default->systemVersion,
			       	"versionname" => $default->versionName,
					'smallVersion' => substr($default->versionName, 0, 3),
			       	'savedSearches'=> $savedSearches);
        if ($oConfig->get("ui/automaticRefresh", false)) {
            $aTemplateData['refreshTimeout'] = (int)$oConfig->get("session/sessionTimeout") + 3;
        }

        // unlike the rest of KT, we use echo here.
        echo $oTemplate->render($aTemplateData);
    }


	/**   heler functions */
	// returns an array ("url", "label")
    function _actionhelper($aActionTuple) {
        $aTuple = Array("label" => $aActionTuple["name"]);
        if ($aActionTuple["action"]) {
           $aTuple["url"] = generateControllerLink($aActionTuple["action"], $aActionTuple["query"]);
        } else if ($aActionTuple["url"]) {
           $sUrl = $aActionTuple["url"];
           $sQuery = KTUtil::arrayGet($aActionTuple, 'query');
           if ($sQuery) {
               $sUrl = KTUtil::addQueryString($sUrl, $sQuery);
           }
		   $aTuple["url"] = $sUrl;
        } else if ($aActionTuple["query"]) {
           $aTuple['url'] = KTUtil::addQueryStringSelf($aActionTuple["query"]);
		} else {
		   $aTuple["url"] = false;
		}

		return $aTuple;
    }

    function setHelp($sHelpPage) {
	$this->helpPage = $sHelpPage;
    }

    function getHelpURL() {
	if (empty($this->helpPage)) {
	    return null;
	}

	return KTUtil::ktLink('help.php',$this->helpPage);
    }

    function getReqTime() {
        $microtime_simple = explode(' ', microtime());
        $finaltime = (float) $microtime_simple[1] + (float) $microtime_simple[0];
	return sprintf("%.3f", ($finaltime - $GLOBALS['_KT_starttime']));
    }

    function getDisclaimer() {
        $oRegistry =& KTPluginRegistry::getSingleton();
        $oPlugin =& $oRegistry->getPlugin('ktstandard.disclaimers.plugin');
        if (!PEAR::isError($oPlugin) && !is_null($oPlugin)) {
            return $oPlugin->getPageDisclaimer();
        } else {
            return;
        }
    }

}

?>
