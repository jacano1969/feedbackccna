<?php

defined('MOODLE_INTERNAL') || die();

/* Deoarece deocamdata sunt planuite doar 2 tipuri de feedback pt studenti 
   si 2 tipuri pt profesori:

	feedback feedback_type = 1 pt studenti si 2 pt profesori
	ufo/tfo type = 1 pt prezentari si 2 pt laboratoare
*/
define('USER_FEEDBACK', 1);
define('TEACHER_FEEDBACK', 2);

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



function insert_user_feedback($user_id, $feedback_id,  $value) {
	global $DB;

	$record = new stdClass();
	
	$record->user_id = $user_id;
	$record->feedback_id = $feedback_id;
	$record->feedback_type = USER_FEEDBACK;
	$record->value = $value;

	$DB->insert_record('feedbackccna_feedback',$record, false);
}

function get_user_feedback($user_id, $week) {
	global $DB;

	return $DB->get_records_sql("SELECT * FROM {feedbackccna_feedback} WHERE feedback_type = " . USER_FEEDBACK ." AND user_id = ? AND feedback_id IN (SELECT id FROM {feedbackccna_ufo} WHERE week = ?) ", array($user_id, $week));
}


function insert_teacher_feedback($user_id, $feedback_id, $value) {
	global $DB;

	$record = new stdClass();
	
	$record->$user_id = $user_id;
	$record->feedback_id = $feedback_id;
	$record->feedback_type = TEACHER_FEEDBACK;
	$record->value = $value;

	$DB->insert_record('feedbackccna_feedback', $record, false);
}

function get_teacher_feedback($user_id, $week) {
	global $DB;

	return $DB->get_records_sql("SELECT * FROM {feedbackccna_feedback} WHERE feedback_type = " . TEACHER_FEEDBACK . " AND user_id = ? AND feedback_id IN (SELECT id FROM {feedbackccna_tfo} WHERE week = ?) ", array($user_id, $week));
}


function get_ufos_feedback($week, $type) {
	global $DB;

	return $DB->get_records_sql("SELECT * FROM {feedbackccna_feedback} WHERE feedback_type = " . USER_FEEDBACK . " AND feedback_id IN (SELECT id FROM {feedbackccna_ufo} WHERE week = ?  AND type = ?)", array($week, $type));
}

function set_allow_ufo_feedback($week, $type, $value) {
	global $DB;

	$DB->set_field('feedbackccna_tfo','allow', $value,array("week"=>$week, "type"=>$type));
}
