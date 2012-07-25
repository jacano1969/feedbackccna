<?php

defined('MOODLE_INTERNAL') || die();

// 0/1 nu se poate/se poate raspunde la feedback imediat ce este adaugat
define('DEFAULT_ALLOW_FEEDBACK', 0);
// define-uri pt which_way
// feedback-ul este dat de profesor pentru elev
define('TEACHER_FOR_STUDENT', 1);
// feedback-ul este dat de elev pentru profesor
define('STUDENT_FOR_TEACHER', 2);

// functie de inserat modul saptamanal/per topic
function insert_feedback_module($course_id, $section_id) {
	global $DB;

	$record = new stdClass();
	$record->course_id = $course_id;
	$record->section_id = $section_id;

	$DB->insert_record("feedback_module",$record);
}

// functie de inserat obiect de feedback (deocamdata prezentare/laborator)
function insert_feedback_object($type, $name, $instructor_id, $module_id) {
	global $DB;

	$record = new stdClass();
	$record->type = $type;
	$record->name = $name;
	$record->instructor_id = $instructor_id;
	$record->module_id = $module_id;
	$record->allow = DEFAULT_ALLOW_FEEDBACK;

	$DB->insert_record("feedback_ccna",$record);
}

// functie de inserat intrebari
function insert_feedback_question($name, $type) {
	global $DB;
	
	$record = new stdClass();
	$record->name = $name;
	$record->type = $type;

	$DB->insert_record("questions", $record);
}

// functie de inserat raspunsul
function insert_feedback_answer($student_id, $feedback_id, $question_id, $answer,
$which_way) {
	global $DB;

	$record =  new stdClass();
	$record->student_id = $student_id;
	$record->feedback_id = $feedback_id;
	$record->question_id = $question_id;
	$record->answer = $answer;
	$record->which_way = $which_way;
}
