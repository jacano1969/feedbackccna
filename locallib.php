<?php

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");
require_once($CFG->dirroot.'/lib/accesslib.php');
require_once(dirname(__FILE__).'/db_functions.php');

function build_tabs($active, $id, $n, $context) {

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

           	$context = get_context_instance(CONTEXT_MODULE, $this->_customdata['cm']->id);

                global $new_array;
                global $array_q;

                $new_array = get_feedback_ccna_objects_teacher($this->_customdata['id'], ($this->_customdata['cm']->section));
                $array_q = get_questions_for_teachers();

print_r($new_array);
print_r($array_q);

                $nothing = 1;
		$something = 0;

                // numarul de tipuri este considerat hard-coded = 2 in view.php
		foreach ($new_array as $data) {

                    if ($data->type == '1') {

                        foreach ($array_q as $question) {

                            if ($question->type == $data->type) {

                                print($question->name);

		                $mform->addElement('hidden', 'qid'.$data->id, $question->id);

                                if (has_capability('mod/feedbackccna:rateteacher', $context)) {

                                    if ($data->allow == '1' || hascapability('mod/feedbackccna:feededit', $context)) {

                                        $nothing = 0;

                                        $mform->addElement('header', 'editorheader', get_string('headerlabel_presentation', 'feedbackccna'));
                                        $mform->addElement('html', "<script type='text/javascript'>
																		var s1 = new Stars({ 
																					maxRating: 5,
																					imagePath: 'images/',
																					value: 3
																					});
																	</script>");


                                        $mform->addElement('select', 'value'.$data->id, get_string('feedback_values', 'feedbackccna'),
                                                array('1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5'));
                                    }
                                }

                                if (has_capability('mod/feedbackccna:feedallow', $context)) {
                                    $mform->addElement('advcheckbox', 'check'.$data->id, get_string('checkbox', 'feedbackccna'), null,
                                                            null, array(0, 1));
                                }

                                print_container_start(false, 'singlebutton');
                                $this->add_action_buttons(false, get_string('submitlabel', 'feedbackccna'));
                                print_container_end();

                                $something = 1;
                                break;

                            }

                        }

                    } elseif ($data->type == '2') {

                        foreach ($array_q as $question) {

                            if ($question->type == $data->type) {

                                print($question->name);

		                $mform->addElement('hidden', 'qid'.$data->id, $question->id);

                                if (has_capability('mod/feedbackccna:rateteacher', $context)) {
                                    if ($data->allow == '1' || hascapability('mod/feedbackccna:feededit', $context)) {
                                        $nothing = 0;

                                        $mform->addElement('header', 'editorheader', get_string('headerlabel_lab', 'feedbackccna'));
                                        $mform->addElement('html', "<script type='text/javascript'>
                                                                        var s1 = new Stars({ 
                                                                                    maxRating: 5,
                                                                                    imagePath: 'images/',
                                                                                    value: 3
                                                                                    });
                                                                    </script>");

                                        $mform->addElement('select', 'value'.$data->id, get_string('feedback_values', 'feedbackccna'),
                                                array('1' => '1', '2' => '2', '3' => '3', '4' => '4', '5' => '5'));

                                    }
                                }

                                if (has_capability('mod/feedbackccna:feedallow', $context)) {
                                    $mform->addElement('advcheckbox', 'check'.$data->id, get_string('checkbox', 'feedbackccna'), null,
                                                            null, array(0, 1));
                                }

                                print_container_start(false, 'singlebutton');
                                $this->add_action_buttons(false, get_string('submitlabel', 'feedbackccna'));
                                print_container_end();

                                $something = 1;
                                break;

                            }

                        }

                    }
		}
echo '2';
		if (!$something) {

			if($nothing) {

				$mform->addElement('header', 'editorheader', get_string('headerlabel_nothing', 'feedbackccna'));

			} else {

				print_error('Feedback category is non-existent!');

			}

                }
	}

}
