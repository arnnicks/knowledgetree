<?php
/**
 * $Id$
 *
 * This page handles logging a user into the dms.
 * This page displays the login form, and performs the business logic login processing.
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
 */

// main library routines and defaults
require_once("config/dmsDefaults.php");
require_once(KT_LIB_DIR . '/templating/templating.inc.php');
require_once(KT_LIB_DIR . '/session/control.inc');
require_once(KT_LIB_DIR . '/session/Session.inc');
require_once(KT_LIB_DIR . '/users/User.inc');
require_once(KT_LIB_DIR . '/authentication/authenticationutil.inc.php');
require_once(KT_LIB_DIR . '/help/help.inc.php');
require_once(KT_LIB_DIR . '/help/helpreplacement.inc.php');
require_once(KT_LIB_DIR . '/authentication/interceptorregistry.inc.php');
require_once(KT_LIB_DIR . '/widgets/fieldWidgets.php');

class LoginPageDispatcher extends KTDispatcher {

    function check() {
        $oKTConfig = KTConfig::getSingleton();
        $this->session = new Session();
        $sessionStatus = $this->session->verify();
        if ($sessionStatus === true) { // the session is valid
            if ($_SESSION['userID'] == -2 && $oKTConfig->get('allowAnonymousLogin', false)) {
                ; // that's ok - we want to login.
            }
            else {
                // User is already logged in - get the redirect
                $redirect = KTUtil::arrayGet($_REQUEST, 'redirect');

                $cookietest = KTUtil::randomString();
                setcookie("CookieTestCookie", $cookietest, 0);

                $this->redirectTo('checkCookie', array(
                    'cookieVerify' => $cookietest,
                    'redirect' => $redirect,
                ));
                exit(0);
                // The old way -> doesn't take the redirect into account
                //exit(redirect(generateControllerLink('dashboard')));
            }
        }
        return true;
    }

    function do_providerVerify() {
        $this->session = new Session();
        $sessionStatus = $this->session->verify();
        if ($sessionStatus !== true) { // the session is not valid
            $this->redirectToMain();
        }
        $this->oUser =& User::get($_SESSION['userID']);
        $oProvider =& KTAuthenticationUtil::getAuthenticationProviderForUser($this->oUser);
        $oProvider->subDispatch($this);
        exit(0);
    }

    function performLogin(&$oUser) {
        if (!is_a($oUser, 'User')) {
            #var_dump($oUser);
            #var_dump(PEAR::raiseError());
        }
        $iOldUserID = checkLastSessionUserID();
        
        //if the current person logging in isn't the same person who logged out or timed out
        //then set the redirect to the dashboard and not the last page that was viewed.
        if ($oUser->getId() != $iOldUserID['user_id'])
        {
        	$_REQUEST['redirect'] = generateControllerLink('dashboard');
        	
        }
        
        $session = new Session();
        $sessionID = $session->create($oUser);
        if (PEAR::isError($sessionID)) {
            return $sessionID;
        }
		
		$redirect = KTUtil::arrayGet($_REQUEST, 'redirect');
        
        // DEPRECATED initialise page-level authorisation array
        $_SESSION["pageAccess"] = NULL;

        $cookietest = KTUtil::randomString();
        setcookie("CookieTestCookie", $cookietest, 0);

        $this->redirectTo('checkCookie', array(
            'cookieVerify' => $cookietest,
            'redirect' => $redirect,
        ));
        exit(0);
    }

    function do_main() {
        global $default;

        $oUser =& KTInterceptorRegistry::checkInterceptorsForAuthenticated();
        if (is_a($oUser, 'User')) {
            $res = $this->performLogin($oUser);
            if ($res) {
                $oUser = array($res);
            }
        }
        if (is_array($oUser) && count($oUser)) {
            if (empty($_REQUEST['errorMessage'])) {
                $_REQUEST['errorMessage'] = array();
            } else {
                $_REQUEST['errorMessage'] = array($_REQUEST['errorMessage']);
            }
            foreach ($oUser as $oError) {
                $_REQUEST['errorMessage'][] = $oError->getMessage();
            }
            $_REQUEST['errorMessage'] = join('. <br /> ', $_REQUEST['errorMessage']);
        }


        KTInterceptorRegistry::checkInterceptorsForTakeOver();

        $this->check(); // bounce here, potentially.
        header('Content-type: text/html; charset=UTF-8');

        $errorMessage = KTUtil::arrayGet($_REQUEST, 'errorMessage');
        session_start();
        
        $errorMessageConfirm = $_SESSION['errormessage']['login'];
        
        $redirect = KTUtil::arrayGet($_REQUEST, 'redirect');

        $oReg =& KTi18nregistry::getSingleton();
        $aRegisteredLangs = $oReg->geti18nLanguages('knowledgeTree');
        $aLanguageNames = $oReg->getLanguages('knowledgeTree');
        $aRegisteredLanguageNames = array();
        foreach (array_keys($aRegisteredLangs) as $sLang) {
            $aRegisteredLanguageNames[$sLang] = $aLanguageNames[$sLang];
        }
        $sLanguageSelect = $default->defaultLanguage;

        // extra disclaimer, if plugin is enabled
        $oRegistry =& KTPluginRegistry::getSingleton();
        $oPlugin =& $oRegistry->getPlugin('ktstandard.disclaimers.plugin');
        if (!PEAR::isError($oPlugin) && !is_null($oPlugin)) {
            $sDisclaimer = $oPlugin->getLoginDisclaimer();
        }

        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("ktcore/login");
        $aTemplateData = array(
              "context" => $this,
              'errorMessage' => $errorMessage,
              'errorMessageConfirm' => $errorMessageConfirm,
              'redirect' => $redirect,
              'systemVersion' => $default->systemVersion,
              'versionName' => $default->versionName,
              'languages' => $aRegisteredLanguageNames,
              'selected_language' => $sLanguageSelect,
	      	  'disclaimer' => $sDisclaimer,
			  'smallVersion' => substr($default->versionName, 0, 3),
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
        $aExtra = array();
        $oUser =& KTInterceptorRegistry::checkInterceptorsForAuthenticated();
        if (is_a($oUser, 'User')) {
            $res = $this->performLogin($oUser);
            if ($res) {
                $oUser = array($res);
            }
        }
        if (is_array($oUser)) {
            foreach ($oUser as $oError) {
                if (is_a($oError, 'KTNoLocalUser')) {
                    $aExtra = kt_array_merge($aExtra, $oError->aExtra);
                }
            }
        }

        KTInterceptorRegistry::checkInterceptorsForTakeOver();

        $this->check();
        global $default;

        $language = KTUtil::arrayGet($_REQUEST, 'language');
        if (empty($language)) {
            $language = $default->defaultLanguage;
        }
        setcookie("kt_language", $language, 2147483647, '/');

        $redirect = KTUtil::arrayGet($_REQUEST, 'redirect');

        $url = $_SERVER["PHP_SELF"];
        $queryParams = array();

        if ($redirect !== null) {
            $queryParams[] = 'redirect=' . urlencode($redirect);
        }

        $username = KTUtil::arrayGet($_REQUEST,'username');
        $password = KTUtil::arrayGet($_REQUEST,'password');

        if (empty($username)) {
            $this->simpleRedirectToMain(_kt('Please enter your username.'), $url, $queryParams);
        }

        $oUser =& User::getByUsername($username);
        if (PEAR::isError($oUser) || ($oUser === false)) {
            if (is_a($oUser, 'ktentitynoobjects')) {
                $this->handleUserDoesNotExist($username, $password, $aExtra);
            }
            $this->simpleRedirectToMain(_kt('Login failed.  Please check your username and password, and try again.'), $url, $queryParams);
            exit(0);
        }

        if (empty($password)) {
            $this->simpleRedirectToMain(_kt('Please enter your password.'), $url, $queryParams);
        }

        $authenticated = KTAuthenticationUtil::checkPassword($oUser, $password);

        if (PEAR::isError($authenticated)) {
            $this->simpleRedirectToMain(_kt('Authentication failure.  Please try again.'), $url, $queryParams);
            exit(0);
        }

        if ($authenticated !== true) {
            $this->simpleRedirectToMain(_kt('Login failed.  Please check your username and password, and try again.'), $url, $queryParams);
            exit(0);
        }

        $res = $this->performLogin($oUser);

        if ($res) {
            $this->simpleRedirectToMain($res->getMessage(), $url, $queryParams);
            exit(0);
        }
    }

    function handleUserDoesNotExist($username, $password, $aExtra = null) {
        if (empty($aExtra)) {
            $aExtra = array();
        }
        $res = KTAuthenticationUtil::autoSignup($username, $password, $aExtra);
        if (empty($res)) {
            return $res;
        }
        if (is_a($res, 'User')) {
            $this->performLogin($res);
        }
        if (is_a($res, 'KTAuthenticationSource')) {
            $_SESSION['autosignup'] = $aExtra;
            $this->redirectTo('autoSignup', array(
                'source_id' => $res->getId(),
                'username' => $username,
            ));
            exit(0);
        }
    }

    function do_autoSignup() {
        $oSource =& $this->oValidator->validateAuthenticationSource($_REQUEST['source_id']);
        $oProvider =& KTAuthenticationUtil::getAuthenticationProviderForSource($oSource);
        $oDispatcher = $oProvider->getSignupDispatcher($oSource);
        $oDispatcher->subDispatch($this);
        exit(0);
    }

    function do_checkCookie() {
        $cookieTest = KTUtil::arrayGet($_COOKIE, "CookieTestCookie", null);
        $cookieVerify = KTUtil::arrayGet($_REQUEST, 'cookieVerify', null);

        $url = $_SERVER["PHP_SELF"];
        $queryParams = array();
        $redirect = KTUtil::arrayGet($_REQUEST, 'redirect');

        if ($redirect !== null) {
            $queryParams[] = 'redirect='. urlencode($redirect);
        }

        if ($cookieTest !== $cookieVerify) {
            Session::destroy();
            $this->simpleRedirectToMain(_kt('You must have cookies enabled to use the document management system.'), $url, $queryParams);
            exit(0);
        }

        // check for a location to forward to
        if ($redirect !== null) {
            $url = $redirect;
        // else redirect to the dashboard if there is none
        } else {
            $url = generateControllerUrl("dashboard");
        }

        exit(redirect($url));
    }
}

//FIXME Direct Database Access
//checkLastSessionUserID finds the last user to logout or timeout
function checkLastSessionUserID()
{
	$sQuery = 'SELECT user_id FROM user_history ORDER BY id DESC LIMIT 1';
	$res = DBUtil::getOneResult($sQuery);
	return $res;
}

$dispatcher =& new LoginPageDispatcher();
$dispatcher->dispatch();

?>
