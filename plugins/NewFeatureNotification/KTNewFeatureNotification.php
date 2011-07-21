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
require_once(KT_LIB_DIR . '/plugins/plugin.inc.php');
require_once(KT_LIB_DIR . '/plugins/pluginregistry.inc.php');
require_once(KT_LIB_DIR . '/templating/templating.inc.php');

class KTNewFeatureNotificationPlugin extends KTPlugin {
	public $sNamespace = 'new.feature.notification.plugin';
	public $iVersion = 0;
	public $autoRegister = true;
	public $showInAdmin = false;

	public function __construct($sFilename = null)
	{
		$res = parent::KTPlugin($sFilename);
		$this->sFriendlyName = _kt('New Features Notfications');

		return $res;
	}

	public function setup() {
		$templating = KTTemplating::getSingleton();
		$dir = dirname(__FILE__) . DIRECTORY_SEPARATOR;
		$dir = str_replace(KT_DIR, '', $dir);
		$templating->addLocation('newfeature', "$dir/templates", 'new.feature.notification.plugin');
	}
}

$oPluginRegistry = KTPluginRegistry::getSingleton();
$oPluginRegistry->registerPlugin('KTNewFeatureNotificationPlugin', 'new.feature.notification.plugin', __FILE__);

?>