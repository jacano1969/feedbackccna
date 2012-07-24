<?php

defined('MOODLE_INTERNAL') || die();

define('USER_FEEDBACK', 1);
define('TEACHER_FEEDBACK', 2);
define('ALLOW_FEEDBACK', 0);

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

		$new_array = get_tfos_feedback(($this->_customdata['cm']->section) - 1);

		$nothing = 1;
		$something = 0;

		foreach ($new_array as $data) {
			if ($data->allow == '1') {
				$nothing = 0;

				if ($data->type == '1' and $data->allow == '1') {
					$mform->addElement('header', 'editorheader', get_string('headerlabel_presentation', 'feedbackccna'));

					$mform->addElement('select', 'value', get_string('feedback_values', 'feedbackccna'), 
						array('1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5'));
			
					print_container_start(false, 'singlebutton'); 
					$this->add_action_buttons(false, get_string('submitlabel', 'feedbackccna')); 
					print_container_end();

					$something = 1;
					break;

				} elseif ($data->type == '2' and $data->allow == '1') {
					$mform->addElement('header', 'editorheader', get_string('headerlabel_lab', 'feedbackccna'));

					$mform->addElement('select', 'value', get_string('feedback_values', 'feedbackccna'), 
						array('1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5'));
			
					print_container_start(false, 'singlebutton'); 
					$this->add_action_buttons(false, get_string('submitlabel', 'feedbackccna')); 
					print_container_end();

					$something = 1;
					break;

				}
			}
		}

		if (!$something) {

			if($nothing) {

				$mform->addElement('header', 'editorheader', get_string('headerlabel_nothing', 'feedbackccna'));

			} else {

				print_error('Feedback category is non-existent!');

			}

		}

	}

}


// adauga feedback-ul dat de un student in baza de date
function insert_user_feedback($user_id, $feedback_id,  $value) {
	global $DB;

	$record = new stdClass();
	$record->user_id = $user_id;
	$record->feedback_id = $feedback_id;
	$record->feedback_type = USER_FEEDBACK;
	$record->value = $value;

	$DB->insert_record('feedbackccna_feedback',$record, false);
}

//obtine feedback-ul obtinut de un student intr-o saptamana
function get_user_feedback($user_id, $course_id, $section) {
	global $DB;

	return $DB->get_records_sql("SELECT * FROM {feedbackccna_feedback} WHERE feedback_type = " . USER_FEEDBACK ." AND user_id = ? AND feedback_id IN (SELECT id FROM {feedbackccna_ufo} WHERE course_id = ? AND section = ?) ", array($user_id, $course_id, $section));
}

//adauga feedback-ul dat de un profesor unui student
function insert_teacher_given_feedback($user_id, $feedback_id, $value) {
	global $DB;

	$record = new stdClass();
	$record->$user_id = $user_id;
	$record->feedback_id = $feedback_id;
	$record->feedback_type = TEACHER_FEEDBACK;
	$record->value = $value;

	$DB->insert_record('feedbackccna_feedback', $record, false);
}

//obtine feedback-ul obtinut de un profesor intr-o saptamana
function get_teacher_feedback($course_id, $section) {
	global $DB;

	return $DB->get_records_sql("SELECT * FROM {feedbackccna_feedback} WHERE feedback_type = " . TEACHER_FEEDBACK . " AND feedback_id IN (SELECT id FROM {feedbackccna_tfo} WHERE section = ? AND course_id = ? AND allow = ) ", array( $section, $course_id));
}

//obtine toate obiectele la care studentul poate primi feedback
function get_ufos_feedback($section) {
	global $DB;

	return $DB->get_records_sql("SELECT * FROM {feedbackccna_ufo} WHERE section = ?", array($section));
}

//obtine toate obiectele la care profesorul poate primi feedback
function get_tfos_feedback($section) {
	global $DB;

	return $DB->get_records_sql("SELECT *  FROM {feedbackccna_tfo} WHERE section = ?", array($section));
}

// permite sau interzice un item de feedback care sa fie acordat profesorului
function set_allow_tfo_feedback($feedback_id, $section, $type, $value) {
	global $DB;

	$DB->set_field('feedbackccna_tfo','allow', $value, array("feedback_id"=>$feedback_id,"section"=>$section, "type"=>$type));
}

// inserare obiect feedback teacher
function insert_tfo_object($course_id, $section, $type) {
	global $DB;

	$record->allow = ALLOW_FEEDBACK;
	$record->course_id = $course_id;
	$record->section = $section;
	$record->type = $type;

	$DB->insert_record('feedbackccna_tfo', $record, false);
}

// inserare obiect feedback utilizator
function insert_ufo_object($course_id, $section, $type) {
    global $DB;

    $record->course_id = $course_id;
    $record->section = $section;
    $record->type = $type;

    $DB->insert_record('feedbackccna_ufo', $record, false);
}

global $hardc_type_tfo;
global $hardc_type_ufo;

$hardc_type_tfo = array( 1, 2); // Tip feedback profesor 1 = Prezentare, 2 = Laborator
$hardc_type_ufo = array( 1, 2); // Tip feedback student 1 = Prezentare, 2 = Laborator

// inserare obiecte feedback profesor
function mod_setup_insert_tfo_objects(stdClass $feedback, $fobjects_type) {
	foreach($fobjects_type as $type) {
		insert_tfo_object($feedback->course, $feedback->section, $type);
	}
}

// inserare obiecte feedback student 
function mod_setup_insert_ufo_objects(stdClass $feedback, $fobjects_type) {
	foreach($fobjects_type as $type) {
		insert_ufo_object($feedback->course, $feedback->section, $type);
	}
}
