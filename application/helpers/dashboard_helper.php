<?php

//probally move this function once working
function get_dashboard(){
	
	check_for_alerts();	
		
	if(user_group('admin') == TRUE){
		return 'admin/admin';
	} 
	elseif(user_group('user') == TRUE) {
		echo "error";
		return 'parents/parents';
	}
	elseif(user_group('alerts') == TRUE) {
		return 'alerts/alerts';
	}
	else {
		echo "error";
	}	

}

function check_for_alerts(){
	
}
	
?>