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
echo '<script type="text/javascript" src="prototype.js"></script>
	  <script type="text/javascript" src="stars.js"></script>';

// Output starts here
echo $OUTPUT->header();


if(has_capability('mod/feedbackccna:ratestudent', $context)) {
    build_tabs('view', $id, $n, $context);
}

global $USER;


$form = new add_view_form(null, array('id' => $id, 'n' => $n, 'courseid' => $course->id, 'cm' => $cm));
$entry = $form->get_data();


if (!empty($entry) and confirm_sesskey($USER->sesskey)) {

    foreach ($new_array as $data) {

        if (has_capability('mod/feedbackccna:rateteacher', $context)) {

            $answer = 'value'.$data->id.$data->type;

                insert_feedback_answer(
                    $data->id,
                    $USER->id,
                    $entry->$answer
                );

        }

        if (has_capability('mod/feedbackccna:feedallow', $context)) {

            $check1 = 'check'.$data->id.'1';
            $check2 = 'check'.$data->id.'2';

            if (isset($entry->$check1) and $entry->$check1 == '1') {
                set_allow_feedback($data->id, FEEDBACK_ALLOWED);
            }
            if (isset($entry->$check2) and $entry->$check2 == '1') {
                set_allow_feedback($data->id, FEEDBACK_ALLOWED);
            }

        }

    }

    echo $OUTPUT->notification(get_string('feedback_sent', 'feedbackccna'), 'notifysuccess');

}

$form->display();


// Finish the page
echo $OUTPUT->footer();

?>
