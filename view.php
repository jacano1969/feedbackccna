<?php

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(__FILE__).'/locallib.php');
require_once(dirname(__FILE__).'/mod_form.php');
require_once($CFG->dirroot.'/lib/accesslib.php');


$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // feedbackccna instance ID - it should be named as the first character of the module

global $DB;

if ($id) {
    $cm         = get_coursemodule_from_id('feedbackccna', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $feedbackccna  = $DB->get_record('feedbackccna', array('id' => $cm->instance), '*', MUST_EXIST);
} elseif ($n) {
    $feedbackccna  = $DB->get_record('feedbackccna', array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $feedbackccna->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('feedbackccna', $feedbackccna->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);

$context = get_context_instance(CONTEXT_MODULE, $cm->id);

add_to_log($course->id, 'feedbackccna', 'view', "view.php?id={$cm->id}", $feedbackccna->name, $cm->id);

/// Print the page header

$PAGE->set_url('/mod/feedbackccna/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($feedbackccna->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);


// Output starts here
echo $OUTPUT->header();


if(has_capability('mod/feedbackccna:ratestudent', $context)) {
    build_tabs('view', $id, $n, $context);
}

global $USER;


//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

$form = new add_view_form(null, array('id' => $id, 'n' => $n, 'courseid' => $course->id, 'cm' => $cm));
$entry = $form->get_data();

if (!empty($entry) and confirm_sesskey()) {

    $db_entry = new stdClass();

    foreach ($new_array as $data) {

        $db_entry->('feedback_value'.$data->id) = $entry->('value'.$data->id);
        $db_entry->('allow'.$data->id) = $entry->('check'.$data->id);

        insert_feedback_answer($USER->id, $data->id, , $value);

    }

	echo $OUTPUT->notification(get_string('feedback_sent', 'feedbackccna'), 'notifysuccess');

}

$form->display();

/*
$new_array = get_tfos_feedback($cm->section);
foreach ($data as $new_array) {
	if ($data->type == '1') {
		$my_feedback_id = $data->id;
		break;
	}
}
*/


//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!


// Finish the page
echo $OUTPUT->footer();

?>
