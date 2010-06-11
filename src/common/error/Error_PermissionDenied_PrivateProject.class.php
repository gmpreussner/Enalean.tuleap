<?php
/**
 * Copyright (c) STMicroelectronics, 2010. All Rights Reserved.
 *
 * This file is a part of Codendi.
 *
 * Codendi is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Codendi is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Codendi. If not, see <http://www.gnu.org/licenses/>.
 */


require_once('Error_PermissionDenied.class.php');

class Error_PermissionDenied_PrivateProject extends Error_PermissionDenied {
    /**
     * Constructor of the class
     *
     * @return void
     */
    function __construct() {
        parent::__construct();
    }


    /**
     * Dispaly interface to ask for membership
     */
    function displayInterface() {
        $url= new URL();
        $groupId =  (isset($GLOBALS['group_id'])) ? $GLOBALS['group_id'] : $url->getGroupIdFromUrl($_SERVER['REQUEST_URI']);
        $userId = $this->getUserManager()->getCurrentUser()->getId();
        
        echo "<b>".$GLOBALS['Language']->getText('include_exit','perm_denied')."</b>";
        echo '<br></br>';
        echo "<br>".$GLOBALS['Language']->getText('include_exit','private_project_no_perm');
        echo $GLOBALS['Language']->getText('include_exit','request_to_admin');
                
        echo '<br></br>';
        echo '<form action="/sendmessage.php" method="POST" name="display_form"  enctype="multipart/form-data">
                 <textarea wrap="virtual" rows="5" cols="70" name="msg_private_project"></textarea></p>
                 <input TYPE="HIDDEN" id="func" name="func" VALUE="private_project_request">
                 <input TYPE="HIDDEN" id="groupId" name="groupId" VALUE="' .$groupId. '">
                 <input TYPE="HIDDEN" id="userId" name="userId" VALUE="' .$userId. '">
                 <br><input name="Submit" type="submit" value="'.$GLOBALS['Language']->getText('include_exit', 'send_mail').'"/></br>';
        echo '</form>';
    }


    /**
     * Prepare the mail inputs
     */
    function processMail() {
        $request =HTTPRequest::instance();
        
        $pm = $this->getProjectManager();
        $project = $pm->getProject($request->get('groupId'));
    
        $um = $this->getUserManager();
        $user = $um->getUserById($request->get('userId'));
        

        $messageToAdmin = trim($request->get('msg_private_project'));
        $messageToAdmin = ereg_replace("(\r\n)|(\n)","###", $messageToAdmin);
        
        $hrefApproval = get_server_url().'/project/admin/?group_id='.$request->get('groupId');
        
        $subject = "Request for private project membership: ".$project->getPublicName();
        $body = $user->getRealName()." as(".$user->getName().") has just asked to be member of ".$project->getPublicName()."\n\n".
                $user->getName()." has added this personal message:  ".$messageToAdmin."\n\n".
                "Please click on the following URL to approve the add\n".
                $hrefApproval."  or not and communicate that information to him \n\n".
                "-- The ".$GLOBALS['sys_name']." Team";
                
        return $this->sendMail($project, $subject, $body);
    }
 
}
?>
