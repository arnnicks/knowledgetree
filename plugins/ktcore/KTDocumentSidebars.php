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

require_once(KT_LIB_DIR . "/actions/documentviewlet.inc.php");
require_once(KT_LIB_DIR . "/workflow/workflowutil.inc.php");
require_once(KT_LIB_DIR . '/actions/documentaction.inc.php');
require_once(KT_PLUGIN_DIR . '/commercial/alerts/alertUtil.inc.php');

class KTDocumentSidebar extends KTDocumentViewlet {
    public $sName = 'ktcore.sidebars.document';
	public $_sShowPermission = 'ktcore.permissions.read';
	public $order = 1;
	
	public function getCSSName() {}
	public function getOrder() {
		return $this->order;
	}
	/**
	 * Create a sidebar block
	 *
	 * @return string
	 */
	public function getDocSideBars() {
		$sidebars = KTDocumentActionUtil::getDocumentActionsForDocument($this->oDocument, $this->oUser, 'documentsidebar');
		$ordered = array();
        foreach ($sidebars as $sidebar) {
        	if(isset($ordered[$sidebar->getOrder()])) {
        		$ordered[$sidebar->getOrder() + 1] = $sidebar;
        	} else {
        		$ordered[$sidebar->getOrder()] = $sidebar;
        	}
        }
		$oTemplating = KTTemplating::getSingleton();
		$oTemplate = $oTemplating->loadTemplate('ktcore/document/sidebars/viewSidebar');
        $aTemplateData = array(
              'context' => $this,
              'sidebars' => $ordered,
              'documentId' => $this->oDocument->getId(),
        );
        
        return $oTemplate->render($aTemplateData);
	}
	
}

?>