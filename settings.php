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

require_once('config/dmsDefaults.php');
require_once(KT_LIB_DIR . '/templating/kt3template.inc.php');
require_once(KT_LIB_DIR . '/dispatcher.inc.php');
require_once(KT_LIB_DIR . '/widgets/portlet.inc.php');

require_once(KT_LIB_DIR . '/plugins/KTAdminNavigation.php');

class AdminSplashDispatcher extends KTAdminDispatcher {

    var $category = '';
    var $sSection = 'settings';

    function AdminSplashDispatcher()
    {
        $this->aBreadcrumbs = array(
            array('url' => KTUtil::getRequestScriptName($_SERVER), 'name' => _kt('Settings')),
        );

        parent::KTAdminDispatcher();
    }

    public function do_main()
    {
        $registry = KTAdminNavigationRegistry::getSingleton();
        $categories = $registry->getCategories();
        $KTConfig = KTConfig::getSingleton();
        $condensedAdmin = $KTConfig->get('condensedAdminUI');

	// TODO Figure whether this is still relevant and remove if not.
        // We need to investigate sub_url solutions.
        $allItems = array();
        if ($condensedAdmin) {
            foreach ($categories as $category) {
                $items = $registry->getItemsForCategory($category['name']);
                $allItems[$category['name']] = $items;
            }
        }

        global $default;
        if (ACCOUNT_ROUTING_ENABLED && $default->tier == 'trial') {
            $this->includeOlark();
        }

	$templating = KTTemplating::getSingleton();
        $template = $templating->loadTemplate('kt3/settings');
        $templateData = array(
              'context' => $this,
              'categories' => $categories,
              'all_items' => $allItems,
	      'items' => $this->getCategoryItems(),
              'baseurl' => $_SERVER['PHP_SELF'],
        );

        return $template->render($templateData);
    }

    // TODO Default category display on first entry.
    private function getCategoryItems()
    {
        $page = $GLOBALS['main'];

        $category = KTUtil::arrayGet($_REQUEST, 'fCategory', $this->category);

        if ($category == 'contentSetup') {
            $jscript .= "<script src='resources/js/kt_hideadminlink.js' type='text/javascript'></script>";
        }

        $javascript[] = 'resources/js/newui/hide_system_links.js';
        $page->requireJSResources($javascript);

        $registry = KTAdminNavigationRegistry::getSingleton();
        if (ACCOUNT_ROUTING_ENABLED && $category == 'contentIndexing') {
            $items = null;
            $message = 'Indexing of full-text content in KnowledgeTree is carried out through shared queue processes using SOLR. <br/>Content Indexing statistics coming soon!';
        }
        else {
            $items = $registry->getItemsForCategory($category);
            $message = null;
        }

        if (count($items) == 1) {
            // skip the list of admin pages and go direct to the first / only page
            $url = KTUtil::ktLink('admin.php', $items[0]['fullname']);
            redirect($url);
        }

        $this->aBreadcrumbs[] = array('name' => $category['title'], 'url' => KTUtil::ktLink('settings.php', $category));

        $this->oPage->title = _kt('Settings') . ': ' . $category['title'];

	return $items;
    }

    // This function is now just an alias for do_main...
    function do_viewCategory()
    {
	return $this->do_main();
    }

    private function includeOlark()
    {
        $user = User::get($_SESSION['userID']);
        $js = preg_replace('/.*[\/\\\\]plugins/', 'plugins', KT_LIVE_DIR) . '/resources/js/olark/olark.js';
        $this->oPage->requireJsResource($js);
        $this->oPage->setBodyOnload("javascript: ktOlark.setUserData('" . $user->getName() . "', '" . $user->getEmail() . "');");
    }

}

$sub_url = KTUtil::arrayGet($_SERVER, 'PATH_INFO');
$sub_url = trim($sub_url);
$sub_url= trim($sub_url, '/');

if (empty($sub_url)) {
    $oDispatcher = new AdminSplashDispatcher();
} else {
    $oRegistry =& KTAdminNavigationRegistry::getSingleton();
    if ($oRegistry->isRegistered($sub_url)) {
       $oDispatcher = $oRegistry->getDispatcher($sub_url);

       $aParts = explode('/',$sub_url);

       $oRegistry =& KTAdminNavigationRegistry::getSingleton();
       $aCategory = $oRegistry->getCategory($aParts[0]);

       $oDispatcher->aBreadcrumbs = array();
       $oDispatcher->aBreadcrumbs[] = array('action' => 'settings', 'name' => _kt('Settings'));
       $oDispatcher->aBreadcrumbs[] = array('name' => $aCategory['title'], 'url' => KTUtil::ktLink('admin.php',$aParts[0]));
    } else {
       // FIXME (minor) redirect to no-suburl?
       $oDispatcher = new AdminSplashDispatcher();
       $oDispatcher->category = $sub_url;
    }
}

// Implement an electronic signature for accessing the admin section, it will appear every 10 minutes
global $main;
global $default;
if ($default->enableAdminSignatures && ($_SESSION['electronic_signature_time'] < time())) {
    $sBaseUrl = KTUtil::kt_url();
    $sUrl = KTPluginUtil::getPluginPath('electronic.signatures.plugin', true);
    $heading = _kt('You are attempting to access Settings');
    $main->setBodyOnload("javascript: showSignatureForm('{$sUrl}', '{$heading}', 'dms.administration.administration_section_access', 'admin', '{$sBaseUrl}/browse.php', 'close');");
}

$oDispatcher->dispatch(); // we _may_ be redirected at this point (see KTAdminNavigation)
?>