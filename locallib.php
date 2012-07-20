<?php

defined('MOODLE_INTERNAL') || die();

function build_tabs($active, $id = '', $n = '') {

	global $CFG;

	$options = array();

	$inactive = array();
	$activetwo = array();

	$currenttab = $active;

	if ($id) {
		$param1 = 'id';
		$param2 = $id;
	} else {
		$param1 = 'n';
		$param2 = $n;
	}

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

?>

