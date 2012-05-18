<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
	
	// TODO: refactor this helper. This is gross. Try to use some sort of associative array.
	
	const waitlistID = 1;
	const registerAChildID = 6;
	const MedicalInformationID = 7;
	const registrationCompleteID = 8;
	//const  ID = 9;
	//const  ID = 10;
	//const  ID = 11;
	//const  ID = 12;
	
	function setNotification($type , $userID, $additionalInfo = null, $urlParam = null){
		switch ($type) {
			case "waitlistAChild":
				set($userID, waitlistID, $additionalInfo, $urlParam);
				break;
			
			case "registerAChild":
				set($userID, registerAChildID, $additionalInfo, $urlParam);
				break;
				
			case "MedicalInformation":
				set($userID, MedicalInformationID, $additionalInfo, $urlParam);
				break;
			case "registrationComplete":
				set($userID, registrationCompleteID, $additionalInfo, $urlParam);
				break;
		}
	}
	
	function unsetNotification($type , $userID, $additionalInfo = null, $urlParam = null){
		switch ($type) {
			case "waitlistAChild":
				delete($userID, waitlistID, $additionalInfo, $urlParam);
				break;
				
				case "registerAChild":
				delete($userID, registerAChildID, $additionalInfo, $urlParam);
				break;
		}		
		
	}
	
	function set($userID , $id, $additionalInfo = null, $urlParam = null){
	    mysql_query("INSERT INTO UserNotifications Value(" . $id . ",'" . $userID . "', '" . $additionalInfo . "', '" . $urlParam . "')");
	}
	
	function delete($userID, $id){
	    mysql_query("DELETE FROM UserNotifications WHERE UserID =" . $userID . " AND NotificationID =" . $id );
	}
	
	function getUserIDFromFormID($ids){
		$waitlist = Waitlist_form::find_by_formid($ids);
		$waitlistAttr = $waitlist->attributes();
        
		return $waitlistAttr['userid'];

	}

	function getUsernameFromFormID($ids){
		$UserID = getUserIDFromFormID($ids);
		
		$users = User::find_by_id($UserID);
		$usersAttr = $users->attributes();
        
		return $usersAttr['username'];

	}
	
	function emailParentAndLetThemKnowTheyCanRegisterAStudent($ids){
        	
		$username = getUsernameFromFormID($ids);	
			
        $query =   "SELECT Parent.FirstName, Parent.LastName, users.email 
                    FROM users
                    JOIN Parent ON (users.id = Parent.UserID)
                    WHERE users.username ='" . $username . "'";     
        
        $result = mysql_query($query);
        
        $firstName =  mysql_result($result, 0, "FirstName");
        $lastName =  mysql_result($result, 0, "LastName");
        $email =  mysql_result($result, 0, "email");	
			
			
		$to = $email;
		$subject = "New MSG from CMS for" . $firstName . ", " . $lastName;
		$body = "ATTN: " . $firstName . ", " . $lastName .  "\n\nYou can now finish enrolling your student.";
		if (mail($to, $subject, $body)) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
    function getUserinfo($username){ 
        $query =   "SELECT Parent.FirstName, Parent.LastName, users.email 
                    FROM users
                    JOIN Parent ON (users.id = Parent.UserID)
                    WHERE users.username ='" . $username . "'";     
        
        $result = mysql_query($query);
        
        $info['first'] =  mysql_result($result, 0, "FirstName");
        $info['last'] =  mysql_result($result, 0, "LastName");
        $info['email'] =  mysql_result($result, 0, "email");
        
        return $info;
    }

?>