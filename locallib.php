<?php

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");

function build_tabs($active, $id = '', $n = '') {

	global $CFG;

	if ($id) {
		$param1 = 'id';
		$param2 = $id;
	} else {
		$param1 = 'n';
		$param2 = $n;
	}

	$options = array();
	$inactive = array();
	$activetwo = array();
	$currenttab = $active;

	
	$options[] = new tabobject('view',
		$CFG->wwwroot . '/mod/feedbackccna/view.php?' . $param1 . '=' . $param2,
		get_string('view', 'feedbackccna'),
		get_string('viewdesc', 'feedbackccna'), true);

	$options[] = new tabobject('add',
		$CFG->wwwroot . '/mod/feedbackccna/add.php?' . $param1 . '=' . $param2,
		get_string('add', 'feedbackccna'),
		get_string('adddesc', 'feedbackccna'), true);

	$options[] = new tabobject('t_view',
		$CFG->wwwroot . '/mod/feedbackccna/t_view.php?' . $param1 . '=' . $param2,
		get_string('t_view', 'feedbackccna'),
		get_string('t_viewdesc', 'feedbackccna'), true);

	$tabs = array($options);

	print_tabs($tabs, $currenttab, $inactive, $activetwo);

}

class add_view_form extends moodleform {

	function definition() {

		global $CFG;
		global $usr;

		$mform =& $this->_form;

		$mform->addElement('hidden', 'action');
		$mform->setType('action', PARAM_TEXT);

		$mform->addElement('hidden', 'id', $this->_customdata['id']);
		$mform->setType('id', PARAM_INT);

		$mform->addElement('header', 'editorheader',
				get_string('headerlabel', 'feedbackccna'));

		$mform->addElement('select', 'value', get_string('feedback_values', 'feedbackccna'), 
				array('1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5'));
		
		//$mform->setType('name', PARAM_TEXT);
		//$mform->addrule('name', null, 'required', null, 'client');

		//$mform->addElement('textarea', 'desc', get_string('desclabel', 'feedbackccna'), 
		//		array('rows' => 3, 'cols' => 45));
		//$mform->setType('desc', PARAM_TEXT);

		print_container_start(false, 'singlebutton'); 
		$this->add_action_buttons(false, get_string('submitlabel', 'feedbackccna')); 
		print_container_end();

	}

}


class add_add_form extends moodleform {

	function definition() {

		//bla

	}

}


class add_t_view_form extends moodleform {

	function definition() {

		//bla

	}

}

function insert_feedback($user_id, $course_id, $week, $role, $type, $rating) {
	global $DB;
	$record = new stdClass();

	$record->user_id	= $user_id;
	$record->course_id  = $course_id;
	$record->week 		= $week;
	$record->role 		= $role;
	$record->type 		= $type;
	$record->rating 	= $rating;
	

	$DB->insert_records('feedbackccna_data',$record);
}

function get_week_feedback_for_user($user_id, $course_id, $week, $role, $type) {
	global $DB;
	
	return $DB->get_records_sql("SELECT type, value FROM {feedbackccna_data} WHERE 
			user_id 	= $user_id AND
			course_id	= $course_id AND
			week 		= $week AND
			role		= $role AND
			type 		= $type");
}

function get_week_feedback_for_teacher($course_id, $week, $type, $role) {
	global $DB;

	return $DB->get_records_sql("SELECT type, value FROM {feedbackccna_data} WHERE
			course_id	= $course_id AND
			week 		= $week AND
			role 		= $role AND
			type		= $type");
}

?>


