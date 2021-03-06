<?php

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(__FILE__).'/locallib.php');
require_once(dirname(__FILE__).'/mod_form.php');
require_once(dirname(__FILE__).'/db_functions.php');
require_once($CFG->dirroot.'/lib/accesslib.php');

$id = optional_param('id', 0, PARAM_INT);
$n  = optional_param('n', 0, PARAM_INT);

global $f_id;
global $bla_array;
global $DB;
global $USER;
global $CFG;

if ($id) {
    $cm       = get_coursemodule_from_id('feedbackccna', $id, 0, false, MUST_EXIST);
    $course   = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $feedback = $DB->get_record('feedbackccna', array('id' => $cm->instance), '*', MUST_EXIST);
} elseif ($n) {
    $feedback = $DB->get_record('feedbackccna', array('id' => $n), '*', MUST_EXIST);
    $course   = $DB->get_record('course', array('id' => $feedback->course), '*', MUST_EXIST);
    $cm       = get_coursemodule_from_instance('feedbackccna', $f_id, $course->id, false, MUST_EXIST);
} else {
    print_error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);

$context = get_context_instance(CONTEXT_MODULE, $cm->id);

$contextid = $context->id;
$courseid = $course->id;
$f_id = $feedback->id;

add_to_log($course->id, 'feedbackccna', 't_view', "view.php?id={$cm->id}", $feedback->name, $cm->id);

$PAGE->set_url('/mod/feedbackccna/t_view.php', array('id' => $cm->id));
$PAGE->set_context($context);
$PAGE->set_title(format_string($feedback->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_pagelayout('standard');

echo $OUTPUT->header();


if (has_capability('mod/feedbackccna:ratestudent', $context)) {

    build_tabs('t_view', $id, $n, $context);
    require_once('participants.php');

    $bla_array = get_feedback_module_teacher($courseid, $cm->section, $f_id, TEACHER_FOR_STUDENT);

    foreach ($bla_array as $t_module) {

        if (!empty($_POST) and confirm_sesskey($USER->sesskey)) {

            foreach ($bundle as $user_id) {

                $old_id_1 = get_feedback_answer_id($courseid, $user_id, $cm->section, $f_id, TEACHER_FOR_STUDENT, FEED_TYPE_PRE);
                $old_id_2 = get_feedback_answer_id($courseid, $user_id, $cm->section, $f_id, TEACHER_FOR_STUDENT, FEED_TYPE_LAB);

                $user = 'user'.$user_id;

                if (!isset($_POST[$user])) {

                    $feed = 'Prez'.$user_id;
                    $lab = 'Lab'.$user_id;

                    if($t_module->type == FEED_TYPE_PRE) {

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

                    } elseif ($t_module->type == FEED_TYPE_LAB) {

                        if ($old_id_2) {

                            update_feedback_answer(
                                $old_id_2,
                                $t_module->id,
                                $user_id,
                                $_POST[$lab]
                            );

                        } else {

                            insert_feedback_answer(
                                $t_module->id,
                                $user_id,
                                $_POST[$lab]
                            );

                        }

                    }

                } else {

                    if($t_module->type == FEED_TYPE_PRE) {

                        if ($old_id_1 and !(get_user_answer_true($courseid, $user_id, FEED_TYPE_PRE, $f_id))) {

                            delete_feedback_answer($old_id_1);

                        }

                    } elseif ($t_module->type == FEED_TYPE_LAB) {

                        if ($old_id_2 and !(get_user_answer_true($courseid, $user_id, FEED_TYPE_LAB, $f_id))) {

                            delete_feedback_answer($old_id_2);

                        }

                    }

                }

            }

        }

    }

    if ($_POST) {

        redirect($CFG->wwwroot.'/mod/feedbackccna/t_view.php?id='.$cm->id);

    }

} else {

    die('You are not allowed to see this page!');

}

echo $OUTPUT->footer();

