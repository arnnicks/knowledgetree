<?php

// main library routines and defaults
require_once("../config/dmsDefaults.php");
require_once(KT_LIB_DIR . '/templating/templating.inc.php');
require_once(KT_LIB_DIR . '/session/control.inc');
require_once(KT_LIB_DIR . '/session/Session.inc');
require_once(KT_LIB_DIR . '/users/User.inc');

/**
 * $Id$
 *  
 * This page handles logging a user into the dms.
 * This page displays the login form, and performs the business logic login processing.
 *
 * Copyright (c) 2003 Jam Warehouse http://www.jamwarehouse.com
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
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
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 */

class LoginPageDispatcher extends KTDispatcher {

    function check() {
        // bounce out immediately.
		$session = new Session();
		if ($session->verify() == 1) { // erk.  neil - DOUBLE CHECK THIS PLEASE.
			exit(redirect(generateControllerLink('dashboard')));
		} else {
		    $session->destroy(); // toast it - its probably a hostile session.
		}
		return true;
	}

	function do_main() {
	    $this->check(); // bounce here, potentially.
	
		$cookietest = KTUtil::randomString();
		setcookie("CookieTestCookie", $cookietest, false);
		
		$errorMessage = KTUtil::arrayGet($_REQUEST, 'errorMessage');
		
		$oTemplating = new KTTemplating;
		$oTemplate = $oTemplating->loadTemplate("ktcore/login");
		$aTemplateData = array(
              "context" => $this,
			  'cookietest' => $cookietest,
			  'errorMessage' => $errorMessage,
		);
		return $oTemplate->render($aTemplateData);		
	}
	
	function simpleRedirectToMain($errorMessage, $url, $params) {
		$params[] = 'errorMessage='. urlencode($errorMessage);
		$url .= '?' . join('&', $params);
		redirect($url);
		exit(0);
	}
	
	function do_login() {
	    $this->check();
		global $default;
		
		$redirect = KTUtil::arrayGet($_REQUEST, 'redirect');
		
		$url = $_SERVER["PHP_SELF"];
		$queryParams = array();
		
		if ($redirect !== null) {
		    $queryParams[] = 'redirect='. urlencode($redirect);
		}		
		
		
	    $cookieTest = KTUtil::arrayGet($_COOKIE, "CookieTestCookie", null);
		$cookieVerify = KTUtil::arrayGet($_REQUEST, 'cookieverify', null);
		
		if (($cookieVerify === null) || ($cookieTest !== $cookieVerify)) {
		    $this->simpleRedirectToMain('You must have cookies enabled to use the KnowledgeTree.', $url, $params);
		    exit(0);
		}
		
		$username = KTUtil::arrayGet($_REQUEST,'username');
		$password = KTUtil::arrayGet($_REQUEST,'password');
		
		if (empty($username)) {
		    $this->simpleRedirectToMain('Please enter your username.', $url, $params);
		}
		
		if (empty($password)) {
		    $this->simpleRedirectToMain('Please enter your username.', $url, $params);
		}
		
        $dbAuth = new $default->authenticationClass; // $default.  urk.
        $userDetails = $dbAuth->login($username, $password);		
			
		
        switch ($userDetails["status"]) {
        case 0: // bad credentials
            $this->simpleRedirectToMain('Login failed.  Please check your username and password, and try again.', $url, $params);
			break; 
        case 1: // successfully authenticated
            // start the session
            $session = new Session();
            $sessionID = $session->create($userDetails["userID"]);

            // DEPRECATED initialise page-level authorisation array
            $_SESSION["pageAccess"] = NULL; 

            // check for a location to forward to
            if ($redirect !== null) {
                // remove any params from redirect before looking up from sitemap
                if (strstr($redirect, "?")) {
                    $queryString = substr($redirect, strpos($redirect, "?")+1, strlen($redirect));
                    $redirect = substr($redirect, 0, strpos($redirect, "?"));
                }

                // need to strip rootUrl off $redirect
                if (strlen($default->rootUrl) > 0) {
                    $redirect = substr($redirect, strpos($redirect, $default->rootUrl)+strlen($default->rootUrl), strlen($redirect));
                }
                $action = $default->siteMap->getActionFromPage($redirect);
                if ($action) {
                    $url = generateControllerUrl($action);
                } else {
                    // default to the dashboard
                    $url = generateControllerUrl("dashboard");
                }
            // else redirect to the dashboard if there is none
            } else {
                $url = generateControllerUrl("dashboard");
            }
			exit(redirect($url));
            break;
            // login disabled
        case 2:
            $this->simpleRedirectToMain("Account has been DISABLED, contact the System Adminstrator", $url, $params);
            break;
            // too many sessions
        case 3 :
            $this->simpleRedirectToMain(_("Maximum sessions for user reached.<br>Contact the System Administrator"), $url, $params);
            break;
            // not a unit user
        case 4 :
            $this->simpleRedirectToMain(_("This user does not belong to a group and is therefore not allowed to log in."), $url, $params);;
            break;            
        default :
            $this->simpleRedirectToMain(_("Login failure"), $url, $params);
        }		
	    // we should not get here.
	    $this->simpleRedirectToMain(_("Unable to start session.  Please contact the administrator."), $url, $params);
	}
}


$dispatcher =& new LoginPageDispatcher();
$dispatcher->dispatch();

?>