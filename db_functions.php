<?php

defined('MOODLE_INTERNAL') || die();

// 0/1 nu se poate/se poate raspunde la feedback
define('FEEDBACK_ALLOWED', 1); // valoare care permite feedback
define('FEEDBACK_NOT_ALLOWED', 0); // valoare care nu permite feedback
define('DEFAULT_FEEDBACK_ALLOWED', FEEDBACK_NOT_ALLOWED); // valoarea implicita la adaugarea in DB
// define-uri pt which_way
// feedback-ul se da de profesor pentru elev
define('TEACHER_FOR_STUDENT', 1);
// feedback-ul se da de elev pentru profesor
define('STUDENT_FOR_TEACHER', 2);

// functie de inserat modul saptamanal/per topic
function insert_feedback_module($course_id, $section_id) {
	global $DB;

	$record = new stdClass();
	$record->course_id = $course_id;
	$record->section_id = $section_id;

	$DB->insert_record("feedbackccna_module",$record);
}

// functie de inserat obiect de feedback (deocamdata prezentare/laborator)
function insert_feedback_object($type, $name, $instructor_id, $module_id) {
	global $DB;

	$record = new stdClass();
	$record->type = $type;
	$record->name = $name;
	$record->instructor_id = $instructor_id;
	$record->module_id = $module_id;
	$record->allow = DEFAULT_FEEDBACK_ALLOWED;

	$DB->insert_record("feedbackccna_feedback",$record);
}

// functie de inserat intrebari
function insert_feedback_question($name, $type) {
	global $DB;
	
	$record = new stdClass();
	$record->name = $name;
	$record->type = $type;
	$record->which_way = $which_way;

	$DB->insert_record("feedbackccna_questions", $record);
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
}

// functie de obtinut obiectele (laboratoare/prezenari) de feedback pt profesor
function get_feedback_ccna_objects_teacher($course_id, $section) {
	global $DB;
	
	return $DB->get_records_sql("SELECT * FROM {feedbackccna_feedback} WHERE module_id IN (SELECT id FROM {feedbackccna_module} WHERE course_id = ? AND section = ?)", array($course_id, $section));
}

// functie de obtinut obiectele (laboratoare/prezenari) de feedback pt student
function get_feedback_ccna_objects_student($course_id, $section) {
	global $DB;
	
	return $DB->get_records_sql("SELECT * FROM {feedbackccna_feedback} WHERE allow = ".FEEDBACK_ALLOWED." AND module_id IN (SELECT id FROM {feedbackccna_module} WHERE course_id = ? AND section = ?)", array($course_id, $section));
}

// functie care obtine intrebarile la care trebuie sa raspunda studenti
function get_questions_for_students() {
	global $DB;
	
	return $DB->get_records("feedbackccna_questions", array('which_way'=> STUDENT_FOR_TEACHER));
}

// functie care obtine intrebarile la care trebuie sa raspunda profesori
function get_questions_for_teachers() {
	global $DB;
	
	return $DB->get_records("feedbackccna_questions", array('which_way'=> TEACHER_FOR_STUDENT));
}

// functie care obtine nr de feedbackuri date la un obiect de feedback
function get_responses_count($course_id, $section) {
	global $DB;

	return $DB->count_records_sql("SELECT feedback_id, COUNT(*)  FROM {feedbackccna_answer} WHERE question_id IN (SELECT id FROM {feedbackccna_questions} WHERE which_way = '".STUDENT_FOR_TEACHER."') AND feedback_id IN (SELECT id FROM {feedbackccna_feedback} WHERE module_id IN (SELECT id FROM feedbackccna_module WHERE course_id =". $course_id." AND section = ".$section.")) GROUP BY feedback_id", array $params=null);
}

//functie de modificat starea unui feedback
function set_feedback_allow($id, $allow) {
	global $DB;

	$record = new stdClass();
	$record->id = $id;
	$record->allow = $allow;
	$DB->update_record("feedbackccna_feedback",$record );
}

// functie care verifica daca un student fost absent verificand 
// daca a dat feedback
function student_present($student_id, $course_id, $section) {
	global $DB;
	
	return $DB->count_records_sql("SELECT * FROM {feedbackccna_answer} WHERE student_id =".$student_id." AND feedback_id IN (SELECT id FROM {feedbackccna_feedback} WHERE module_id IN (SELECT id FROM {feedbackccna_module} WHERE course_id=".$course_id." AND section=".$section.")) AND question_id IN (SELECT id FROM {feedbackccna_questions} WHERE which_way=".STUDENT_FOR_TEACHER.")") > 0
}
?>
