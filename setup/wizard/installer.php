<?php
/**
* Installer Controller.
*
* KnowledgeTree Community Edition
* Document Management Made Simple
* Copyright (C) 2008,2009 KnowledgeTree Inc.
* Portions copyright The Jam Warehouse Software (Pty) Limited
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
*
* @copyright 2008-2009, KnowledgeTree Inc.
* @license GNU General Public License version 3
* @author KnowledgeTree Team
* @package Installer
* @version Version 0.1
*/

class Installer {
	/**
	* Reference to simple xml object
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var object SimpleXMLElement 
	*/
    protected $simpleXmlObj = null;
    
	/**
	* Reference to step action object
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var object StepAction
	*/
    protected $stepAction = null;
    
	/**
	* Reference to session object
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var object Session
	*/
    protected $session = null;
    
	/**
	* List of installation steps as strings
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var array string
	*/
    protected $stepClassNames = array();
    
	/**
	* List of installation steps as human readable strings
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var array string
	*/
	protected $stepNames = array();
    
	/**
	* List of installation steps as human readable strings
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var array string
	*/
	protected $stepObjects = array();
    
	/**
	* Order in which steps have to be installed
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var array string
	*/
	protected $installOrders = array();
	
	/**
	* Flag if a step object needs confirmation
	*
	* @author KnowledgeTree Team
	* @access protected
	* @var array boolean
	*/
    protected $stepConfirmation = false;
    
	/**
	* Constructs installation object
	*
	* @author KnowledgeTree Team
	* @access public
	* @param object Session $session Instance of the Session object
 	*/
    public function __construct($session = null) {
        $this->session = $session;
    }
    
	/**
	* Sets any variables passed through for testing purposes
	*
	* @author KnowledgeTree Team
	* @access private
	* @param none
	* @return void
 	*/
    private function _setSessionVars() {
		if(isset($_GET['bypass'])) {
			$bypass = $_GET['bypass'];
			$this->session->set('bypass', $bypass);
		}
    }

	/**
	* Read xml configuration file
	*
	* @author KnowledgeTree Team
	* @param string $name of config file
	* @access private
	* @return object 
	*/
    private function _readXml($name = "config.xml") {
    	try {
        	$this->simpleXmlObj = simplexml_load_file(CONF_DIR.$name);
    	} catch (Exception $e) {
    		echo "Error loading file : $e";
    	}
    }

	/**
	* Checks if first step of installer
	*
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return boolean
	*/
    private function _firstStep() {
        if(isset($_GET['step_name'])) {
            return false;
        }
        
        return true;
    }

	/**
	* Checks if first step of installer
	*
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return boolean
	*/
    private function _firstStepPeriod() {
        if(isset($_GET['step_name'])) {
        	if($_GET['step_name'] != 'welcome')
            	return false;
        }
        
        return true;
    }
    
	/**
	* Returns next step
	*
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return string
	*/
    private function _getNextStep() {
        return $this->_getStepName(1);
    }

	/**
	* Returns previous step
	*
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return string
	*/
    private function _getPreviousStep() {
        return $this->_getStepName(-1);
    }

	/**
	* Returns the step name, given a position
	*
	* @author KnowledgeTree Team
	* @param integer $pos current position
	* @access private
	* @return string $name
	*/
    private function _getStepName($pos = 0) {
        if($this->_firstStep()) {
            $step = (string) $this->simpleXmlObj->steps->step[0];
        } else {
            $pos += $this->getStepPosition();
            $step = (string) $this->simpleXmlObj->steps->step[$pos];
        }

        return $step;
    }

	/**
	* Executes next step
	*
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return string
	*/
    private function _proceed() {
        $step_name = $this->_getNextStep();

        return $this->_runStepAction($step_name);
    }

	/**
	* Executes previous step
	*
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return string
	*/
    private function _backward() {
        $step_name = $this->_getPreviousStep();

        return $this->_runStepAction($step_name);
    }

	/**
	* Executes step landing
	*
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return string
	*/
    private function _landing() {
        $step_name = $this->_getStepName();

        return $this->_runStepAction($step_name);
    }

	/**
	* Executes step based on step class name
	*
	* @author KnowledgeTree Team
	* @param string $step_name
	* @access private
	* @return string
	*/
    private function _runStepAction($stepName) {
        $this->stepAction = new stepAction($stepName);
        $this->stepAction->setSteps($this->getSteps());
        $this->stepAction->setStepNames($this->getStepNames());
        $this->stepAction->setDisplayConfirm($this->stepConfirmation);
        $this->stepAction->loadSession($this->session);
        
        return $this->stepAction->doAction();
    }

	/**
	* Set steps class names in string format
	*
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return array
	*/
    private function _getInstallOrders() {
        return $this->installOrders;
    }
    
	/**
	* Set steps as names
	*
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return void
	*/
    private function _xmlStepsToArray() {
        foreach($this->simpleXmlObj->steps->step as $d_step) {
        	$step_name = (string) $d_step[0];
            $this->stepClassNames[] = $step_name; // Store steps as strings
            $this->stepNames[$step_name] = (string) $d_step['name']; // Store steps as human readable strings
            if(isset($d_step['order'])) {
				$order = (string) $d_step['order'];
            	$this->installOrders[$order] = $step_name; // Store step install order
            }
        }
    }
    
	/**
	* Install steps
	*
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return void
	*/
    private function _runStepsInstallers() {
    	$steps = $this->_getInstallOrders();
    	for ($i=1; $i< count($steps)+1; $i++) {
    		$this->_installHelper($steps[$i]);
    	}
    	
    	$this->_completeInstall();
    }
    
	/**
	* Complete install cleanup process
	*
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return void
	*/
    private function _completeInstall() {
    	unlink("install");
    }
    
	/**
	* Install steps helper
	*
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return void
	*/
    private function _installHelper($className) {
    	$stepAction = new stepAction($className); // Instantiate a step action
    	$class = $stepAction->createStep(); // Get step class
    	if($class) { // Check if class Exists
	    	if($class->runInstall()) { // Check if step needs to be installed
				$class->setDataFromSession($className); // Set Session Information
				$class->setPostConfig(); // Set any posted variables
				$response = $class->installStep(); // Run install step
				// TODO : Break on error response
	    	}
    	} else {
    		die("$className : Class Files Missing");
    	}
    }
    
	/**
	* Reset all session information on welcome landing
	*
	* @author KnowledgeTree Team
	* @param none
	* @access private
	* @return void
	*/
    private function _resetSessions() {
    	if($this->session) {
	    	if($this->_firstStepPeriod()) {
	    		foreach ($this->getSteps() as $class) {
	    			$this->session->un_setClass($class);
	    		}
	    	}
    	}
    }

	/**
	* Main control to handle the flow of install
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return void
	*/
    public function step() {
        $this->_readXml(); // Xml steps
        $this->_xmlStepsToArray(); // String steps
    	$this->_resetSessions(); // Make sure
    	$this->_setSessionVars();
        $response = $this->_landing();
        switch($response) {
            case 'next':
                $this->_proceed(); // Load next window
            	break;

            case 'previous':
                $this->_backward(); // Load previous window
            	break;
            	
            case 'confirm':
                $this->stepConfirmation = true;
                $this->_landing();
            	break;
            	
            case 'error':
                $this->_landing(); // Load landing with errors
            	break;
            	
            case 'landing':
                $this->_landing(); // Load landing
            	break;
            	
            case 'install':
                $this->_runStepsInstallers(); // Load landing
                $this->_proceed(); // Load next window
            	break;
            	
            default:
                die("Response $response: That was unexpected"); // No class response
            	break;
        }

        $this->stepAction->paintAction(); // Display step
    }

	/**
	* Returns the step number
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return integer $pos
	*/
    public function getStepPosition() {
        $pos = 0;
        foreach($this->simpleXmlObj->steps->step as $d_step) {
            $step = (string) $d_step;
            if ($step == $_GET['step_name']) {
                   break;
            }
            $pos++;
        }
        if(isset($_GET['step'])) {
            if($_GET['step'] == "next")
                $pos = $pos+1;
            else
                $pos = $pos-1;
        }

        return $pos;
    }
    
	/**
	* Returns the step names for classes
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return array
	*/
    public function getSteps() {
        return $this->stepClassNames;
    }

	/**
	* Returns the steps as human readable string
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return array
	*/
    public function getStepNames() {
        return $this->stepNames;
    }

	/**
	* Dump of SESSION 
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return array
	*/
    public function showSession() {
        echo '<pre>';
        print_r($_SESSION);
        echo '</pre>';
    }
    
	/**
	* Display errors that are not allowing the installer to operate
	*
	* @author KnowledgeTree Team
	* @param none
	* @access public
	* @return void
	*/
    public function resolveErrors($errors) {
    	echo $errors;
    	exit();
    }    
}

?>