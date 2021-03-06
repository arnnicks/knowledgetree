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

require_once(KT_LIB_DIR . '/subscriptions/Subscription.inc');
require_once(KT_LIB_DIR . '/subscriptions/SubscriptionManager.inc');
require_once(KT_LIB_DIR . '/subscriptions/subscriptions.inc.php');
require_once(KT_LIB_DIR . '/plugins/pluginregistry.inc.php');
require_once(KT_LIB_DIR . '/plugins/plugin.inc.php');
require_once(KT_LIB_DIR . '/actions/documentaction.inc.php');
require_once(KT_LIB_DIR . '/actions/folderaction.inc.php');

class KTSubscriptionPlugin extends KTPlugin {
    var $sNamespace = 'ktstandard.subscriptions.plugin';
    var $autoRegister = true;

    function KTSubscriptionPlugin($sFilename = null) {
        $res = parent::KTPlugin($sFilename);
        $this->sFriendlyName = _kt('Subscription Plugin');
        return $res;
    }

    function setup() {
        $this->registerAction('documentsubscriptionaction', 'KTDocumentSubscriptionAction',
            'ktstandard.subscription.documentsubscription');
        $this->registerAction('documentsubscriptionaction', 'KTDocumentUnsubscriptionAction',
            'ktstandard.subscription.documentunsubscription');

        $this->registerTrigger('checkout', 'postValidate', 'KTCheckoutSubscriptionTrigger',
            'ktstandard.triggers.subscription.checkout');
        $this->registerTrigger('edit', 'postValidate', 'KTEditSubscriptionTrigger',
            'ktstandard.triggers.subscription.checkout');
        $this->registerTrigger('delete', 'postValidate', 'KTDeleteSubscriptionTrigger',
            'ktstandard.triggers.subscription.delete');
        $this->registerTrigger('moveDocument', 'postValidate', 'KTDocumentMoveSubscriptionTrigger',
            'ktstandard.triggers.subscription.moveDocument');
        $this->registerTrigger('archive', 'postValidate', 'KTArchiveSubscriptionTrigger',
            'ktstandard.triggers.subscription.archive');
        $this->registerTrigger('discussion', 'postValidate', 'KTDiscussionSubscriptionTrigger',
            'ktstandard.triggers.subscription.discussion');
        $this->registerAction('foldersubscriptionaction', 'KTFolderSubscriptionAction',
            'ktstandard.subscription.foldersubscription');
        $this->registerAction('foldersubscriptionaction', 'KTFolderUnsubscriptionAction',
            'ktstandard.subscription.folderunsubscription');
        $this->registerAction('folderaction', 'KTFolderSubscribeAction',
            'ktstandard.subscribe.foldersubscribeactions');
        $this->registerPage('manage', 'KTSubscriptionManagePage');
    }
}

$oRegistry =& KTPluginRegistry::getSingleton();
$oRegistry->registerPlugin('KTSubscriptionPlugin', 'ktstandard.subscriptions.plugin', __FILE__);

function wrapString($str, $length = 20) {
    // Wrap string to given character length (content rendered from ajax doesn't render correctly in ie)
    $len = mb_strlen($str);
    $out = '';
    $pos = 0;
    while ($len > $length && $pos !== false) {
        $pos = mb_strpos($str, ' ', $length);
        $line = mb_strcut($str, 0, $pos+1);
        $str = mb_strcut($str, $pos+1);
        $len = mb_strlen($str);
        $out .= $line.'<br>';
    }
    $out .= $str;
    return $out;
}

class KTDocumentSubscriptionAction extends KTDocumentAction {

    public $sName = 'ktstandard.subscription.documentsubscription';
    var $sParentBtn = 'more';

    public function getDisplayName()
    {
        return _kt('Subscribe to document');
    }

    public function getInfo()
    {
        $aInfo = parent::getInfo();
        if (Subscription::exists($this->oUser->getID(), $this->oDocument->getID(), SubscriptionEvent::subTypes('Document'))) {
            $aInfo['active'] = 'no';
        }
        return $aInfo;
    }

    public function do_ajax()
    {
        $str = null;
        $this->createSubscription($str);
        $str = wrapString($str);
        echo $str;
        exit(0);
    }

    public function do_main()
    {
        $str = null;
        if ($this->createSubscription($str)) {
            $_SESSION['KTInfoMessage'][] = $str;
        }
        else {
            $_SESSION['KTErrorMessage'][] = $str;
        }

        controllerRedirect('viewDocument', 'fDocumentId=' . $this->oDocument->getId());
        exit(0);
    }

    private function createSubscription(&$output)
    {
        $result = true;
        $output = null;

        $iSubscriptionType = SubscriptionEvent::subTypes('Document');
        if (Subscription::exists($this->oUser->getId(), $this->oDocument->getId(), $iSubscriptionType)) {
            $output = _kt('You are already subscribed to that document');
            $result = false;
        } else {
            $oSubscription = new Subscription($this->oUser->getId(), $this->oDocument->getId(), $iSubscriptionType);
            $res = $oSubscription->create();
            if ($res) {
                $output = _kt('You have been subscribed to this document');
                // create the document transaction record
                $documentTransaction = new DocumentTransaction($this->oDocument, 'User subscribed to document', 'ktcore.transactions.subscribe');
                $documentTransaction->create();
            } else {
                $output = _kt('There was a problem subscribing you to this document');
                $result = false;
            }
        }

        return $result;
    }

}

class KTDocumentUnsubscriptionAction extends KTDocumentAction {

    var $sName = 'ktstandard.subscription.documentunsubscription';

    public function getDisplayName()
    {
        return _kt('Unsubscribe from document');
    }

    public function getInfo()
    {
        $aInfo = parent::getInfo();
        if (!Subscription::exists($this->oUser->getID(), $this->oDocument->getID(), SubscriptionEvent::subTypes('Document'))) {
            $aInfo['active'] = 'no';
        }
        return $aInfo;
    }

    public function do_ajax()
    {
        $str = null;
        $this->deleteSubscription($str);
        $str = wrapString($str);
        echo $str;
        exit(0);
    }

    public function do_main()
    {
        $str = null;
        if ($this->deleteSubscription($str)) {
            $_SESSION['KTInfoMessage'][] = $str;
        }
        else {
            $_SESSION['KTErrorMessage'][] = $str;
        }

        controllerRedirect('viewDocument', 'fDocumentId=' . $this->oDocument->getId());
        exit(0);
    }

    private function deleteSubscription(&$output)
    {
        $result = true;
        $output = null;

        $iSubscriptionType = SubscriptionEvent::subTypes('Document');
        if (!Subscription::exists($this->oUser->getId(), $this->oDocument->getId(), $iSubscriptionType)) {
            $output = _kt('You are not subscribed to this document');
            $result = false;
        } else {
            $oSubscription = & Subscription::getByIDs($this->oUser->getId(), $this->oDocument->getId(), $iSubscriptionType);
            $res = $oSubscription->delete();
            if ($res) {
                $output = _kt('You have been unsubscribed from this document');
                // create the document transaction record
                $documentTransaction = new DocumentTransaction($this->oDocument, 'User unsubscribed from document', 'ktcore.transactions.usubscribe');
                $documentTransaction->create();
            } else {
                $output = _kt('There was a problem unsubscribing you from this document');
                $result = false;
            }
        }

        return $result;
    }

}

// {{{ KTCheckoutSubscriptionTrigger
class KTCheckoutSubscriptionTrigger {
    var $aInfo = null;
    function setInfo(&$aInfo) {
        $this->aInfo =& $aInfo;
    }

    function postValidate($bulk_action = false) {
        global $default;
        $oDocument =& $this->aInfo['document'];
        // fire subscription alerts for the checked out document

        if (!$bulk_action) {
            $oSubscriptionEvent = new SubscriptionEvent();
            $oFolder = Folder::get($oDocument->getFolderID());
            $oSubscriptionEvent->CheckoutDocument($oDocument, $oFolder);
        }
    }
}
// }}}


// {{{ KTCheckoutSubscriptionTrigger
class KTEditSubscriptionTrigger {
    var $aInfo = null;
    function setInfo(&$aInfo) {
        $this->aInfo =& $aInfo;
    }

    function postValidate() {
        global $default;
        $oDocument =& $this->aInfo['document'];
        // fire subscription alerts for the checked out document

        // fire subscription alerts for the checked in document
        $oSubscriptionEvent = new SubscriptionEvent();
        $oFolder = Folder::get($oDocument->getFolderID());
        $oSubscriptionEvent->ModifyDocument($oDocument, $oFolder);

    }
}
// }}}

// {{{ KTDeleteSubscriptionTrigger
class KTDeleteSubscriptionTrigger {
    var $aInfo = null;
    function setInfo(&$aInfo) {
        $this->aInfo =& $aInfo;
    }

    function postValidate($bulk_action = false) {
        global $default;
        $oDocument =& $this->aInfo['document'];

        // fire subscription alerts for the deleted document
        if (!$bulk_action) {
            // fire subscription alerts for the checked in document
            $oSubscriptionEvent = new SubscriptionEvent();
            $oFolder = Folder::get($oDocument->getFolderID());
            //$oSubscriptionEvent->RemoveDocument($oDocument, $oFolder);
        }
    }
}
// }}}

// {{{ KTDocumentMoveSubscriptionTrigger
class KTDocumentMoveSubscriptionTrigger {
    var $aInfo = null;
    function setInfo(&$aInfo) {
        $this->aInfo =& $aInfo;
    }

    function postValidate($bulk_action = false) {
        global $default;
        $oDocument =& $this->aInfo['document'];
        $oOldFolder =& $this->aInfo['old_folder'];
        $oNewFolder =& $this->aInfo['new_folder'];

        if (!$bulk_action) {
            // fire subscription alerts for the checked in document
            $oSubscriptionEvent = new SubscriptionEvent();
            $oSubscriptionEvent->MoveDocument($oDocument, $oNewFolder, $oNewFolder);
        }
    }
}
// }}}

// {{{ KTArchiveSubscriptionTrigger
class KTArchiveSubscriptionTrigger {
    var $aInfo = null;
    function setInfo(&$aInfo) {
        $this->aInfo =& $aInfo;
    }

    function postValidate($bulk_action = false) {
        global $default;
        $oDocument =& $this->aInfo['document'];
        if (!$bulk_action) {
            // fire subscription alerts for the checked in document
            $oSubscriptionEvent = new SubscriptionEvent();
            $oFolder = Folder::get($oDocument->getFolderID());
            $oSubscriptionEvent->ArchivedDocument($oDocument, $oFolder);
        }
    }
}
// }}}

class KTDiscussionSubscriptionTrigger {
    var $aInfo = null;
    function setInfo(&$aInfo) {
        $this->aInfo =& $aInfo;
    }

    function postValidate() {
        $oDocument =& $this->aInfo['document'];
        $oSubscriptionEvent = new SubscriptionEvent();
        $oFolder = Folder::get($oDocument->getFolderID());

        $oSubscriptionEvent->DiscussDocument($oDocument, $oFolder);
        $indexer = Indexer::get();
        $indexer->index($oDocument, 'D');
    }
}

class KTFolderSubscriptionAction extends KTFolderAction {
    public $sName = 'ktstandard.subscription.foldersubscription';
	public $cssClass = 'subscribe';
	
    public function getDisplayName()
    {
        return _kt('Subscribe to folder');
    }

    public function getInfo()
    {
        $aInfo = parent::getInfo();
        if (Subscription::exists($this->oUser->getID(), $this->oFolder->getID(), SubscriptionEvent::subTypes('Folder'))) {
            // KTFolderUnsubscriptionAction will display instead.
            $aInfo['active'] = 'no';
        }
        // return the url to the action for subfolders - display the Subscribe link, on clicking, display the include subfolders link.
        $aInfo['subaction'] = _kt('Subscribe to folder and <br>subfolders');
        return $aInfo;
    }

    public function do_ajax()
    {
        $this->subscribe();
    }

    public function do_add_subfolders()
    {
        $this->subscribe(true);
    }

    public function subscribe($incSubFolders = false)
    {
        $str = null;
        $this->createSubscription($str, $incSubFolders);
		$oKTFolderSubscribeAction = new KTFolderSubscribeAction($this->oFolder, $this->oUser);
		echo $oKTFolderSubscribeAction->render();
		exit(0);
    }

    public function do_main()
    {
        $str = null;
        if ($this->createSubscription($str)) {
            $_SESSION['KTInfoMessage'][] = $str;
        }
        else {
            $_SESSION['KTErrorMessage'][] = $str;
        }

        controllerRedirect('browse', 'fFolderId=' . $this->oFolder->getId());
        exit(0);
    }

    private function createSubscription(&$output, $incSubFolders = false)
    {
        $result = true;
        $output = null;

        $iSubscriptionType = SubscriptionEvent::subTypes('Folder');
        if (Subscription::exists($this->oUser->getId(), $this->oFolder->getId(), $iSubscriptionType)) {
            $output = _kt('You are already subscribed to this folder');
            $result = false;
        } else {
            $oSubscription = new Subscription($this->oUser->getId(), $this->oFolder->getId(), $iSubscriptionType);

            if ($incSubFolders) {
                $oSubscription->setWithSubFolders(true);
            }

            $res = $oSubscription->create();
            if ($res) {
               if ($incSubFolders) {
                    $output = _kt('You have been subscribed to this folder and its subfolders');
                } else {
                    $output = _kt('You have been subscribed to this folder');
                }

                // create the folder transaction
                $oTransaction = KTFolderTransaction::createFromArray(array(
                    'folderid' => $this->oFolder->getId(),
                    'comment' => 'User subscribed to folder',
                    'transactionNS' => 'ktcore.transactions.subscribe',
                    'userid' => $_SESSION['userID'],
                    'ip' => Session::getClientIP(),
                	'parentid' => $this->oFolder->getParentID(),
                ));

                if (PEAR::isError($oTransaction)) {
                    $default->log->debug('Folder subscription: transaction could not be logged. '.$oTransaction->getMessage());
                }
            } else {
                $output = _kt('There was a problem subscribing you to this folder');
                $result = false;
            }
        }

        return $result;
    }

}

class KTFolderUnsubscriptionAction extends KTFolderAction {
    public $sName = 'ktstandard.subscription.folderunsubscription';
	public $cssClass = 'unsubscribe';
	
    public function getDisplayName()
    {
        return _kt('Unsubscribe from folder');
    }

    public function getInfo()
    {
        $aInfo = parent::getInfo();
        if (!Subscription::exists($this->oUser->getID(), $this->oFolder->getID(), SubscriptionEvent::subTypes('Folder'))) {
            $aInfo['active'] = 'no';
        }
        return $aInfo;
    }

    public function do_ajax()
    {
        $str = null;
        $this->deleteSubscription($str);
		$oKTFolderSubscribeAction = new KTFolderSubscribeAction($this->oFolder, $this->oUser);
		echo $oKTFolderSubscribeAction->render();
		exit(0);
    }

    public function do_main()
    {
        $str = null;
        if ($this->deleteSubscription($str)) {
            $_SESSION['KTInfoMessage'][] = $str;
        }
        else {
            $_SESSION['KTErrorMessage'][] = $str;
        }

        controllerRedirect('browse', 'fFolderId=' . $this->oFolder->getId());
        exit(0);
    }

    private function deleteSubscription(&$output)
    {
        $result = true;
        $output = null;

        $iSubscriptionType = SubscriptionEvent::subTypes('Folder');
        if (!Subscription::exists($this->oUser->getId(), $this->oFolder->getId(), $iSubscriptionType)) {
            $output = _kt('You were not subscribed to that folder');
            $result = false;
        } else {
            $oSubscription =& Subscription::getByIDs($this->oUser->getId(), $this->oFolder->getId(), $iSubscriptionType);
            $res = $oSubscription->delete();
            if ($res) {
                $output = _kt('You have been unsubscribed from this folder');
                // create the folder transaction
                $oTransaction = KTFolderTransaction::createFromArray(array(
                    'folderid' => $this->oFolder->getId(),
                    'comment' => 'User unsubscribed from folder',
                    'transactionNS' => 'ktcore.transactions.unsubscribe',
                    'userid' => $_SESSION['userID'],
                    'ip' => Session::getClientIP(),
                	'parentid' => $this->oFolder->getParentID(),
                ));
            } else {
                $output = _kt('There was a problem unsubscribing you from this folder');
                $result = false;
            }
        }
    }

}

class KTFolderSubscribeAction extends KTFolderAction {
	public $sName = 'ktstandard.subscribe.foldersubscribeactions';
	public $cssClass = 'subscribe_actions';
	
    public function getDisplayName()
    {
        return _kt('Subscriptions');
    }
    
	public function customiseInfo($aInfo) {
		$aInfo['context'] = $this;
		$aInfo['url'] = '#';
		$aInfo['render'] = true;
		$aInfo['onclick'] = "javascript:{ showMenus('subscribe_actions'); }";
		
		return $aInfo;
	}
    
	public function do_refresh() {
		echo $this->render();
		exit(0);
	}
	
    public function render() {
        if ($this->oUser->isAnonymous() || $this->oUser->getDisabled() == 4) { return null; }
		if ($this->oFolder) {
            $oObject = $this->oFolder;
        } else {
            // not in a folder
            return null;
        }
        $oUser = $this->oUser;
        $this->actions = array();

        // Get the actions
        $oKTActionRegistry =& KTActionRegistry::getSingleton();
        $actions = $oKTActionRegistry->getActions('foldersubscriptionaction');

        foreach ($actions as $aAction) {
            list($sClassName, $sPath) = $aAction;
            $oSubscription = new $sClassName($this->oFolder, $oUser);
            $actionInfo = $oSubscription->getInfo();
            if (!empty($actionInfo)) {
                if (!isset($actionInfo['active']) && $actionInfo['active'] != 'no') {
					$aInfo = $actionInfo;
                }
            }
        }

        // Create js script
        $url = KTUtil::kt_url() . $aInfo['url'];
        $class = 'folder_subscribe';
        $subInfo = array();
        if (isset($aInfo['subaction'])) {
            $subInfo['js'] = "<a id='subLink' class='subfolder_subscribe' style='cursor:pointer' onclick='javascript: { subscriptions.doSubscribe(\"add_subfolders\", \"{$url}\", \"{$aInfo['class']}\"); }'>{$aInfo['subaction']}</a>";
        } else {
        	$class = 'folder_unsubscribe';
        }
        $script = "<a id='subscribeLink' class='$class' style='cursor:pointer' onclick='javascript: { subscriptions.doSubscribe(\"ajax\", \"{$url}\", \"{$aInfo['class']}\"); }'>{$aInfo['name']}</a>";
        $aInfo['js'] = $script;
        $manageInfo = array();
        $manageInfo['js'] = "<a class='manage_subscribe' style='cursor:pointer' onclick='javascript: { subscriptions.displayAction(); }'>". _kt('Manage subscriptions') ."</a>";
        
        $this->actions[] = $aInfo;
		$this->actions[] = $subInfo;
		$this->actions[] = $manageInfo;

        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate('kt3/portlets/actions_portlet');
        $aTemplateData = array(
            'context' => $this,
        );

        return $oTemplate->render($aTemplateData);
    }
}

class KTSubscriptionManagePage extends KTStandardDispatcher {

    function do_main()
    {
        $this->aBreadcrumbs[] = array('name' => _kt('Subscription Management'));
        $aFolderSubscriptions = SubscriptionManager::retrieveUserSubscriptions(
            $this->oUser->getId(), SubscriptionEvent::subTypes('Folder'));
        $aDocumentSubscriptions = SubscriptionManager::retrieveUserSubscriptions(
            $this->oUser->getId(), SubscriptionEvent::subTypes('Document'));
        $bNoSubscriptions  = ((count($aFolderSubscriptions) == 0) && (count($aDocumentSubscriptions) == 0)) ? true : false;

        $oTemplate = $this->oValidator->validateTemplate('ktstandard/subscriptions/manage');

        $aTemplateData = array(
            'aFolderSubscriptions' => $aFolderSubscriptions,
            'aDocumentSubscriptions' => $aDocumentSubscriptions,
        );

        return $oTemplate->render($aTemplateData);
    }

    function do_ajax()
    {
        $aFolderSubscriptions = SubscriptionManager::retrieveUserSubscriptions($this->oUser->getId(), SubscriptionEvent::subTypes('Folder'));
        $aDocumentSubscriptions = SubscriptionManager::retrieveUserSubscriptions($this->oUser->getId(), SubscriptionEvent::subTypes('Document'));
        $bNoSubscriptions  = ((count($aFolderSubscriptions) == 0) && (count($aDocumentSubscriptions) == 0)) ? true : false;
        $oTemplate = $this->oValidator->validateTemplate('ktstandard/subscriptions/management');
        $aTemplateData = array(
            'aFolderSubscriptions' => $aFolderSubscriptions,
            'aDocumentSubscriptions' => $aDocumentSubscriptions,
        );
		
        echo $oTemplate->render($aTemplateData);
        exit(0);
    }
    
    function do_removeSubscriptions()
    {
        $foldersubscriptions = KTUtil::arrayGet($_REQUEST, 'foldersubscriptions');
        $documentsubscriptions = KTUtil::arrayGet($_REQUEST, 'documentsubscriptions');
        if (empty($foldersubscriptions) && empty($documentsubscriptions)) {
            $this->errorRedirectToMain(_kt('No subscriptions were chosen'));
        }

        $iSuccesses = 0;
        $iFailures = 0;

        if (!empty($foldersubscriptions)) {
            foreach ($foldersubscriptions as $iSubscriptionId) {
                $oSubscription = Subscription::get($iSubscriptionId, SubscriptionEvent::subTypes('Folder'));
                if ($oSubscription) {
                    $result = DBUtil::getOneResult("SELECT folder_id FROM folder_subscriptions WHERE id = $iSubscriptionId");
                    if ($oSubscription->delete()) {
                        // create the folder transaction
                        $oTransaction = KTFolderTransaction::createFromArray(array(
                            'folderid' => $result['folder_id'],
                            'comment' => 'User unsubscribed from folder',
                            'transactionNS' => 'ktcore.transactions.unsubscribe',
                            'userid' => $_SESSION['userID'],
                            'ip' => Session::getClientIP(),
                        	'parentid' => $result['folder_id'],	//TODO: need to get parent ID!
                        ));
                        ++$iSuccesses;
                    }
                    else {
                        ++$iFailures;
                    }
                } else {
                    ++$iFailures;
                }
            }
        }

        if (!empty($documentsubscriptions)) {
            foreach ($documentsubscriptions as $iSubscriptionId) {
                $oSubscription = Subscription::get($iSubscriptionId, SubscriptionEvent::subTypes('Document'));
                if ($oSubscription) {
                    $result = DBUtil::getOneResult("SELECT document_id FROM document_subscriptions WHERE id = $iSubscriptionId");
                    if ($oSubscription->delete()) {
                        // get document object
                        $document = Document::get($result['document_id']);
                        // create the document transaction record
                        $documentTransaction = new DocumentTransaction($document, 'User unsubscribed from document', 'ktcore.transactions.usubscribe');
                        $documentTransaction->create();
                        ++$iSuccesses;
                    }
                    else {
                        ++$iFailures;
                    }
                } else {
                    ++$iFailures;
                }
            }
        }

        $sMessage = _kt('Subscriptions removed') . ': ';
        if ($iFailures) {
            $sMessage .= sprintf(_kt('%d successful, %d failures'), $iSuccesses, $iFailures);
        } else {
            $sMessage .= sprintf('%d', $iSuccesses);
        }

        echo $this->do_ajax();
    }

}
