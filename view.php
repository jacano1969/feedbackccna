<?php

require_once(dirname(__FILE__).'/extra.php');
require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(__FILE__).'/locallib.php');
require_once(dirname(__FILE__).'/mod_form.php');
require_once($CFG->dirroot.'/lib/accesslib.php');


$id = optional_param('id', 0, PARAM_INT);
$n  = optional_param('n', 0, PARAM_INT);

global $CFG;
global $USER;
global $DB;
global $string_from_view1;
global $string_from_view2;

$arr1 = array();
$arr10 = array();
$arr2 = array();
$arr20 = array();

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
add_to_log($course->id, 'feedbackccna', 'view', "view.php?id={$cm->id}", $feedbackccna->name, $cm->id);
$f_id = $feedbackccna->id;

$PAGE->set_url('/mod/feedbackccna/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($feedbackccna->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);


$list1 = get_role_users(STUDENT_ROLE, $context, true);
$list2 = get_role_users(STUDENT_ROLE, $context, true);

foreach ($list1 as $object1) {

    if (!get_user_answer_true($course->id, $object1->id, FEED_TYPE_PRE, $f_id)) {

        if (get_user_absent($course->id, $object1->id, FEED_TYPE_PRE, $f_id)) {
            $arr10[] = $object1->firstname.' '.$object1->lastname;
        } else {
            $arr1[] = $object1->firstname.' '.$object1->lastname;
        }

    }

}

foreach ($list2 as $object2) {

    if (!get_user_answer_true($course->id, $object2->id, FEED_TYPE_LAB, $f_id)) {

        if (get_user_absent($course->id, $object1->id, FEED_TYPE_PRE, $f_id)) {
            $arr20[] = $object1->firstname.' '.$object1->lastname;
        } else {
            $arr2[] = $object2->firstname.' '.$object2->lastname;
        }

    }

}

$string_from_view1 = implode('<br />', $arr1).
                     '<br /><br /><br /><strong>These are absent:</strong><br /><br />'.
                     implode('<br />', $arr10);

$string_from_view2 = implode('<br />', $arr2).
                     '<br /><br /><br /><strong>These are absent:</strong><br /><br />'.
                     implode('<br />', $arr20);

echo '<script type="text/javascript" src="prototype.js"></script>
      <script type="text/javascript" src="stars.js"></script>';

echo $OUTPUT->header();


if(has_capability('mod/feedbackccna:ratestudent', $context)) {
    build_tabs('view', $id, $n, $context);
}

$form = new add_view_form(null, array('id' => $id, 'n' => $n, 'courseid' => $course->id,
    'cm' => $cm, 'user_id' => $USER->id, 'f_id' => $feedbackccna->id));

if ($entry = $form->get_data() and confirm_sesskey($USER->sesskey)) {

    foreach ($new_array as $data) {

        $answer = 'value'.$data->id.$data->type;

        if (has_capability('mod/feedbackccna:rateteacher', $context)) {

            if (isset($entry->$answer) and $entry->$answer) {

                $values[$data->type] = $entry->$answer;
                insert_feedback_answer(
                    $data->id,
                    $USER->id,
                    $entry->$answer
                );

            }

        }

        if (has_capability('mod/feedbackccna:feedallow', $context)) {

            $check1 = 'check'.$data->id.'1';
            $uncheck1 = 'uncheck'.$data->id.'1';
            $check2 = 'check'.$data->id.'2';
            $uncheck2 = 'uncheck'.$data->id.'2';

            if (isset($entry->$check1) and $entry->$check1 == CHECKED) {
                set_allow_feedback($data->id, FEED_ALLOWED);
            } elseif (isset($entry->$uncheck1) and $entry->$uncheck1 == CHECKED) {
                set_allow_feedback($data->id, FEED_CLOSED);
            }
            if (isset($entry->$check2) and $entry->$check2 == CHECKED) {
                set_allow_feedback($data->id, FEED_ALLOWED);
            } elseif (isset($entry->$uncheck2) and $entry->$uncheck2 == CHECKED) {
                set_allow_feedback($data->id, FEED_CLOSED);
            }

        }

    }

    redirect($CFG->wwwroot.'/mod/feedbackccna/view.php?id='.$cm->id);

}

$form->display();

if ($_POST) {

    if (has_capability('mod/feedbackccna:rateteacher', $context)) {

        echo $OUTPUT->notification(get_string('feedback_sent', 'feedbackccna'), 'notifysuccess');

    } else {

        echo $OUTPUT->notification(get_string('feedback_allowed', 'feedbackccna'), 'notifysuccess');

    }

}

echo $OUTPUT->footer();

