<?
/**
 * $Id$
 *
 * KnowledgeTree Open Source Edition
 * Document Management Made Simple
 * Copyright (C) 2004 - 2008 The Jam Warehouse Software (Pty) Limited
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
 *
 */

require_once(dirname(__FILE__) . '/../test.php');
require_once(KT_DIR .  '/ktapi/ktapi.inc.php');


class APIAuthenticationTestCase extends KTUnitTestCase 
{
	function testAdmin()
	{
		$ktapi = new KTAPI();
		
		$session = $ktapi->start_session('admin','admin');
		$this->assertTrue(is_a($session,'KTAPI_UserSession'));
		$this->assertTrue($session->is_active());
		
		$ktapi = new KTAPI();
		$session = $ktapi->get_active_session($session->session);
		$this->assertTrue(is_a($session,'KTAPI_UserSession'));
		
		
		$session->logout();
		$this->assertFalse($session->is_active());
	}
	
	function testSystemLogin()
	{
		$ktapi = new KTAPI();
		
		$session = $ktapi->start_system_session();
		$this->assertTrue(is_a($session,'KTAPI_SystemSession'));
		$this->assertTrue($session->is_active());
				
		$session->logout();
		$this->assertFalse($session->is_active());
	}
	
	function testAnonymousLogin()
	{
		$ktapi = new KTAPI();
		
		$session = $ktapi->start_anonymous_session();
		$this->assertTrue(is_a($session,'KTAPI_AnonymousSession'));
		$this->assertTrue($session->is_active());
		
		$ktapi = new KTAPI();
		$session = $ktapi->get_active_session($session->session);
		$this->assertTrue(is_a($session,'KTAPI_AnonymousSession'));

		
		$session->logout();
		$this->assertFalse($session->is_active());
	}
	
}
?>