<?php

require_once(KT_LIB_DIR . '/unitmanagement/Unit.inc');

require_once(KT_LIB_DIR . "/templating/templating.inc.php");
require_once(KT_LIB_DIR . "/dispatcher.inc.php");
require_once(KT_LIB_DIR . "/templating/kt3template.inc.php");
require_once(KT_LIB_DIR . "/widgets/fieldWidgets.php");

require_once(KT_LIB_DIR . "/browse/DocumentCollection.inc.php");
require_once(KT_LIB_DIR . "/browse/BrowseColumns.inc.php");
require_once(KT_LIB_DIR . "/browse/PartialQuery.inc.php");

require_once(KT_LIB_DIR . "/foldermanagement/folderutil.inc.php");
require_once(KT_LIB_DIR . "/permissions/permissionutil.inc.php");

class KTUnitAdminDispatcher extends KTAdminDispatcher {
    var $bAutomaticTransaction = true;

    function check() {
        $this->aBreadcrumbs[] = array('url' => $_SERVER['PHP_SELF'], 'name' => _('Unit Management'));
        return parent::check();
    }

    function do_main() {
		$this->oPage->setBreadcrumbDetails(_('select a unit'));
		$this->oPage->setTitle(_("Unit Management"));

		$unit_list =& Unit::getList();
		
		$oTemplating =& KTTemplating::getSingleton();        
		$oTemplate = $oTemplating->loadTemplate("ktcore/principals/unitadmin");
		$aTemplateData = array(
			"context" => $this,
			"unit_list" => $unit_list,
		);
 		return $oTemplate->render($aTemplateData);
    }

    function do_addUnit() {
        $this->oPage->setBreadcrumbDetails(_('Add a new unit'));
        $this->oPage->setTitle(_("Add a new unit"));

        $add_fields = array();
        $add_fields[] =  new KTStringWidget(_('Unit Name'),_('A short name for the unit.  e.g. <strong>Accounting</strong>.'), 'unit_name', null, $this->oPage, true);

        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("ktcore/principals/addunit");
        $aTemplateData = array(
            "context" => $this,
            "add_fields" => $add_fields,
        );
        return $oTemplate->render($aTemplateData);
    }

    function do_addUnit2() {
        $this->oPage->setBreadcrumbDetails(_('Add a new unit'));
        $this->oPage->setTitle(_("Add a new unit"));

        $aOptions = array(
            'redirect_to' => array('addUnit'),
            'message' => 'No name given',
        );
        $sName = $this->oValidator->validateString($_REQUEST['unit_name'], $aOptions);

        $iFolderId = KTUtil::arrayGet($_REQUEST, 'fFolderId', 1);
        $_REQUEST['fFolderId'] = $iFolderId;
        $oFolder = $this->oValidator->validateFolder($_REQUEST['fFolderId']);

        $collection = new DocumentCollection();
        $collection->addColumn(new KTUnitTitleColumn($sName));
        $qObj = new FolderBrowseQuery($oFolder->getId());
        $collection->setQueryObject($qObj);
        $batchPage = (int) KTUtil::arrayGet($_REQUEST, "page", 0);
        $batchSize = 20;

        $resultURL = KTUtil::addQueryString($_SERVER['PHP_SELF'], sprintf("action=addUnit2&unit_name=%s&fFolderId=%d", $sName, $oFolder->getId()));
        $collection->setBatching($resultURL, $batchPage, $batchSize);

        // ordering. (direction and column)
        $displayOrder = KTUtil::arrayGet($_REQUEST, 'sort_order', "asc");
        if ($displayOrder !== "asc") { $displayOrder = "desc"; }
        $displayControl = KTUtil::arrayGet($_REQUEST, 'sort_on', "title");

        $collection->setSorting($displayControl, $displayOrder);

        $collection->getResults();

        $aBreadcrumbs = array();
        $folder_path_names = $oFolder->getPathArray();
        $folder_path_ids = explode(',', $oFolder->getParentFolderIds());
        $folder_path_ids[] = $oFolder->getId();
        if ($folder_path_ids[0] == 0) {
            array_shift($folder_path_ids);
            array_shift($folder_path_names);
        }

        foreach (range(0, count($folder_path_ids) - 1) as $index) {
            $id = $folder_path_ids[$index];
            $url = KTUtil::addQueryString($_SERVER['PHP_SELF'], sprintf("action=addUnit2&unit_name=%s&fFolderId=%d", $sName, $id));
            $aBreadcrumbs[] = array("url" => $url, "name" => $folder_path_names[$index]);
        }

        $add_fields = array();
        $add_fields[] =  new KTStaticTextWidget(_('Unit Name'),_('A short name for the unit.  e.g. <strong>Accounting</strong>.'), 'unit_name', $sName, $this->oPage, true);

        $oTemplating =& KTTemplating::getSingleton();
        $oTemplate = $oTemplating->loadTemplate("ktcore/principals/addunit2");
        $aTemplateData = array(
            "context" => $this,
            "add_fields" => $add_fields,
            "collection" => $collection,
            "collection_breadcrumbs" => $aBreadcrumbs,
            "folder" => $oFolder,
            "name" => $sName,
        );
        return $oTemplate->render($aTemplateData);
    }

    function do_createUnit() {
        $aOptions = array(
            'redirect_to' => array('main'),
            'message' => 'Invalid folder chosen',
        );
        $oParentFolder = $this->oValidator->validateFolder($_REQUEST['fFolderId'], $aOptions);
        $aOptions = array(
            'redirect_to' => array('addUnit', sprintf('fFolderId=%d', $oParentFolder->getId())),
            'message' => 'No name given',
        );
        $sName = $this->oValidator->validateString($_REQUEST['unit_name'], $aOptions);

        $oFolder = KTFolderUtil::add($oParentFolder, $sName, $this->oUser);
        $aOptions = array(
            'redirect_to' => array('addUnit', sprintf('fFolderId=%d', $oParentFolder->getId())),
            'defaultmessage' => 'Error creating folder',
        );
        $this->oValidator->notError($oFolder, $aOptions);

        KTPermissionUtil::copyPermissionObject($oFolder);
        
        $oUnit = Unit::createFromArray(array(
            'name' => $sName,
            'folderid' => $oFolder->getId(),
        ));
        return $this->successRedirectToMain('Unit created');
    }

    function do_editUnit() {
        $oUnit =& $this->oValidator->validateUnit($_REQUEST['unit_id']); 

        $fields = array();
        $fields[] =  new KTStringWidget(_('Unit Name'),_('A short name for the unit.  e.g. <strong>Accounting</strong>.'), 'unit_name', $oUnit->getName(), $this->oPage, true);

        $oTemplate =& $this->oValidator->validateTemplate('ktcore/principals/editunit');
        $aTemplateData = array(
            "context" => $this,
            "edit_unit" => $oUnit,
            "edit_fields" => $fields,
        );
        return $oTemplate->render($aTemplateData);
    }

    function do_saveUnit() {
        $oUnit =& $this->oValidator->validateUnit($_REQUEST['unit_id']); 
        $sName =& $this->oValidator->validateString($_REQUEST['unit_name']);
        $oUnit->setName($sName);
        $res = $oUnit->update();
        if (($res == false) || (PEAR::isError($res))) {
            return $this->errorRedirectToMain(_('Failed to set unit details.'));
        }
        $iFolderId = $oUnit->getFolderId();
        $oFolder = Folder::get($iFolderId);
        if (!PEAR::isError($oFolder) && ($oFolder !== false)) {
            KTFolderUtil::rename($oFolder, $sName, $this->oUser);
        }

        $this->successRedirectToMain(_("Unit details updated"));
    }

    function do_deleteUnit() {
        $oUnit =& $this->oValidator->validateUnit($_REQUEST['unit_id']); 

        $fields = array();
        $fields[] = new KTCheckboxWidget(_('Delete folder'), _('Each unit has an associated folder.  While the unit is being deleted, there may be some documents within the associated folder.  By unselecting this option, they will not be removed.'), 'delete_folder', true, $this->oPage, true);

        $oTemplate =& $this->oValidator->validateTemplate('ktcore/principals/deleteunit');
        $aTemplateData = array(
            "context" => $this,
            "unit" => $oUnit,
            "fields" => $fields,
        );
        return $oTemplate->render($aTemplateData);
    }

    function do_removeUnit() {
        $oUnit =& $this->oValidator->validateUnit($_REQUEST['unit_id']); 
        $bDeleteFolder = KTUtil::arrayGet($_REQUEST, 'delete_folder', false);
        if ($bDeleteFolder) {
            $iFolderId = $oUnit->getFolderId();
            $oFolder = Folder::get($iFolderId);
            if (!PEAR::isError($oFolder) && ($oFolder !== false)) {
                $aOptions = array(
                    'ignore_permissions' => true,
                );
                KTFolderUtil::delete($oFolder, $this->oUser, "Unit deleted", $aOptions);
            }
        }
        $oUnit->delete();
        $this->successRedirectToMain("Unit removed");
    }
}

class KTUnitTitleColumn extends TitleColumn {
    function KTUnitTitleColumn($sName) {
        $this->sName = $sName;
        parent::TitleColumn("Unit", "title");
    }
    function buildFolderLink($aDataRow) {
        return KTUtil::addQueryString($_SERVER['PHP_SELF'], 
            sprintf('action=addUnit2&unit_name=%s&fFolderId=%d', $this->sName, $aDataRow['folder']->getId()));
    }
}


?>
