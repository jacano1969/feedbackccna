<?php

defined('MOODLE_INTERNAL') || die();

function build_tabs($active, $id = '', $n = '') {

	global $CFG;

	if ($id) {
		$param1 = 'id';
		$param2 = $id;
	} else {
		$param1 = 'n';
		$param2 = $n;
	}

	$options = array();
	$inactive = array();
	$activetwo = array();
	$currenttab = $active;

	
	$options[] = new tabobject('view',
		$CFG->wwwroot . '/mod/feedbackccna/view.php?' . $param1 . '=' . $param2,
		get_string('view', 'feedbackccna'),
		get_string('viewdesc', 'feedbackccna'), true);

	$options[] = new tabobject('add',
		$CFG->wwwroot . '/mod/feedbackccna/add.php?' . $param1 . '=' . $param2,
		get_string('add', 'feedbackccna'),
		get_string('adddesc', 'feedbackccna'), true);

	$options[] = new tabobject('t_view',
		$CFG->wwwroot . '/mod/feedbackccna/t_view.php?' . $param1 . '=' . $param2,
		get_string('t_view', 'feedbackccna'),
		get_string('t_viewdesc', 'feedbackccna'), true);

	$tabs = array($options);

	print_tabs($tabs, $currenttab, $inactive, $activetwo);

}

function insert_feedback($user_id, $course_id, $week, $role, $type, $rating) {
	global $DB;
	$record = new stdClass();

	$record->user_id	= $user_id;
	$record->course_id  = $course_id;
	$record->week 		= $week;
	$record->role 		= $role;
	$record->type 		= $type;
	$record->rating 	= $rating;
	

	$DB->insert_records('feedbackccna_data',$record);
}

function get_week_feedback_for_user($user_id, $course_id, $week, $role, $type) {
	global $DB;
	
	return $DB->get_records_sql("SELECT type, value FROM {feedbackccna_data} WHERE 
			user_id 	= $user_id AND
			course_id	= $course_id AND
			week 		= $week AND
			role		= $role AND
			type 		= $type");
}

function get_week_feedback_for_teacher($course_id, $week, $type, $role) {
	global $DB;

	return $DB->get_records_sql("SELECT type, value FROM {feedbackccna_data} WHERE
			course_id	= $course_id AND
			week 		= $week AND
			role 		= $role AND
			type		= $type");
}

?>


