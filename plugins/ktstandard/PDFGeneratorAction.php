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

require_once(KT_LIB_DIR . '/actions/folderaction.inc.php');
require_once(KT_LIB_DIR . '/permissions/permission.inc.php');
require_once(KT_LIB_DIR . '/permissions/permissionutil.inc.php');
require_once(KT_LIB_DIR . '/browse/browseutil.inc.php');

require_once(KT_LIB_DIR . '/plugins/plugin.inc.php');
require_once(KT_LIB_DIR . '/plugins/pluginregistry.inc.php');

require_once(KT_LIB_DIR . '/roles/Role.inc');

require_once(KT_DIR . '/plugins/pdfConverter/pdfConverter.php');

class PDFGeneratorAction extends KTDocumentAction {
    var $sName = 'ktstandard.pdf.generate';
    var $_sShowPermission = "ktcore.permissions.read";
    var $sDisplayName = 'Download PDF';
    // Note: 'asc' below seems to be a catchall for plain text docs.
    //       'htm' and 'html' should work but are not so have been removed for now.
    var $aAcceptedMimeTypes = array('doc', 'ods', 'odt', 'ott', 'txt', 'rtf', 'sxw', 'stw',
            //                                    'html', 'htm',
            'xml' , 'pdb', 'psw', 'ods', 'ots', 'sxc',
            'stc', 'dif', 'dbf', 'xls', 'xlt', 'slk', 'csv', 'pxl',
            'odp', 'otp', 'sxi', 'sti', 'ppt', 'pot', 'sxd', 'odg',
            'otg', 'std', 'asc');

	var $bShowIfReadShared = true;
	var $bShowIfWriteShared = true;

	var $sIconClass = 'download-pdf';
	var $sParentBtn = 'ktcore.actions.document.view';

    function getDisplayName()
    {
        global $default;

        // The generation of the pdf is done through the PDF Converter plugin.
        // The PDF's are generated in the background by the document processor

        // Build the display name and url
        $sDisplayName = _kt('Download PDF');

        if(!empty($this->oDocument))
        {
            // Check the document has the correct mimetype for pdf conversion
            $converter = new pdfConverter();
            $mimeTypes = $converter->getSupportedMimeTypes();
            $docType = $this->getMimeExtension();

            if(!in_array($docType, $mimeTypes) && $mimeTypes !== true)
            {
                return '';
            }

            // Check if the pdf exists
            if($this->pdfExists($this->oDocument)) {
                return $sDisplayName;
            }

        }else{
            // If the document is empty then we are probably in the workflow admin - action restrictions section, so we can display the name.
            return $sDisplayName;
        }

        return '';
    }

    function getUrl()
    {
        $sHostPath = KTUtil::kt_url();
        $link = KTUtil::ktLink('action.php', 'ktstandard.pdf.generate', array( 'fDocumentId' => $this->oDocument->getId(), 'action' => 'pdfdownload'));
        return $link;
    }

    function pdfExists($oDocument)
    {
        global $default;

        // Check if the document has a pdf rendition -> has_rendition = 1, 3, 5, 7
        // 0 = nothing, 1 = pdf, 2 = thumbnail, 4 = flash
        // 1+2 = 3: pdf & thumbnail; 1+4 = 5: pdf & flash; 2+4 = 6: thumbnail & flash; 1+2+4 = 7: all

        // If the flag hasn't been set, check against storage and update the flag - for documents where the flag hasn't been set
        $check = false;
        $hasRendition = $oDocument->getHasRendition();
        if (is_null($hasRendition)) {
            $iDocId = $this->oDocument->iId;
            $dir = $default->pdfDirectory;
            $file = $dir . '/' . $iDocId . '.pdf';

            $oStorage = KTStorageManagerUtil::getSingleton();
            if ($oStorage->file_exists($file)) {
                $oDocument->setHasRendition(1);
                $check = true;
            }

            $oDocument->update();
        }

        if ($check || in_array($hasRendition, array(1,3,5,7))) {
            return true;
        }
        return false;
    }

    function form_main() {
        $oForm = new KTForm;
        $oForm->setOptions(array(
                    'label' => _kt('Convert Document to PDF'),
                    'action' => 'selectType',
                    'fail_action' => 'main',
                    'cancel_url' => KTBrowseUtil::getUrlForDocument($this->oDocument),
                    'submit_label' => _kt('Convert Document'),
                    'context' => &$this,
                    ));

        $oForm->setWidgets(array(
                    array('ktcore.widgets.selection', array(
                            'label' => _kt("Type of conversion"),
                            'description' => _kt('The following are the types of conversions you can perform on this document.'),
                            //'important_description' => _kt('QA NOTE: Permissions checks are required here...'),
                            'name' => 'convert_type',
                            //'vocab' => array('Download as PDF', 'Duplicate as PDF', 'Replace as PDF'),
                            'vocab' => array('Download as PDF'),
                            'simple_select' => true,
                            'required' => true,
                            )),
                    ));

        return $oForm;
    }

    function do_selectType() {

        switch($_REQUEST[data][convert_type]){
            case '0':
                $this->do_pdfdownload();
                break;
            case '1':
                $this->do_pdfduplicate();
                break;
            case '2':
                $this->do_pdfreplace();
                break;
            default:
                $this->do_pdfdownload();
        }
        redirect(KTUtil::ktLink( 'action.php', 'ktstandard.pdf.generate', array( "fDocumentId" => $this->oDocument->getId() ) ) );
        exit(0);
    }

    function do_main() {
        $this->oPage->setBreadcrumbDetails(_kt('Generate PDF'));
        $oTemplate =& $this->oValidator->validateTemplate('ktstandard/PDFPlugin/PDFPlugin');

        $oForm = $this->form_main();

        $oTemplate->setData(array(
                    'context' => &$this,
                    'form' => $oForm,
                    ));
        return $oTemplate->render();
    }

    /**
     * Method for getting the MIME type extension for the current document.
     *
     * @return string mime time extension
     */
    function getMimeExtension() {

        if($this->oDocument == null || $this->oDocument == "" || PEAR::isError($this->oDocument) ) return _kt('Unknown Type');

        $oDocument = $this->oDocument;
        $iMimeTypeId = $oDocument->getMimeTypeID();
        $mimetypename = KTMime::getMimeTypeName($iMimeTypeId); // mime type name

        // the pdf converter uses the mime type and not the extension.
        return $mimetypename;

        /*
        $sTable = KTUtil::getTableName('mimetypes');
        $sQuery = "SELECT filetypes FROM " . $sTable . " WHERE mimetypes = ?";
        $aQuery = array($sQuery, array($mimetypename));
        $res = DBUtil::getResultArray($aQuery);
        if (PEAR::isError($res)) {
            return $res;
        } else if (count($res) != 0){
            return $res[0]['filetypes'];
        }

        return _kt('Unknown Type');
        */
    }

    /**
     * Method to download the pdf.
     *
     * @author KnowledgeTree Team
     * @access public
     */
    public function do_pdfdownload()
    {
        global $default;
        $oStorage = KTStorageManagerUtil::getSingleton();
        $iDocId = $this->oDocument->iId;

        // Check if pdf has already been created

        $dir = $default->pdfDirectory;
        $file = $dir . '/' . $iDocId . '.pdf';
        $mimetype = 'application/pdf';
        $size = $oStorage->fileSize($file);

        // Set the filename
        $name = $this->oDocument->getFileName();
        $aName = explode('.', $name);
        array_pop($aName);
        $name = implode('.', $aName) . '.pdf';

        if($oStorage->file_exists($file))
        {
            if(!$oStorage->downloadRendition($file, $mimetype, $size, $name))
            {
                $default->log->error('PDF Generator: PDF file could not be downloaded because it doesn\'t exist');
                $this->errorRedirectToMain(_kt('PDF file could not be downloaded because it doesn\'t exist'));
            }
            exit();
        }

        /**
         * Account Routing:: Stuff still in queue
         */
        if(ACCOUNT_ROUTING_ENABLED){
			$default->log->error('PDF Generator: PDF file is in the process queue and not available at this time.');
			$this->errorRedirectToMain(_kt('PDF File is currently queued for processing. Please try again later.'));
        	exit();
        }

        // If not - create one
        $converter = new pdfConverter();
        $converter->setDocument($this->oDocument);
        $res = $converter->processDocument();

        if($res !== true){
            // output error
            $default->log->error('PDF Generator: PDF file could not be generated');
            $this->errorRedirectToMain($res);
            exit();
        }

        if($oStorage->file_exists($file))
        {
            if(KTUtil::download($file, $mimetype, $size, $name) === false)
            {
                $default->log->error('PDF Generator: PDF file could not be downloaded because it doesn\'t exist');
                $this->errorRedirectToMain(_kt('PDF file could not be downloaded because it doesn\'t exist'));
            }
            exit();
        }

        // Check if this is a office 2007 doc
        $mime = $this->getMimeExtension();

        $o2007_types[] = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
    	$o2007_types[] = 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
    	$o2007_types[] = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';

        if(in_array($mime, $o2007_types)){
            $error = _kt('The document is an MS Office 2007 format. This may not be supported by your version of OpenOffice.');
            $default->log->error('PDF Generator: Document is an MS Office 2007 format. OpenOffice must be version 3.0 or higher to support this format. Please upgrade to the latest version.');
        }else{
            $error = _kt('PDF file could not be generated. The format may not be supported by your version of OpenOffice.');
            $default->log->error('PDF Generator: PDF file could not be generated. The format may not be supported by your version of OpenOffice. Please check that you have the latest version installed.');
        }
        $this->errorRedirectToMain($error);
        exit();
    }

    /**
     * Method for downloading the document as a pdf.
     *
     * @deprecated
     * @return true on success else false
     */
    function do_pdfdownload_deprecated() {
        $oDocument = $this->oDocument;
        $oStorage = KTStorageManagerUtil::getSingleton();
        $oConfig =& KTConfig::getSingleton();
        $default = realpath(str_replace('\\','/',KT_DIR . '/../openoffice/program'));
        putenv('ooProgramPath=' . $oConfig->get('openoffice/programPath', $default));
		$cmdpath = KTUtil::findCommand('externalBinary/python');
        // Check if openoffice and python are available
        if($cmdpath == false || !file_exists($cmdpath) || empty($cmdpath)) {
            // Set the error messsage and redirect to view document
            $this->addErrorMessage(_kt('An error occurred generating the PDF - Python binary not found.'));
//            redirect(generateControllerLink('viewDocument',sprintf('fDocumentId=%d',$oDocument->getId())));
			redirect(KTUtil::kt_clean_document_url($oDocument->getId()));
            exit(0);
        }

        //get the actual path to the document on the server
        $sPath = $oStorage->getDocStoragePath($oDocument);

        if ($oStorage->file_exists($sPath))
        {

            // Get a tmp file
            $sTempFilename = $oStorage->tempnam('/tmp', 'ktpdf');

            // We need to handle Windows differently - as usual ;)
            if (substr( PHP_OS, 0, 3) == 'WIN') {

                $cmd = "\"" . $cmdpath . "\" \"". KT_DIR . "/bin/openoffice/pdfgen.py\" \"" . $sPath . "\" \"" . $sTempFilename . "\"";
                $cmd = str_replace( '/','\\',$cmd);

                // TODO: Check for more errors here
                // SECURTIY: Ensure $sPath and $sTempFilename are safe or they could be used to excecute arbitrary commands!
                // Excecute the python script. TODO: Check this works with Windows
                $res = `"$cmd" 2>&1`;
                //print($res);
                //print($cmd);
                //exit;

            } else {

                // TODO: Check for more errors here
                // SECURTIY: Ensure $sPath and $sTempFilename are safe or they could be used to excecute arbitrary commands!
                // Excecute the python script.
                $cmd = $cmdpath . ' ' . KT_DIR . '/bin/openoffice/pdfgen.py ' . escapeshellcmd($sPath) . ' ' . escapeshellcmd($sTempFilename);
                $res = shell_exec($cmd." 2>&1");
                //print($res);
                //print($cmd);
                //exit;

            }

            // Check the tempfile exists and the python script did not return anything (which would indicate an error)
            if ($oStorage->file_exists($sTempFilename) && $res == '') {

                $mimetype = 'application/pdf';
                $size = $oStorage->fileSize($sTempFilename);
                $name = substr($oDocument->getFileName(), 0, strrpos($oDocument->getFileName(), '.') ) . '.pdf';
                KTUtil::download($sTempFilename, $mimetype, $size, $name);

                // Remove the tempfile
                $oStorage->unlink($sTempFilename);

                // Create the document transaction
                $oDocumentTransaction = & new DocumentTransaction($oDocument, 'Document downloaded as PDF', 'ktcore.transactions.download', $aOptions);
                $oDocumentTransaction->create();
                // Just stop here - the content has already been sent.
                exit(0);

            } else {
                // Set the error messsage and redirect to view document
                $this->addErrorMessage(sprintf(_kt('An error occurred generating the PDF - %s') , $res));
                redirect(KTUtil::kt_clean_document_url($oDocument->getId()));
                //redirect(generateControllerLink('viewDocument',sprintf('fDocumentId=%d',$oDocument->getId())));
                exit(0);
            }

        } else {
            // Set the error messsage and redirect to view document
            $this->addErrorMessage(_kt('An error occurred generating the PDF - The path to the document did not exist.'));
//            redirect(generateControllerLink('viewDocument',sprintf('fDocumentId=%d',$oDocument->getId())));
			redirect(KTUtil::kt_clean_document_url($oDocument->getId()));
            exit(0);
        }


    }

    /**
     * Method for duplicating the document as a pdf.
     *
     */
    function do_pdfduplicate() {

        $this->oPage->setBreadcrumbDetails(_kt('Generate PDF'));
        $oTemplate =& $this->oValidator->validateTemplate('ktstandard/PDFPlugin/PDFPlugin');

        $oForm = $this->form_main();

        $oTemplate->setData(array(
                    'context' => &$this,
                    'form' => $oForm,
                    ));
        $this->addErrorMessage(_kt('NOT IMPLEMENTED YET: This will create a pdf copy of the document as a new document.'));
        return $oTemplate->render();

    }

    /**
     * Method for replacing the document as a pdf.
     *
     */
    function do_pdfreplace() {

        $this->oPage->setBreadcrumbDetails(_kt('Generate PDF'));
        $oTemplate =& $this->oValidator->validateTemplate('ktstandard/PDFPlugin/PDFPlugin');

        $oForm = $this->form_main();

        $oTemplate->setData(array(
                    'context' => &$this,
                    'form' => $oForm,
                    ));
        $this->addErrorMessage(_kt('NOT IMPLEMENTED YET: This will replace the document with a pdf copy of the document.'));
        return $oTemplate->render();

    }
}
?>
