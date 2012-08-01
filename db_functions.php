<?php

defined('MOODLE_INTERNAL') || die();

// 0/1 nu se poate/se poate raspunde la feedback
define('FEEDBACK_ALLOWED', 1); // valoare care permite feedback
define('FEEDBACK_NOT_ALLOWED', 0); // valoare care nu permite feedback
define('FEEDBACK_CLOSED', -1); // valoare care desemneaza faptul ca feedback-ul a fost inchis
define('DEFAULT_FEEDBACK_ALLOWED', FEEDBACK_NOT_ALLOWED); // valoarea implicita la adaugarea in DB
// define-uri pt which_way
// feedback-ul se da de profesor pentru elev
define('TEACHER_FOR_STUDENT', 1);
// feedback-ul se da de elev pentru profesor
define('STUDENT_FOR_TEACHER', 2);
define('FEEDBACK_TYPE_PRE', 1);
define('FEEDBACK_TYPE_LAB', 2);
define('FEEDBACK_STUDENT_LAB_NOT_DONE', 2);
define('FEEDBACK_STUDENT_LAB_DONE', 1);

//	functie care insereaza un modul de feedback
//	- instructor_id - id-ul instructorului care adauga modulul
//	- denumire - denumirea modulului
//	- section - sectiunea din curs unde se afla modulul
//	- course_id - id-ul cursului unde se afla modulul
//	- which_way - daca feedback-ul este dat de profesor studentului sau invers
function insert_feedback_module($instructor_id, $course_id, $section, $denumire, $which_way, $type) {
	global $DB;

	// inserez modulul de feedback cu drepturile care trebuie, pentru a fi afisat
	$allow = FEEDBACK_NOT_ALLOWED;
	if( $which_way == STUDENT_FOR_TEACHER) $allow = DEFAULT_FEEDBACK_ALLOWED;
	elseif( $which_way == TEACHER_FOR_STUDENT) $allow = FEEDBACK_ALLOWED;

	$record = new stdClass();
	$record->instructor_id = $instructor_id;
	$record->denumire = $denumire;
	$record->allow = $allow;
	$record->section = $section;
	$record->course_id = $course_id;
	$record->which_way = $which_way;
	$record->type = $type;

	$DB->insert_record("feedbackccna_module", $record);
}

function delete_feedback_module($course_id, $section) {
	global $DB;

	$modis = get_feedback_module_id($course_id, $section);
	foreach($modis as $id) {
		$DB->delete_records('feedbackccna_answer', array('module_id'=>$id->id));
		$DB->delete_records('feedbackccna_module', array('id'=>$id->id));
	}
}

//  functie de inserat automat intrare unui modul
function setup_feedback_module($feedback, $instructor_id) {

	insert_feedback_module($instructor_id, $feedback->course, $feedback->section, $feedback->name, STUDENT_FOR_TEACHER, FEEDBACK_TYPE_PRE);
	insert_feedback_module($instructor_id, $feedback->course, $feedback->section, $feedback->name, TEACHER_FOR_STUDENT, FEEDBACK_TYPE_PRE);
	insert_feedback_module($instructor_id, $feedback->course, $feedback->section, $feedback->name, STUDENT_FOR_TEACHER, FEEDBACK_TYPE_LAB);
	insert_feedback_module($instructor_id, $feedback->course, $feedback->section, $feedback->name, TEACHER_FOR_STUDENT, FEEDBACK_TYPE_LAB);

}

//	functie de inserare raspuns la intrebare
//	- module_id - id-ul modulului la care s-a raspuns
//	- student_id - id-ul studentului care a raspuns
//	- answer - valoarea efectiva a raspunsului
function insert_feedback_answer($module_id, $student_id, $answer) {
	global $DB;

	$record = new stdClass();
	$record->module_id = $module_id;
	$record->student_id = $student_id;
	$record->answer = $answer;

	$DB->insert_record("feedbackccna_answer",$record);
}

function update_feedback_answer($id, $module_id, $student_id, $answer) {
	global $DB;

        $record = new stdClass();
        $record->id = $id;
	$record->module_id = $module_id;
	$record->student_id = $student_id;
	$record->answer = $answer;

	$DB->update_record("feedbackccna_answer", $record);
}

function delete_feedback_answer($id) {
        global $DB;

        $record = array('id' => $id);

        $DB->delete_records("feedbackccna_answer", $record);
}

//	functie de schimbat valoarea campului allow din modulul de feedback
//	- module_id - id-ul modulului de modificat
//	- allow - noua valoare a campului allow
function set_allow_feedback($module_id, $allow) {
	global $DB;

	if($allow ==  FEEDBACK_ALLOWED || $allow == FEEDBACK_NOT_ALLOWED) {
		$DB->update_record("feedbackccna_module", array('id'=>$module_id, 'allow'=>$allow));
	}
}

//	functie de obtinut modulul de feedback dintr-un curs si o sectiune
//	- course_id -id-ul cursului
//	- section - saptamana sau topicul
//	- which_way - daca feedback-ul este dat de student pt profesor sau invers
function get_feedback_module($course_id, $section, $which_way) {
	global $DB;

	return $DB->get_records_sql("SELECT * FROM {feedbackccna_module} WHERE course_id = ? AND section = ? AND which_way = ? AND allow='".FEEDBACK_ALLOWED."'", array($course_id, $section, $which_way));
}

function get_feedback_module_id($course_id, $section) {
	global $DB;

	return $DB->get_records_sql("SELECT id FROM {feedbackccna_module} WHERE course_id = ? AND section = ?", array($course_id, $section));
}

// functie de hack - MihaiZ nu stie ce spune
function get_correct_section($section_id) {
	global $DB;

	return $DB->get_field_sql("SELECT section FROM {course_sections} WHERE id='".$section_id."'");
}

//	functie de obtinut modulul de feedback dintr-un curs si o sectiune
//	- course_id -id-ul cursului
//	- section - saptamana sau topicul
//	- which_way - daca feedback-ul este dat de student pt profesor sau invers
function get_feedback_module_teacher($course_id, $section, $which_way) {
	global $DB;

	$section = get_correct_section($section);
	return $DB->get_records_sql("SELECT * FROM {feedbackccna_module} WHERE course_id = ? AND section = ? AND which_way = ?", array($course_id, $section, $which_way));
}

//	functie de obtinut rating mediu pentru un curs pe tip de intrebare
//	- course_id - id-ul cursului
//	- type - tipul itemului pt care se doreste rezultatul
function average_course_rating_pertype($course_id, $type) {
	global $DB;

	return $DB->get_records_sql("SELECT AVG(a.answer) FROM {feedbackccna_answer} a  INNER JOIN {feedbackccna_module} m ON a.module_id = m.id WHERE m.course_id = ? AND m.which_way='".STUDENT_FOR_TEACHER."' AND m.type = ?", array($course_id, $type));
}

//	functie de obtinut rating mediu pentru un curs
//	- course_id - id-ul cursului
function average_course_rating($course_id) {
	global $DB;

	return $DB->get_records_sql("SELECT AVG(a.answer) FROM {feedbackccna_answer} a  INNER JOIN {feedbackccna_module} m ON a.module_id = m.id WHERE m.course_id = ? AND m.which_way='".STUDENT_FOR_TEACHER."'", array($course_id));
}

//	functie de obtinut rating mediu pentru un profesor pe fiecare tip de feedback
//	- instructor_id - id-ul instructorului
//	- type - tipul itemului pt care se doreste rezultatul
function average_instructor_rating_pertype($instructor_id, $type) {
	global $DB;

	return $DB->get_records_sql("SELECT AVG(a.answer) FROM {feedbackccna_answer} a  INNER JOIN {feedbackccna_module} m ON a.module_id = m.id WHERE m.instructor_id = ? AND m.which_way='".STUDENT_FOR_TEACHER."' AND m.type = ?", array($instructor_id, $type));
}

//	functie de obtinut rating mediu pentru un profesor
//	- instructor_id - id-ul instructorului
//	- type - tipul itemului pt care se doreste rezultatul
function average_instructor_rating($instructor_id) {
	global $DB;

	return $DB->get_records_sql("SELECT AVG(a.answer) FROM {feedbackccna_answer} a  INNER JOIN {feedbackccna_module} m ON a.module_id = m.id WHERE m.instructor_id = ? AND m.which_way='".STUDENT_FOR_TEACHER."'" , array($instructor_id));
}

//	functie de obtinut rating mediu pentru un profesor pe fiecare tip de feedback pe fiecare curs
//	- instructor_id - id-ul instructorului
//	- type - tipul itemului pt care se doreste rezultatul
//	- course_id - id-ul cursului
function average_instructor_rating_pertype_percourse($instructor_id, $type, $course_id) {
	global $DB;

	return $DB->get_records_sql("SELECT AVG(a.answer) FROM {feedbackccna_answer} a  INNER JOIN {feedbackccna_module} m ON a.module_id = m.id WHERE m.instructor_id = ? AND m.which_way='".STUDENT_FOR_TEACHER."' AND m.type = ? AND m.course_id = ?", array($instructor_id, $type, $course_id));
}

//	functie de obtinut rating mediu pentru un profesor pe fiecare curs
//	- instructor_id - id-ul instructorului
//	- course_id - tipul itemului pt care se doreste rezultatul
function average_instructor_rating_percourse($instructor_id, $course_id) {
	global $DB;

	return $DB->get_records_sql("SELECT AVG(a.answer) FROM {feedbackccna_answer} a  INNER JOIN {feedbackccna_module} m ON a.module_id = m.id WHERE m.instructor_id = ? AND m.which_way='".STUDENT_FOR_TEACHER."' AND m.course_id = ?", array($instructor_id, $course_id));
}

//  functie de obtinut valoare medie feedback student
//  - student_id
function average_rating_student($student_id) {
	global $DB;

	return $DB->get_records_sql("SELECT AVG(a.answer) FROM {feedbackccna_answer} a INNER JOIN {feedbackccna_module} m ON a.module_id = m_id WHERE  m.which_way='".TEACHER_FOR_STUDENT."' AND a.student_id = ?", array($student_id));
}

//  functie de obtinut valoare medie feedback student pe fiecare item
//  - student_id
//  - type
function average_rating_student_pertype($student_id, $type) {
	global $DB;

	return $DB->get_records_sql("SELECT AVG(a.answer) FROM {feedbackccna_answer} a INNER JOIN {feedbackccna_module} m ON a.module_id = m_id WHERE  m.which_way='".TEACHER_FOR_STUDENT."' AND a.student_id = ? AND m.type = ?", array($student_id, $type));
}
//  functie de obtinut valoare medie
//  - student_id
//  - course_id
function average_rating_student_percourse($student_id, $course_id) {
	global $DB;

	return $DB->get_records_sql("SELECT AVG(a.answer) FROM {feedbackccna_answer} a INNER JOIN {feedbackccna_module} m ON a.module_id = m_id WHERE  m.which_way='".TEACHER_FOR_STUDENT."' AND a.student_id = ? AND m.course_id = ?", array($student_id, $course_id));
}

//  functie de obtinut valoare medie feedback student
//  - student_id
//  - type
//  - course_id
function average_rating_student_pertype_percourse($student_id, $type, $course_id) {
	global $DB;

	return $DB->get_records_sql("SELECT AVG(a.answer) FROM {feedbackccna_answer} a INNER JOIN {feedbackccna_module} m ON a.module_id = m_id WHERE  m.which_way='".TEACHER_FOR_STUDENT."' AND a.student_id = ? AND m.type = ? AND m.course_id = ?", array($student_id, $type, $course_id));
}

//	functie de obtinut nr de laboratoare completate de un utilizator
//	- course_id
//	- student_id
function get_user_feedback_count($course_id, $student_id, $type) {
	global $DB;

	return $DB->count_record_sql("SELECT COUNT(*) FROM {feedbackccna_module} m INNER JOIN {feedbackccna_answer} a ON m.id = a.module_id WHERE m.which_way ='".STUDENT_FOR_TEACHER."' AND m.type='".$type."' AND a.student_id = '".$student_id."' AND m.course_id='".$course_id."'");
}


//	functie de obtinut nr total de feedback-uri care au fost activate pe curs
//	- course_id
//  - type - laborator sau prezentare
function get_feedback_feedbacks_count($course_id, $type) {
	global $DB;

	return $DB->count_record_sql("SELECT COUNT(*) FROM {feedbackccna_module} WHERE course_id ='".$course_id."' AND allow != '".FEEDBACK_NOT_ALLOWED."' AND type='".$type."' AND m.which_way='".STUDENT_FOR_TEACHER."'");
}

//	functie care determina daca un student a terminat toate laboratoarele
//	- id_curs
//	- id_student
function user_completed_all_labs($course_id, $student_id) {
	return  get_user_feedback_count($course_id, $student_id, FEEDBACK_TYPE_LAB, FEEDBACK_STUDENT_LAB_DONE) ===  get_feedback_feedbacks_count($course_id, FEEDBACK_TYPE_LAB);
}

//  functie care obtine ratingul dat de cineva
//  - course_id -
//  - user_id -
//  - section -
//  - which_way -
function get_feedback_answer_records($course_id, $student_id, $section, $which_way) {
	global $DB;

        $section = get_correct_section($section);

        $ans_records = $DB->get_records_sql("SELECT * FROM {feedbackccna_answer} a JOIN {feedbackccna_module} m
                                         ON a.module_id = m.id AND a.student_id = ? AND m.section = ?
                                         AND m.course_id = ? AND m.which_way = ? ",
                                         array($student_id, $section, $course_id, $which_way));

        return $ans_records;
}

function get_feedback_answer_id($course_id, $student_id, $section, $which_way, $type) {

    global $DB;

    $section = get_correct_section($section);

    $id_records = $DB->get_records_sql("SELECT a.id FROM {feedbackccna_answer} a JOIN {feedbackccna_module} m
                                     ON a.module_id = m.id AND a.student_id = ? AND m.section = ?
                                     AND m.course_id = ? AND m.which_way = ? AND type = ?",
                                     array($student_id, $section, $course_id, $which_way, $type));

    foreach ($id_records as $id_record) {
        return $id_record->id;
    }

    return 0;

}
