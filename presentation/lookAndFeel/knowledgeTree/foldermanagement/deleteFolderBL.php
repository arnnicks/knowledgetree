<?php
/**
 * Business logic concerned with the deletion of a folder.
 * Will use deleteFolderUI.inc for presentation functionality.
 *
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * @version $Revision$
 * @author Michael Joseph <michael@jamwarehouse.com>, Jam Warehouse (Pty) Ltd, South Africa
 * @package presentation.lookAndFeel.knowledgeTree.foldermanagement
 */

require_once("../../../../config/dmsDefaults.php");
require_once("$default->fileSystemRoot/lib/foldermanagement/Folder.inc");
require_once("$default->fileSystemRoot/lib/foldermanagement/FolderCollaboration.inc");
require_once("$default->fileSystemRoot/lib/foldermanagement/FolderDocTypeLink.inc");
require_once("$default->fileSystemRoot/lib/foldermanagement/PhysicalFolderManagement.inc");
require_once("$default->fileSystemRoot/lib/users/User.inc");
require_once("$default->fileSystemRoot/lib/subscriptions/SubscriptionManager.inc");
require_once("$default->fileSystemRoot/lib/subscriptions/SubscriptionEngine.inc");
require_once("$default->fileSystemRoot/lib/visualpatterns/PatternCustom.inc");
require_once("$default->fileSystemRoot/presentation/Html.inc");
require_once("$default->fileSystemRoot/presentation/lookAndFeel/knowledgeTree/foldermanagement/folderUI.inc");

require_once("deleteFolderUI.inc");

if (checkSession()) {
    // initialise custom pattern once
    $oPatternCustom = & new PatternCustom();
    
	if (isset($fFolderID)) {
		if (Permission::userHasFolderWritePermission($fFolderID)) {
			if (isset($fDeleteConfirmed)) {
				// deletion of folder is confirmed
				$oFolder = Folder::get($fFolderID);
				if (isset($oFolder)) {
                    // check if there are any documents or folders in this folder
                    
					$sFolderPath = Folder::getFolderPath($fFolderID);
					if ($oFolder->delete()) {
						if (PhysicalFolderManagement::deleteFolder($sFolderPath)) {
							// successfully deleted the folder from the file system
                            
                            // delete folder collaboration entries
                            $aFolderCollaboration = FolderCollaboration::getList("WHERE folder_id=$fFolderID");
                            for ($i=0; $i<count($aFolderCollaboration); $i++) {
                                $aFolderCollaboration[$i]->delete();
                            }
                            
                            // delete folder document types link
                            $aFolderDocTypeLink = FolderDocTypeLink::getList("folder_id=$fFolderID");
                            for ($i=0; $i<count($aFolderDocTypeLink); $i++) {
                                $aFolderDocTypeLink[$i]->delete();
                            }                            
                            
                            // fire subscription alerts for parent folder subscriptions to the deleted folder
                            $count = SubscriptionEngine::fireSubscription($oFolder->getParentID(), SubscriptionConstants::subscriptionAlertType("RemoveChildFolder"),
                                     SubscriptionConstants::subscriptionType("FolderSubscription"),
                                     array( "removedFolderName" => $oFolder->getName(),
                                            "parentFolderName" => Folder::getFolderDisplayPath($oFolder->getParentID())));
                            $default->log->info("deleteFolderBL.php fired $count parent folder subscription alerts for removed folder " . $oFolder->getName());

                            // fire subscription alerts for the deleted folder
                            $count = SubscriptionEngine::fireSubscription($fFolderID, SubscriptionConstants::subscriptionAlertType("RemoveSubscribedFolder"),
                                     SubscriptionConstants::subscriptionType("FolderSubscription"),
                                     array( "removedFolderName" => $oFolder->getName(),
                                            "parentFolderName" => Folder::getFolderDisplayPath($oFolder->getParentID())));
                            $default->log->info("deleteFolderBL.php fired $count parent folder subscription alerts for removed folder " . $oFolder->getName());
                            
                            // remove folder subscriptions for this folder
                            if (SubscriptionManager::removeSubscriptions($fFolderID, SubscriptionConstants::subscriptionType("FolderSubscription"))) {
                                $default->log->info("deleteFolderBL.php removed all subscriptions for this folder");
                            } else {
                                $default->log->error("deleteFolderBL.php couldn't remove folder subscriptions");
                            }                            
                            
							// redirect to the browse folder page with the parent folder id 
							redirect("$default->rootUrl/control.php?action=browse&fFolderID=" . $oFolder->getParentID());
						} else {
							// could not delete the folder from the file system, so reverse the folder deletion
							$oFolder->create();
							require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");																	
							$oPatternCustom->setHtml("");
							$main->setCentralPayload($oPatternCustom);
							$main->setErrorMessage("The folder could not be deleted from the file system");
							$main->render();
						}
					} else {
						// could not delete the folder in the db
						require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");			
						$oPatternCustom->setHtml("");
						$main->setCentralPayload($oPatternCustom);
						$main->setErrorMessage("The folder could not be deleted from the database");
						$main->render();
					}
				} else {
					// could not load folder object
					require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");									
					$oPatternCustom->setHtml("");
					$main->setCentralPayload($oPatternCustom);
					$main->setErrorMessage("An error occured whilst retrieving the folder from the database");
					$main->render();
				}
			} else {
                // check if there are any folders or documents in this folder
                                    
                // get folders descended from this one
                $aFolderArray = Folder::getList("parent_id=$fFolderID");
                // get documents in this folder
                $aDocumentArray = Document::getList("folder_id=$fFolderID");
                
                if (count($aFolderArray) > 0) {
                    $oPatternCustom->setHtml(getFolderNotEmptyPage($fFolderID,  count($aFolderArray), "folder(s)"));
                } else if (count($aDocumentArray) > 0) {
                    $oPatternCustom->setHtml(getFolderNotEmptyPage($fFolderID, count($aDocumentArray), "document(s)"));                                                                  
                } else {                
                    // get confirmation first
                    $oFolder = Folder::get($fFolderID);
                    $oPatternCustom->setHtml(getConfirmPage($fFolderID, $oFolder->getName()));
                }
                // render the page
                require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");
                $main->setCentralPayload($oPatternCustom);				
                $main->render();
			}
		} else {
			// user does not have permission to delete the folder
			require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");			
			$oPatternCustom = & new PatternCustom();							
			$oPatternCustom->setHtml("");
			$main->setCentralPayload($oPatternCustom);
			$main->setErrorMessage("You do not have permission to delete this folder");
			$main->render();
		}
	} else {
		// no folder selected for deletion
		require_once("$default->fileSystemRoot/presentation/webpageTemplate.inc");			
		$oPatternCustom = & new PatternCustom();							
		$oPatternCustom->setHtml("");
		$main->setCentralPayload($oPatternCustom);
		$main->setErrorMessage("No folder currently selected");
		$main->render();
	}
}
