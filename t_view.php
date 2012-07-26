<?php

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(__FILE__).'/locallib.php');
require_once(dirname(__FILE__).'/mod_form.php');
require_once($CFG->dirroot.'/lib/accesslib.php');

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // feedbackccna instance ID - it should be named as the first character of the module

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

$contextid = $context->id;
$courseid = $course->id;

add_to_log($course->id, 'feedbackccna', 't_view', "view.php?id={$cm->id}", $feedbackccna->name, $cm->id);

/// Print the page header

$PAGE->set_url('/mod/feedbackccna/t_view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($feedbackccna->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

echo '<script type="text/javascript" src="prototype.js"></script>
      <script type="text/javascript" src="stars.js"></script>';

// Output starts here

echo $OUTPUT->header();



if(has_capability('mod/feedbackccna:ratestudent', $context)) {

    build_tabs('t_view', $id, $n, $context);

    global $DB;
    global $USER;

    include 'participants.php';

    if (!empty($_POST) and confirm_sesskey($USER->sesskey)) {

        foreach ($bundle as $user_id) {

            $user = 'user'.$user_id;

            if (isset($_POST[$user])) echo 'isset '.$user_id.'<br/>';
            if ($_POST[$user] == "on") {echo 'ison '.$user_id.'<br/>';} else echo 'isoff '.$user_id.'<br/>';


            if (!isset($_POST[$user])) {

                $feed = 'Rating'.$user_id;
                $lab = 'lab'.$user_id;

                insert_feedback_answer(
                    $user_id,
                    1, //$feedback_id,
                    1, //$question_id,
                    $_POST[$feed]
                );

                if (isset($_POST[$lab])) {

                    insert_feedback_answer(
                        $user_id,
                        1, //$feedback_id,
                        1, //$question_id,
                        1
                    );

                } else {

                    insert_feedback_answer(
                        $user_id,
                        1, //$feedback_id,
                        1, //$question_id,
                        0
                    );

                }

            }

        }

        echo $OUTPUT->notification(get_string('feedback_sent', 'feedbackccna'), 'notifysuccess');

    }

} else {
    die('You are not allowed to see this page!');
}

// Finish the page
echo $OUTPUT->footer();

?>
