<?php
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(__FILE__).'/locallib.php');
require_once(dirname(__FILE__).'/mod_form.php');
require_once(dirname(__FILE__).'/db_functions.php');
require_once($CFG->dirroot.'/lib/accesslib.php');

$id = optional_param('id', 0, PARAM_INT); // course_module ID, or
$n  = optional_param('n', 0, PARAM_INT);  // feedbackccna instance ID - it should be named as the first character of the module

//global $cm;

if ($id) {
    $cm         = get_coursemodule_from_id('feedbackccna', $id, 0, false, MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $feedbackccna  = $DB->get_record('feedbackccna', array('id' => $cm->instance), '*', MUST_EXIST);
} elseif ($n) {
    $feedbackccna  = $DB->get_record('feedbackccna', array('id' => $n), '*', MUST_EXIST);
    $course     = $DB->get_record('course', array('id' => $feedbackccna->course), '*', MUST_EXIST);
    $cm         = get_coursemodule_from_instance('feedbackccna', $feedbackccna->id, $course->id, false, MUST_EXIST);
} else {
    print_error('You must specify a course_module ID or an instance ID');
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


echo $OUTPUT->header();

if(has_capability('mod/feedbackccna:ratestudent', $context)) {

    global $bla_array;
    $bla_array = get_feedback_module_teacher($courseid, $cm->section, 1);

    build_tabs('t_view', $id, $n, $context);

    global $DB;
    global $USER;


    foreach ($bla_array as $t_module) {

        require_once('participants.php');

        if (!empty($_POST) and confirm_sesskey($USER->sesskey)) {

            foreach ($bundle as $user_id) {

                $old_id_1 = get_feedback_answer_id($courseid, $user_id, $cm->section, 1, 1);
                $old_id_2 = get_feedback_answer_id($courseid, $user_id, $cm->section, 1, 2);

                $user = 'user'.$user_id;

                if (!isset($_POST[$user])) {

                    $feed = 'Rating'.$user_id;
                    $lab = 'lab'.$user_id;

                    if($t_module->type == 1) {

                        if ($old_id_1) {

                            update_feedback_answer(
                                $old_id_1,
                                $t_module->id,
                                $user_id,
                                $_POST[$feed]
                            );

                        } else {

                            insert_feedback_answer(
                                $t_module->id,
                                $user_id,
                                $_POST[$feed]
                            );

                        }

                    } elseif ($t_module->type == 2) {

                        $lab_full = isset($_POST[$lab]);

                        if ($old_id_2) {

                            update_feedback_answer(
                                $old_id_2,
                                $t_module->id,
                                $user_id,
                                $lab_full
                            );

                        } else {

                            insert_feedback_answer(
                                $t_module->id,
                                $user_id,
                                $lab_full
                            );

                        }

                    }

                } else {

                    if($t_module->type == 1) {

                        if ($old_id_1) {

                            delete_feedback_answer($old_id_1);

                        }

                    } elseif ($t_module->type == 2) {

                        if ($old_id_2) {

                            delete_feedback_answer($old_id_2);

                        }

                    }

                }

            }

        }

    }

    if ($_POST) {

        go($cm->id);
        echo $OUTPUT->notification(get_string('feedback_sent', 'feedbackccna'), 'notifysuccess');

    }

} else {
    die('You are not allowed to see this page!');
}

// Finish the page
echo $OUTPUT->footer();

function go($cm_id) {

    global $CFG;
    redirect($CFG->wwwroot.'/mod/feedbackccna/t_view.php?id='.$cm_id);

}

?>
