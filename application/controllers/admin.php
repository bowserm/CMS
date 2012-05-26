<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Admin extends Application{
	
	private static $data = array();
	
	public function __construct(){
				
		parent::__construct();
		
		/* restrict access to all but admin */
		$this->ag_auth->restrict('admin');
		
		/* Disable Cashcing */
		$this->output->nocache();
		
		/* Load helpers */
		$this->load->helper(array('url', 'form', 'registration', 'menu', 'language' , 'notification'));
        
		/* Load libraries */
        $this->load->library('Repositories/Registration_Repository', '', 'reg');
		$this->load->library('grocery_CRUD');
		
		/* setup default view data */
		$this->data['title'] = 'Admin Dashboard';
		$this->data['MenuItems'] = get_menu_items('admin');
	}
	
	public function index(){
		
		// Main function called when this controller is loaded		
		if(logged_in()){
				
		//* load views */
		$this->load->view('templates/header', $this->data);		
		$this->load->view('admin/dashboard');
		$this->load->view('templates/footer');
			
		}
		else{
			$this->login();
		}
	}
	
	function manageStudents(){
		// TODO: emergency contacts need to be handled in some creative manner
		$crud = new grocery_CRUD();
		$crud->set_table('Student')
			 ->set_relation('UserID', 'users', 'username')
			 ->set_relation('ClassID', 'Classroom', 'ClassName', array('Enabled' => '1'))
			 ->set_relation('ProgramID', 'Program', '{Days}, {StartTime} - {EndTime}')
			 //->set_relation('EmergencyContactID1', 'EmergencyContact', '{ECName}|{ECPhone}|{ECRelationship}')
	         ->columns('FirstName', 'LastName', 'ClassID', 'PhoneNumber', 'Medical Information', 'Admissions Form', 'Waitlist Questionaire', 'IsEnrolled')
			 ->callback_column('Medical Information', array($this, 'getMedicalInformationLink'))
			 ->callback_column('Admissions Form', array($this, 'getAdmissionsFormLink'))
			 ->callback_column('Waitlist Questionaire', array($this, 'getWaitlistQuestionaireLink'))
			 ->callback_edit_field('UserID')
			 ->display_as('UserID', 'Username')
			 ->display_as('ClassID', 'Classroom')
			 ->display_as('PhoneNumber', 'Phone')
			 ->display_as('EmergencyContact', 'Emergency Contacts')
			 ->display_as('ProgramID', 'Program')
			 ->display_as('EmergencyContactID1', 'Emergency Contact 1')
			 ->display_as('IsEnrolled', 'Enrollment Status')
			 ->change_field_type('UserID', 'readonly')
			 ->change_field_type('Gender', 'readonly')
			 ->change_field_type('IsEnrolled', 'true_false')
			 ->change_field_type('UDTTM', 'hidden', date('Y-m-d H:i:s', time()))
			 ->unset_edit_fields('EmergencyContactID1', 'EmergencyContactID2', 'EmergencyContactID3', 'QuestionaireID')
			 ->unset_add()
			 ->unset_delete();
			 
		$output = $crud->render();
				
		$this->load->view('templates/header', $this->data);		
		$this->load->view('admin/record_management/manage_students', $output);
		$this->load->view('templates/footer');
	}
	
	# Callback Column for generating links to the student's Medical Information.
	function getMedicalInformationLink($value, $row) {
		$medInfo = Student_medical::find_by_studentid($row->StudentID);
		if ($medInfo != null || !empty($medInfo)) {
			return '<a href="' . base_url('admin/medicalInformationGrid/edit/' . $row->StudentID) . '" target="_blank">' . 'Medical Information' . '</a>';
		}
		return;
	}
	
	# Callback Column for generating links to the student's Admissions Form.
	function getAdmissionsFormLink($value, $row) {
		$form = Admissions_form::find_by_studentid($row->StudentID);
		if ($form != null || !empty($form)) {
			return '<a href="' . base_url('admin/admissionsFormGrid/edit/' . $row->StudentID) . '" target="_blank">' . 'Admissions Form' . '</a>';
		}
	}
	
	# Callback Column for generating links to the student's Admissions Form.
	function getWaitlistQuestionaireLink($value, $row) {
		return '<a href="' . base_url() . '" target="_blank">' . 'Waitlist Questionaire' . '</a>';
	}
		
	function medicalInformationGrid($studentID) {
		$crud = new grocery_CRUD();
		$crud->set_table('StudentMedicalInformation')
	         ->change_field_type('StudentID', 'hidden', $studentID);
		$crud->where('StudentID', $studentID);
		$crud->unset_list();
		
		// TODO: fix validation.
		$crud->required_fields('PreferredHospital', 'HospitalPhone', 'Physician', 'PhysicianPhone', 'Dentist', 'DentistPhone');
		$crud->set_rules('HospitalPhone','Hospital Phone','min_length[12]');
		$crud->set_rules('PhysicianPhone','Physician Phone','min_length[12]');
		$crud->set_rules('DentistPhone','Dentist Phone','min_length[12]');
		
		$output = $crud->render();
				
		$this->load->view('templates/header', $this->data);		
		$this->load->view('admin/record_management/manage_medical_form', $output);
		$this->load->view('templates/footer');
	}
	
	function admissionsFormGrid($studentID) {
		$crud = new grocery_CRUD();
		$crud->set_table('AdmissionsForm')
	         ->change_field_type('StudentID', 'hidden', $studentID);
		$crud->where('StudentID', $studentID);
		$crud->unset_list();
		
		// TODO: add validation
		
		$output = $crud->render();
				
		$this->load->view('templates/header', $this->data);		
		$this->load->view('admin/record_management/manage_admissions_form', $output);
		$this->load->view('templates/footer');
	}
	
	function manageAccounts(){
		//this is so we can add more grids using iframes
		// hopefully soon grocery CRUD supports multiple tables with out using iframes in 1 view soon	
		$this->manActGrid();
	}
	
	function manActGrid(){
		$crud = new grocery_CRUD();
		
		$crud->set_table('users')
			->columns('username', 'email', 'password', 'group_id', 'Enabled', 'HasChangedPassword');
		
		$crud->fields('username', 'email', 'password', 'group_id', 'Enabled', 'HasChangedPassword');
		$crud->required_fields('username', 'email', 'password', 'group_id', 'Enabled', 'HasChangedPassword');
		
		//$crud->change_field_type('password', 'password');
     	$crud->callback_before_update(array($this,'encrypt_password_callback'));
		$crud->callback_before_insert(array($this,'encrypt_password_callback'));
		
    	$output = $crud->render();
		$this->load->view('templates/header', $this->data);		
		$this->load->view('templates/grid', $output);
		$this->load->view('templates/footer');
	}

	function encrypt_password_callback($post_array) {
		$post_array['password'] = $this->ag_auth->salt($post_array['password']);
		return $post_array;
	}        

	
	// grids for the dashboard
	function waitlistGrid(){
			
		$crud = new grocery_CRUD();
			
		$crud->set_table('WaitlistForm')
			->columns('FirstName', 'LastName')
			->display_as('FirstName', 'First')
			->display_as('LastName','Last')
			->unset_operations();
		
		$crud->where('IsWaitlisted', 1);	
		
    	$output = $crud->render();
		$this->load->view('templates/grid',$output);
	}

	function preEnrolledGrid(){
		
		$crud = new grocery_CRUD();
		
		$crud->set_table('WaitlistForm')
			->columns('FirstName', 'LastName')
			->display_as('FirstName', 'First')
			->display_as('LastName','Last')
			->unset_operations();
		
		$crud->where('IsPreEnrolled', 1);
		
    	$output = $crud->render();
		$this->load->view('templates/grid',$output);
	}
	
	function volunteerLogGrid(){
			
		$crud = new grocery_CRUD();
		
		$crud->set_table('VolunteerLogEntry')
			->set_relation('UserID', 'users', 'username')
			->columns('UserID', 'Hours', 'Description', 'SubmissionDTTM')
			->display_as('SubmissionDTTM', 'Date Submitted')
			->display_as('UserID', 'Username')
			->unset_operations();
		
    	$output = $crud->render();
		$this->load->view('templates/grid', $output);
	}
	
	# The grocery crud for the current user's notifications. This grid is dedicated
	# for viewing. Adds, Edits, and Deletes should not be allowed.
	function notificationGrid() {

		$crud = new grocery_CRUD();
		$crud->set_table('Notifications')
			 ->set_relation('NotificationID', 'UserNotifications', 'UserID')
	         ->columns('Description')
			 ->display_as('NotificationID', 'UserID')
			 ->callback_column('Description', array($this, 'get_notification_URL'))
			 ->unset_operations();
			 
		$crud->where('UserID', user_id());

        $output = $crud->render();
		
		//$this->output->enable_profiler(TRUE);//Turns on CI debugging
		
		$this->load->view('templates/grid', $output);
	}
	// end grids for dashboard
	
	# Callback Add Field for the SubmissionDTTM.
	# We want a the SubmissionDTTM to be readonly and set to the current datetime.
	# This function adds the SubmissionDTTM to the add form of a grocery crud.
	function get_notification_URL($value, $row) {
		return '<a href="' . base_url($row->URL) . '" target="_blank">' . $row->Description . '</a>';
	}
	
	// this function is for creating a new account for parents
	function addParentUserAccount(){
		$this->form_validation->set_rules('first', 'First Name', 'required|min_length[1]');
        $this->form_validation->set_rules('last', 'Last Name', 'required|min_length[1]');
        //$this->form_validation->set_rules('middle', 'Middle Name', '');
        $this->form_validation->set_rules('email', 'Email Address', 'required|min_length[3]|valid_email|callback_field_exists');

		if($this->form_validation->run() == FALSE){
			$this->load->view('templates/header', $this->data);	
			$this->load->view('admin/register/add_parent_user');
			$this->load->view('templates/footer');
		}
		else{
			$firstName = set_value('first');
            //$middleName = set_value('middle');
            $lastName = set_value('last');
			$username = $firstName . '.' . $lastName;
            
            //check db to make sure this is a unique name
            // if not add a number and try again
            if (!$this->reg->isUsernameUnique($username)){
                $isUnique = FALSE; 
                $i = 1;
                while (!$isUnique) {         
                    $i = $i + 1;
                    $isUnique = $this->reg->isUsernameUnique($username . '.'. $i);
                }
                $username = $username . '.'. $i;
            }
            
            $plainTextPassword = generatePassword();
            
			// encrypt the password
			$password = $this->ag_auth->salt($plainTextPassword);
			$email = set_value('email');

			if($this->ag_auth->register($username, $password, $email) === TRUE){
				$this->sendEmail(sendNewUserAccountCreationEmail($firstName, $lastName, $email, $username, $plainTextPassword));
                
                $this->data['firstName'] = $firstName;
                $this->data['lastName'] = $lastName;
                $this->data['email'] = $email;
                $this->data['username'] = $username;
                $this->data['plainTextPassword'] = $plainTextPassword;
                
                $this->load->view('templates/header', $this->data);  
                $this->load->view('admin/register/success' , $this->data);
                $this->load->view('templates/footer');
				
			} 
			else{
				echo "Error";
			}
            
			$newUserAttr = User::find_by_username($username)->attributes();
            $userId = $newUserAttr['id'];
                  
            $this->createParent($userId, $firstName, $lastName, $email);
			setNotification('waitlistAChild', $userId);
             //$this->output->enable_profiler(TRUE);//Turns on CI debugging
		} 
	}

	# This is the Interview/Observation form that the administrator
	# fills out for the parent.
	function interviewObservationForm(){

		$viewData['classes'] = Classroom::find_all_by_enabled('1');

		# Set up validation for admissionsPage1.php
		$this->validateInterviewObservationForm();

		// if user is posting back answers, then save the form
		if ($this->form_validation->run() == TRUE) {
			// get answers from waitlist questionaire
			$this->storeInterviewObservationForm();

			// back to the admin dashboard
			redirect('admin');
		} else {
			// display the interview observation form
			$this->load->view('templates/header', $this->data);  
        	$this->load->view('admin/register/interview_observation', $viewData);
        	$this->load->view('templates/footer');
		}
	}

	# Saves the interview and observation form that the admin fills out.
	function storeInterviewObservationForm(){
		$ioform = new Interview_observation_form();

		$ioform->parentnames =  set_value('pNames');
		$ioform->childrennamesages = set_value('namesAndAges');
		$ioform->firstcontacteddttm = date('Y-m-d H:i:s', strtotime(set_value('contactDateTime')));
		$ioform->phonenumber = set_value('phoneNumber');
		$ioform->visitdttm = date('Y-m-d H:i:s', strtotime(set_value('visitDateTime')));
		$ioform->email = set_value('email');
		$ioform->websearchref = set_value('webSearch');
		$ioform->cmsfamilyref = set_value('cmsFamily');
		$ioform->friendsref = set_value('friends');
		$ioform->adref = set_value('adIn');
		$ioform->adrefnote = set_value('adInRefNote');
		$ioform->otherref = set_value('otherRef');
		$ioform->otherrefnote = set_value('otherRefNote');
		$ioform->levelofinterest = set_value('interestLevel');
		$ioform->levelofunderstanding = set_value('understandingLevel');
		$ioform->willingnesstolearn = set_value('willingnessLevel');
		
		// combine city and state
		$city = set_value('movingCity');
		$state = set_value('movingState');
		
		if($city != null && $state != null)
			$citystate = $city . ', ' . $state;
		else if($city != null)
			$citystate = $city;
		else if($state != null)
			$citystate = $state;
		else {
			$citystate = "";
		}
		
		$ioform->newcitystate = $citystate;
		$ioform->newschool = set_value('movingSchool');
		$ioform->islearningindependently = set_value('independent');
		$ioform->islearningatownpace = set_value('ownPace');
		$ioform->ishandsonlearner = set_value('handsOn');
		$ioform->ismixedages = set_value('mixedAges');
		$ioform->montessoriimpressions = set_value('montessoriImpressions');
		$ioform->interviewimpressions = set_value('interviewImpressions');
		$ioform->observationdttm = date('Y-m-d H:i:s', strtotime(set_value('observationDateTime')));
		$ioform->classid = set_value('classroom');
		$ioform->attendedobservation = set_value('attendedRadio');
		$ioform->ontimetoobservation = set_value('onTimeRadio');
		$ioform->interviewdttm = date('Y-m-d H:i:s', strtotime(set_value('interviewDateTime')));
		$ioform->appreceiveddttm = date('Y-m-d H:i:s', strtotime(set_value('appReceivedDateTime')));
		$ioform->feereceiveddttm = date('Y-m-d H:i:s', strtotime(set_value('feeReceivedDateTime')));

		$ioform->save();
	}

	# Sets the validation rules for the InterviewObservationForm
	function validateInterviewObservationForm(){
		$this->form_validation->set_rules('pNames', 'Parents', 'required');
		$this->form_validation->set_rules('namesAndAges', 'Children', 'required');
		$this->form_validation->set_rules('contactDateTime', 'Contact DateTime', 'required|valid_date');
		$this->form_validation->set_rules('phoneNumber', 'Phone number', 'required|min_length[12]');
		$this->form_validation->set_rules('visitDateTime', 'Visit DateTime', 'required|valid_date');
		$this->form_validation->set_rules('email', 'Email', 'required|valid_email');
		$this->form_validation->set_rules('webSearch', 'Websearch Reference', 'exact_length[1]|is_natural');
		$this->form_validation->set_rules('cmsFamily', 'CMS Family Reference', 'exact_length[1]|is_natural');
		$this->form_validation->set_rules('friends', 'Friend Reference', 'exact_length[1]|is_natural');
		$this->form_validation->set_rules('adIn', 'Ad Reference', 'exact_length[1]|is_natural');
		$this->form_validation->set_rules('adInRefNote', 'Ad Reference Description', '');
		$this->form_validation->set_rules('otherRef', 'Other Reference', 'exact_length[1]|is_natural');
		$this->form_validation->set_rules('otherRefNote', 'Other Reference Description', '');
		$this->form_validation->set_rules('interestLevel', 'Level of interest', 'required');
		$this->form_validation->set_rules('understandingLevel', 'Level of understanding', 'required');
		$this->form_validation->set_rules('willingnessLevel', 'Willingness to learn', 'required');
		$this->form_validation->set_rules('movingCity', 'New City', 'alpha');
		$this->form_validation->set_rules('movingState', 'New State', 'alpha');
		$this->form_validation->set_rules('movingSchool', 'New School', '');
		$this->form_validation->set_rules('independent', 'Independent learner', 'required|exact_length[1]');
		$this->form_validation->set_rules('ownPace', 'Learns at own pace', 'required|exact_length[1]');
		$this->form_validation->set_rules('handsOn', 'Hands-on learner', 'required|exact_length[1]');
		$this->form_validation->set_rules('mixedAges', 'Mixed ages', 'required|exact_length[1]');
		$this->form_validation->set_rules('montessoriImpressions', 'Montessori impressions', 'required');
		$this->form_validation->set_rules('interviewImpressions', 'Interview impressions', 'required');
		$this->form_validation->set_rules('observationDateTime', 'Observation DateTime', 'valid_date');
		$this->form_validation->set_rules('classroom', 'Classroom', 'is_natural_no_zero');
		$this->form_validation->set_rules('attendedRadio', 'Attended observation', 'exact_length[1]');
		$this->form_validation->set_rules('onTimeRadio', 'On time to observation', 'exact_length[1]');
		$this->form_validation->set_rules('interviewDateTime', 'Interview DateTime', 'required|valid_date');
		$this->form_validation->set_rules('appReceivedDateTime', 'Application received DateTime', 'valid_date');
		$this->form_validation->set_rules('feeReceivedDateTime', 'Application fee received DateTime', 'valid_date');
	}

    function createParent($userId, $fName, $lName, $email){
        $parent = new Parental();
        $parent->userid = $userId;
        $parent->firstname = $fName;
        //$parent->middlename = $mName;
        $parent->lastname = $lName;
        $parent->email = $email;
        $parent->uddtm = date('Y-m-d H:i:s', time());
        $parent->save();
    }
    
    // dev function for creating new menu items
    // this function will not be used in final prod
    function addSubItem(){

        $this->form_validation->set_rules('menuItemID', 'menuItemID', 'required');
        $this->form_validation->set_rules('label', 'label', 'required');
        $this->form_validation->set_rules('URL', 'URL', 'required');
        $this->form_validation->set_rules('rankOrder', 'rankOrder', 'required');

        if($this->form_validation->run() == FALSE){
            $this->load->view('templates/header', $this->data);  
            $this->load->view('admin/menu/add_menu_item');
            $this->load->view('templates/footer');
        }
        else{
        
        $menuItemID = set_value('menuItemID');
        $label = set_value('label');
        $URL = set_value('URL');
        $rankOrder = set_value('rankOrder');
                
        $query = "INSERT INTO SubItem (MenuItemID, Label, URL, RankOrder) VALUES (" . $menuItemID . ", '" . $label . "', '" . $URL . "', " . $rankOrder . ")";
        $result = mysql_query($query);
        
        redirect('admin');        
		}
    }
	
	function waitlist($grid1 = 'none', $grid2 = 'none') {
			
	    if ($this->input->post('moveToEnrolled')){
            // cg_preEnrolledForm_item_ids is a var auto made from cg
            // the preEnrolledForm is from the 'id' => 'preEnrolledForm',
            // item_ids i belive referes to 'table_id_name' => 'FormID',
            $ids = $this->input->post('cg_preEnrolledForm_item_ids');
 
            if (count($ids)){
                //TODO: This should be in a model of course
                $this->db->set('IsWaitlisted', 0)->where_in('FormID', $ids)->update('WaitlistForm');
				$this->db->set('IsPreEnrolled', 1)->where_in('FormID', $ids)->update('WaitlistForm');
								
				foreach ($ids as $id) {
					setNotification('registerAChild' , getUserIDFromFormID($id), $id);
					$this->sendEmail(emailParentAndLetThemKnowTheyCanRegisterAStudent($id));
				}
				
            }
        }
        if ($this->input->post('moveToWaitlist')){
            $ids = $this->input->post('cg_WaitlistForm_item_ids');
 
            if (count($ids)){
                //TODO This should be in a model of course
                $this->db->set('IsWaitlisted', 1)->where_in('FormID', $ids)->update('WaitlistForm');
				$this->db->set('IsPreEnrolled', 0)->where_in('FormID', $ids)->update('WaitlistForm');
				
				foreach ($ids as $id) {
					unsetNotification('registerAChild' , getUserIDFromFormID($id), $id);
				}
				
            }
        }	
			  	
		//grid1 - the waitlist table
		$columns = array(
			0 => array(
				'name' => 'cFname',
				'db_name' => 'FirstName',
				'header' => 'First Name',
				'group' => 'Child',
				'required' => TRUE,
				'unique' => TRUE,
				'form_control' => 'text_long',
				'type' => 'string'),
			1 => array(
				'name' => 'cLname',
				'db_name' => 'LastName',
				'header' => 'First Name',
				'group' => 'Child',
				'required' => FALSE,
				'visible' => TRUE,
				'form_control' => 'text_long',
				'type' => 'string'),
			2 => array(
				'name' => 'preEnrolled',
				'db_name' => 'IsPreEnrolled',
				'header' => 'Pre-enrolled',
				'group' => 'Child',
				'allow_filter' => FALSE,
                'visible' => FALSE,
                'form_control' => 'checkbox',
                'type' => 'boolean'),
			3 => array(
				'name' => 'waitlisted',
				'db_name' => 'IsWaitlisted',
				'header' => 'Waitlisted',
				'group' => 'Child',
				'allow_filter' => FALSE,
                'visible' => FALSE,
                'form_control' => 'checkbox',
                'type' => 'boolean')
		);
		      
		$params = array(
			'id' => 'preEnrolledForm',
			'table' => 'WaitlistForm',
			'table_id_name' => 'FormID',
			'url' => 'admin/waitlist',
			'uri_param' => $grid1,
			'params_after' => $grid2,
			'columns' => $columns,
			
			'hard_filters' => array(
                2 => array('value' => FALSE)
			   ,3 => array('value' => TRUE)
            ),
			
			'allow_add' => FALSE,
            'allow_edit' => FALSE,
            'allow_delete' => FALSE,
            'allow_filter' => FALSE,
            'allow_columns' => FALSE,
            'allow_page_size' => FALSE,
			
			'nested' => TRUE,
			'ajax' => TRUE,
			
		);
		
		$this->load->library('carbogrid', $params, 'grid1');
 
        if ($this->grid1->is_ajax)
        {
            $this->grid1->render();
            return FALSE;
        }
 
		//grid2 - the pre-enrolled table
		$columns = array(
			0 => array(
				'name' => 'cFname',
				'db_name' => 'FirstName',
				'header' => 'First Name',
				'group' => 'Child',
				'required' => TRUE,
				'unique' => TRUE,
				'form_control' => 'text_long',
				'type' => 'string'),
			1 => array(
				'name' => 'cLname',
				'db_name' => 'LastName',
				'header' => 'First Name',
				'group' => 'Child',
				'required' => FALSE,
				'visible' => TRUE,
				'form_control' => 'text_long',
				'type' => 'string'),
			2 => array(
				'name' => 'preEnrolled',
				'db_name' => 'IsPreEnrolled',
				'header' => 'Pre-enrolled',
				'group' => 'Child',
				'required' => FALSE,
				'visible' => FALSE,
				'form_control' => 'checkbox',
				'type' => 'string'),
			3 => array(
				'name' => 'waitlisted',
				'db_name' => 'IsWaitlisted',
				'header' => 'Waitlisted',
				'group' => 'Child',
				'allow_filter' => FALSE,
                'visible' => FALSE,
                'form_control' => 'checkbox',
                'type' => 'boolean')
		);
		      
		$params = array(
			'id' => 'WaitlistForm',
			'table' => 'WaitlistForm',
			'table_id_name' => 'FormID',
			'url' => 'admin/waitlist',
			'uri_param' => $grid2,
			'params_before' => $grid1,
			'columns' => $columns,
			
			'hard_filters' => array(
                2 => array('value' => TRUE)
			   ,3 => array('value' => FALSE)
            ),
			
			'allow_add' => FALSE,
            'allow_edit' => FALSE,
            'allow_delete' => FALSE,
            'allow_filter' => FALSE,
            'allow_columns' => FALSE,
            'allow_page_size' => FALSE,
			
			'nested' => TRUE,
			'ajax' => TRUE,
			
		);
		
		$this->load->library('carbogrid', $params, 'grid2');
 
        if ($this->grid2->is_ajax)
        {
            $this->grid2->render();
            return FALSE;
        }		
 

        // Pass grid to the view     
        $data->grid1 = $this->grid1->render();
		$data->grid2 = $this->grid2->render();
 
    	$this->load->view('templates/header', $this->data);  
		$this->load->view('admin/record_management/waitlist_management', $data);
		$this->load->view('templates/footer');
    }

	/*
	 * This function takes data and takes on proper header information so emails dont come from corvall5
	 * $to - the email destination
	 * $subject - the email subject
	 * $body - the actuall body of the email
	 * if you want to change who emails are from in the system this is the function to change
	 */
	function sendEmail($data){
	
	$to = $data['to'];
	$subject = $data['subject'];
	$body = $data['body'];
		
	$headers   = array();
	$headers[] = "MIME-Version: 1.0";
	$headers[] = "Content-type: text/plain; charset=iso-8859-1";
	$headers[] = "From: From: \"Sarah Bingham\" <sarah@corvallismontessori.org>";
	$headers[] = "Reply-To: \"Sarah Bingham\" <sarah@corvallismontessori.org>";
	$headers[] = "Subject: {$subject}";
	$headers[] = "X-Mailer: PHP/".phpversion();
	
	mail($to, $subject, $body, implode("\r\n", $headers));
			
	}

}

/* End of file: dashboard.php */
/* Location: application/controllers/admin/dashboard.php */