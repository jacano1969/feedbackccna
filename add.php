<?php

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(__FILE__).'/locallib.php');
require_once(dirname(__FILE__).'/mod_form.php');

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

add_to_log($course->id, 'feedbackccna', 'view', "view.php?id={$cm->id}", $feedbackccna->name, $cm->id);

/// Print the page header

$PAGE->set_url('/mod/feedbackccna/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($feedbackccna->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);

// Output starts here
echo $OUTPUT->header();

build_tabs('add', $id, $n);


// Finish the page
echo $OUTPUT->footer();

echo 'Hello!';

?>

