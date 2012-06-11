<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Admissions extends Application {

	private static $globalViewData = array();

	function __construct() {
		parent::__construct();

		/* restrict access to all but admin */
		$this->ag_auth->restrict('parent');
		
		/* Disable Cashcing */
		$this->output->nocache();

		# Load Helpers
		$this->load->helper(array('url', 'form', 'dashboard', 'ag_auth', 'menu', 'notification'));
		
		# setup default view data
		$this->globalViewData['title'] = 'Admissions Dashboard';
		$this->globalViewData['MenuItems'] = get_menu_items();		// no argument means use current group
	}

	# The admissions controller has no default method. Thus, the index method's job here
	# is to direct the user elsewhere.
	function index() {
		// let the login controller decide our fate
		redirect('login');
	}

	# view the Montessori policy and statements of value
	function policy() {
		$this->load->view('templates/css');	
		$this->load->view('admissions/forms/policy');
	}

	# Manages the waitlist_questionaire. Handles displaying the
	# questionaire, validating the questionaire, and saving the form.
	function waitlistQuestionaire() {
		try{
			// get all enabled questions
			$wlQuestions = Waitlist_question::find_all_by_enabled(1);
	
			// get current school year info, so we can get only current programs
			$schoolInfo = School_information::find_by_currentyear(1);
			if($schoolInfo == null)
				throw new Exception('The system could not determine the current academic year.Please contact the administration for assistance.');
	
			// get academic levels, eagerload the programs (they must be filered in the view)
			$academicLevels = Academic_level::find_all_by_enabled(1, array('order' => 'academiclevelid asc',
																		   'include' => array('programs')));
		}
		catch(ActiveRecord\ActiveRecordException $e){
			// TODO: handle gracefully
			throw $e;
		}
		catch(Exception $e){
			// TODO: handle gracefully
			printf($e->getMessage());
		}

		// send these questions and programs to the view for display
		$viewData['wlQuestions'] = $wlQuestions;
		$viewData['academicLevels'] = $academicLevels;
		$viewData['schoolInformation'] = $schoolInfo;

		# Set up validation for admissionsPage1.php
		$this->validateWaitlistQuestionaire($wlQuestions);

		// if user is posting back answers, then save the form
		if ($this->form_validation->run() == TRUE) {
			// get answers from waitlist questionaire
			$this->storeWaitListForm($wlQuestions);

			// let the login controller decide our fate
			redirect('login');
		} else {
			// display the waitlist questionaire
			$this->load->view('templates/header', $this->globalViewData);
			$this->load->view('templates/fancybox_dependencies');
			$this->load->view('admissions/forms/waitlist_questionaire', $viewData);
			$this->load->view('templates/footer');
		}
	}

	# Displays the list of all waitlisted students for the current user.
	# 	That is, the waitlisted students who were waitlisted by the current
	# 	user AND who have been approved for registration.
	function registerStudentSelector() {
		$preenrolled = Waitlist_form::all(array('conditions' => array('UserID=? AND IsPreEnrolled=1', user_id())
											  , 'joins' => array('user')
											  , 'include' => array('student')));

		$waitlisted = Waitlist_form::all(array('conditions' => array('UserID=? AND IsPreEnrolled=0', user_id()), 'joins' => array('user')));

		// filter out students who are already represented in the Student table
		$preenrolled = array_filter($preenrolled, function($preenrolled){
			$stud = $preenrolled->student;
			return $stud ? 0 : 1;
		});
		
		$viewData['preEnStudents'] = $preenrolled;
		$viewData['wlStudents'] = $waitlisted;
		
		$this->load->view('templates/header', $this->globalViewData);
		$this->load->view('admissions/forms/register_student_selection', $viewData);
		$this->load->view('templates/footer');
	}

	# Manages the register_student form. Handles displaying the
	# form, validating the form, and saving the form.
	# We are registering the student represented by the given
	# waitlist ID, wlid.
	function registerStudent($wlid='0') {
		
		// verify that this student belongs to this user and does not yet have a completed registration form
		$wlStud = Waitlist_form::find(array('conditions' => array('FormID=? AND UserID=? AND IsPreEnrolled=1 AND IsWaitlisted=0', $wlid, user_id())));
		if ($wlStud == null || empty($wlStud)) {
			redirect('login');
		}

		// verify that this student hasn't filled out his admissions for yet.
		$student = Student::find_by_questionaireid($wlid);
		if ($student != null || !empty($student)){
			redirect('login');
		}

		// get current school year info, so we can get only current programs
		$schoolInfo = School_information::find_by_currentyear(1);
		if($schoolInfo == null)
			throw new Exception('The system could not determine the current academic year.Please contact the administration for assistance.');

		// get academic levels, eagerload the programs (they must be filered in the view)
		$academicLevels = Academic_level::find_all_by_enabled(1, array('order' => 'academiclevelid asc',
																	   'include' => array('programs')));
																	   
		// populate view data with child info and program info
		$viewData['firstName'] = $wlStud->firstname;
		$viewData['middleName'] = $wlStud->middlename;
		$viewData['lastName'] = $wlStud->lastname;
		$viewData['progSelected'] = $wlStud->expectedprogramid;
		$viewData['academicLevels'] = $academicLevels;
		$viewData['schoolInformation'] = $schoolInfo;

		# Validation for the student registration process
		$this -> validateRegistrationForm();
		if ($this->form_validation -> run() == FALSE) {
			$this->load->view('templates/header', $this->globalViewData);
			$this->load->view('admissions/forms/register_student', $viewData);
			$this->load->view('templates/footer');
		} else {
			$this->storeRegistrationForm($wlid);

			// Let the login controller decide our fate!!! MwaHaHaHa
			redirect('login');
		}
	}


	# Displays the list of all students that lack medical info belonging
	# to the current parent.
	function studentMedicalSelector() {
		// get students who aren't enrolled yet
		$students = Student::all(array('conditions' => array('UserID=? AND IsEnrolled=0', user_id()), 'include' => array('student_medical')));

		// filter out students who already have their medical forms filled out
		$students = array_filter($students, function($student){
			$medForm = $student->student_medical;
			return $medForm ? 0 : 1;
		});
		
		$viewData['Students'] = $students;

		$this->load->view('templates/header', $this->globalViewData);
		$this->load->view('admissions/forms/student_medical_selector', $viewData);
		$this->load->view('templates/footer');
	}

	function studentMedical($studentId) {

		// verify that this student belongs to this user
		$student = Student::find(array('conditions' => array('StudentID=? AND UserID=?', $studentId, user_id())));
		if ($student == null || empty($student)) {
			// redirect to login. The login controller decides where to go from there.
			redirect('login');
		}

		// verify that this student has no medical info
		$medInfo = Student_medical::find_by_studentid($studentId);
		if ($medInfo != null || !empty($medInfo)) {
			// redirect to login. The login controller decides where to go from there.
			redirect('login');
		}

		// populate view data with relevant child info
		$viewData['firstName'] = $student->firstname;
		$viewData['middleName'] = $student->middlename;
		$viewData['lastName'] = $student->lastname;

		// Validation for the student registration process
		$this->validateMedicalForm();
		if ($this->form_validation -> run() == FALSE) {
			$this->load->view('templates/header', $this->globalViewData);
			$this->load->view('admissions/forms/student_medical', $viewData);
			$this->load->view('templates/footer');
		} else {
			$this->storeMedicalForm($studentId);

			// display list of students who have yet to fill out their medical form
			redirect('login');
		}
	}

	# saves the completed Waitlist Questionaire
	function storeWaitlistForm($questions) {
		// save waitlist form to DB
		$wlForm = new Waitlist_form();
		$wlForm->userid = user_id();
		$wlForm->expectedprogramid = set_value('programChecked');
		$wlForm->firstname = set_value('cFirstName');
		$wlForm->middlename = set_value('cMiddleName');
		$wlForm->lastname = set_value('cLastName');
		$wlForm->agreement = set_value('pAgreement');
		$wlForm->ispreenrolled = 0;
		$wlForm->iswaitlisted = 1;
		$wlForm->submissiondttm = date('Y-m-d H:i:s', time());
		// Example: 2012-11-28 14:32:08
		$wlForm->save();

		// store each answer from the waitlist questionaire form
		$i = 0;
		foreach ($questions as $q) {
			$wlAnswer = new Waitlist_form_question();
			$wlAnswer->formid = $wlForm->formid;
			$wlAnswer->questionid = $q->questionid;
			$wlAnswer->answer = set_value('q' . $i . 'answer');
			$wlAnswer->save();

			$i++;
		}

		// UrlParam is empty string on waitlist notifications
		unsetNotification('waitlistAChild', user_id());
	}

	# saves the admissions form
	function storeRegistrationForm($wlid) {

		// make submission of multiple tables to database atomic.
		Admissions_form::transaction(function() use ($wlid) {

			// must save the student 2nd
			$student = new Student();
			$student->userid = user_id();
			$student->classid = null;
			// admin decides classroom later in the admissions process
			$student->programid = set_value('programChecked');
			$student->firstname = set_value('cFirstName');
			$student->middlename = set_value('cMiddleName');
			$student->lastname = set_value('cLastName');
			$student->gender = set_value('cGender');
			$student->address = set_value('cAddress');
			$student->placeofbirth = set_value('cBirthplace');
			$student->dob = date('Y-m-d H:i:s', strtotime(set_value('cDOB')));
			$student->phonenumber = set_value('cPhoneNum');
			$student->questionaireid = $wlid;
			$student->isenrolled = 0;
			$student->udttm = date('Y-m-d H:i:s', time());
			// Example: 2012-11-28 14:32:08
			$student->enrollmentdttm = null;
			$student->save();

			// must save 3 emergency contacts 1st
			$emergencyContact1 = new Emergency_contact();
			$emergencyContact1->studentid = $student->studentid;
			$emergencyContact1->ecname = set_value('emergencyContactName1');
			$emergencyContact1->ecphone = set_value('emergencyContactPhone1');
			$emergencyContact1->ecrelationship = set_value('emergencyContactRelationship1');
			$emergencyContact1->save();

			$emergencyContact2 = new Emergency_contact();
			$emergencyContact2->studentid = $student->studentid;
			$emergencyContact2->ecname = set_value('emergencyContactName2');
			$emergencyContact2->ecphone = set_value('emergencyContactPhone2');
			$emergencyContact2->ecrelationship = set_value('emergencyContactRelationship2');
			$emergencyContact2->save();

			$emergencyContact3 = new Emergency_contact();
			$emergencyContact3->studentid = $student->studentid;
			$emergencyContact3->ecname = set_value('emergencyContactName3');
			$emergencyContact3->ecphone = set_value('emergencyContactPhone3');
			$emergencyContact3->ecrelationship = set_value('emergencyContactRelationship3');
			$emergencyContact3->save();

			// save the Admissions_form last to complete the transaction
			$form = new Admissions_form();
			$form->studentid = $student->studentid;
			$form->schoolexperience = set_value('daycareExperience');
			$form->socialexperience = set_value('socialExperience');
			$form->comfortmethods = set_value('comfortMethod');
			$form->toilet = set_value('toiletNeeds');
			$form->naptime = set_value('napTime');
			$form->outdoorplay = set_value('playOutside');
			if (set_value('HasPets') == "1") {
				$form -> pets = set_value('petType') . " : " . set_value('petName');
			}
			$form->interests = set_value('childInterestsName');
			$form->siblingnames = set_value('siblingOneName');
			$form->siblingages = set_value('siblingOneAge');
			$form->referrertype = set_value('referenceType');
			$form->referredby = set_value('referenceName');
			$form->notes = set_value('otherImportantInfo');
			$form->save();
			
			// when a student is registered, unset the register notification and set the medical information notification
			unsetNotification('registerAChild', user_id(), $wlid);
			setNotification('medicalInformation', user_id(), $student->studentid, $student->firstname . ' ' . $student->lastname);
		});
	}

	# saves the medical form to the DB
	function storeMedicalForm($studentId) {
		
		// make submission of multiple tables to database atomic.
		Student::transaction(function() use ($studentId) {
		
			$student = Student::find_by_studentid($studentId);
			$studAttr = $student->attributes();
		
			// at this point, the student is no longer considered pre-enrolled
			$waitlistform = Waitlist_form::find_by_formid($studAttr['questionaireid']);
			$waitlistform->iswaitlisted = 0;
			$waitlistform->ispreenrolled = 0;
			$waitlistform->save();
			
			// This will save the information to the StudentMedicalInformation table.
			$student_medical = new Student_medical();
			$student_medical->studentid = $studentId;
			$student_medical->preferredhospital = set_value('preferredHospitalName');
			$student_medical->hospitalphone = set_value('hospitalPhoneName');
			$student_medical->physician = set_value('physicianName');
			$student_medical->physicianphone = set_value('pPhoneName');
			$student_medical->dentist = set_value('dentistName');
			$student_medical->dentistphone = set_value('dPhoneName');
			$student_medical->medicalconditions = set_value('medicalConditionsName');
			$student_medical->allergies = set_value('allergiesName');
			$student_medical->insurancecompany = set_value('insuranceCompanyName');
			$student_medical->certificatenumber = set_value('certificateNumberName');
			$student_medical->employer = set_value('employerName');
			$student_medical->save();
			
			// when a student is registered, unset the register medical info notification and
			// set the completed registration notification for the admin
			unsetNotification('medicalInformation', user_id(), $studentId);
			// on a registrationComplete notification, UserID is calculated by the notification helper
			setNotification('registrationComplete', null, $student->studentid, $student->firstname . ' ' . $student->lastname);
		});
	}

	# sets the validation rules
	function validateWaitlistQuestionaire($questions) {
		// validate name (don't require middle name)
		$this->form_validation->set_rules('cFirstName', 'Child\'s First Name', 'required|min_length[1]');
		$this->form_validation->set_rules('cMiddleName', 'Child\'s Middle Name', '');
		$this->form_validation->set_rules('cLastName', 'Child\'s Last Name', 'required|min_length[1]');

		// make sure a program was selected
		$this->form_validation->set_rules('programChecked', 'Program', 'required');

		// validate all questions on the form
		$i = 0;
		foreach ($questions as $q) {
			$this->form_validation->set_rules('q' . $i . 'answer', 'question#' . $i . '\'s answer', 'required|min_length[1]');
			$i++;
		}

		$this->form_validation->set_rules('pAgreement', 'Policy Agreement', 'required');
	}

	# Sets the validation rules for the Student Registration Form
	function validateRegistrationForm() {
		// validate name (don't require middle name)
		$this->form_validation->set_rules('cFirstName', 'Child\'s First Name', 'required|min_length[1]');
		$this->form_validation->set_rules('cMiddleName', 'Child\'s Middle Name', '');
		$this->form_validation->set_rules('cLastName', 'Child\'s Last Name', 'required|min_length[1]');

		// make sure a program was selected
		$this->form_validation->set_rules('programChecked', 'Program', 'required');

		// verify 3 emergency contacts
		$this->form_validation->set_rules('emergencyContactName1', 'Emergency Contact#1\'s Name', 'required|min_length[1]');
		$this->form_validation->set_rules('emergencyContactPhone1', 'Emergency Contact#1\'s Phone', 'required|min_length[1]');
		$this->form_validation->set_rules('emergencyContactRelationship1', 'Emergency Contact#1\'s Relationship to child', 'required|min_length[1]');
		$this->form_validation->set_rules('emergencyContactName2', 'Emergency Contact#2\'s Name', 'required|min_length[1]');
		$this->form_validation->set_rules('emergencyContactPhone2', 'Emergency Contact#2\'s Phone', 'required|min_length[1]');
		$this->form_validation->set_rules('emergencyContactRelationship2', 'Emergency Contact#2\'s Relationship to child', 'required|min_length[1]');
		$this->form_validation->set_rules('emergencyContactName3', 'Emergency Contact#3\'s Name', 'required|min_length[1]');
		$this->form_validation->set_rules('emergencyContactPhone3', 'Emergency Contact#3\'s Phone', 'required|min_length[1]');
		$this->form_validation->set_rules('emergencyContactRelationship3', 'Emergency Contact#3\'s Relationship to child', 'required|min_length[1]');

		$this->form_validation->set_rules('cAddress', 'Child\'s Address', 'required|min_length[1]');
		$this->form_validation->set_rules('cPhoneNum', 'Child\s Phone', 'required|min_length[1]');
		$this->form_validation->set_rules('cBirthplace', 'Child\s birthplace', 'required|min_length[2]');
		$this->form_validation->set_rules('cDOB', 'Date of Birth', 'required|min_length[4] ');
		$this->form_validation->set_rules('cGender', 'Gender', 'required ');
		$this->form_validation->set_rules('daycareExperience', 'Daycare Experiences', 'required|min_length[1]');
		$this->form_validation->set_rules('socialExperience', 'Social Experiences', 'required|min_length[1]');
		$this->form_validation->set_rules('comfortMethod', 'Comfort your child', 'required|min_length[1]');
		$this->form_validation->set_rules('toiletNeeds', 'Toilet Needs', 'required|min_length[1]');
		$this->form_validation->set_rules('napTime', 'Takes Naps', 'required|min_length[1]');
		$this->form_validation->set_rules('playOutside', 'Plays outside', 'required|min_length[1]');
		$this->form_validation->set_rules('HasPets', 'Has Pets', 'required');
		$this->form_validation->set_rules('childInterestsName', 'Child\'s Interests', 'required|min_length[1]');
		$this->form_validation->set_rules('siblingOneName', 'Silbing\'s first name', 'alpha');
		$this->form_validation->set_rules('siblingOneAge', 'Silbing\'s age', 'is_natural_no_zero');
		$this->form_validation->set_rules('otherImportantInfo', 'Other Important Information', 'required|min_length[1]');
		$this->form_validation->set_rules('referenceType', 'Heard about us', 'required|min_length[1]');

		// should not be validated
		$this->form_validation->set_rules('referenceName', 'Learned About us', '');
		$this->form_validation->set_rules('petType', 'Type of Pet', '');
		$this->form_validation->set_rules('petName', 'Name of Pet', '');
	}

	# sets the validation rules for the MedicalInformationForm
	function validateMedicalForm() {
		$this->form_validation->set_rules('preferredHospitalName', 'Preferred Hospital', 'required');
		$this->form_validation->set_rules('hospitalPhoneName', 'Hospital\'s phone number', 'required|min_length[12]');
		$this->form_validation->set_rules('physicianName', 'Physician', 'required');
		$this->form_validation->set_rules('pPhoneName', 'Physician\'s Phone', 'required|min_length[12]');
		$this->form_validation->set_rules('dentistName', 'Dentist', 'required');
		$this->form_validation->set_rules('dPhoneName', 'Dentist\'s Phone', 'required|min_length[12]');
		$this->form_validation->set_rules('medicalConditionsName', 'Medical Conditions', '');
		$this->form_validation->set_rules('allergiesName', 'Allergies', '');
		$this->form_validation->set_rules('insuranceCompanyName', 'Insurance Company', '');
		$this->form_validation->set_rules('certificateNumberName', 'Insurance Certification Number', '');
		$this->form_validation->set_rules('employerName', 'Employer', '');
	}
	
	function stepTwoInfo(){
		$this->load->view('templates/header', $this->globalViewData);
		$this->load->view('admissions/step_two');
		$this->load->view('templates/footer');
	}
	
		function adminApproval(){
		$this->load->view('templates/header', $this->globalViewData);
		$this->load->view('admissions/admin_approval');
		$this->load->view('templates/footer');
	}

}
