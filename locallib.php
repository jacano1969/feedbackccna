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
/*
	$options[] = new tabobject('add',
		$CFG->wwwroot . '/mod/feedbackccna/add.php?' . $param1 . '=' . $param2,
		get_string('add', 'feedbackccna'),
		get_string('adddesc', 'feedbackccna'), true);
*/
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
