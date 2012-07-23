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

$contextid = $context->id;
$courseid = $course->id;

add_to_log($course->id, 'feedbackccna', 't_view', "view.php?id={$cm->id}", $feedbackccna->name, $cm->id);

/// Print the page header

$PAGE->set_url('/mod/feedbackccna/t_view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($feedbackccna->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_context($context);


// Output starts here
echo $OUTPUT->header();

build_tabs('t_view', $id, $n);

global $DB;
global $USER;

//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

echo '<link type="text/css" rel="stylesheet" href="achievement.css"/>';

$vach = array();
foreach($_POST as $key=>$post){
	if(strpos($key,'color')!==FALSE){
		$dd=explode('_',$key);
		if($post==1){
			array_push($vach, $dd[1]);
		}
	}	
}



include('participants.php');



foreach($vach as $key){
	$delid=$key;

	$students = get_students_by_course($course->id);
	foreach($students as $student){
		$name='user'.$student->id;
		if (array_key_exists($name,$_POST)){

			$post=new object();
			$post->discussion = 0;
			$post->parent = 0;
			$post->userid = $USER->id;
			$t = time();
			$post->created = $t;
			$post->modified = $t;
			$post->mailed = 0;
			$post->subject = "";
			$post->message = "";
			//$fid = $DB->insert_record('forum_posts', $post);

			$f=new object();
			$f->achid=$delid;
			$f->postid=$fid;
			$usr=$student->id;
			$pc=get_profile_course_by_course_user($course->id,$usr);
			//$profile = $DB->get_record('academy_profile',array('userid'=>$usr));
			if ($pc!=NULL) {
				// add the new feedback inside the course
				$f->apcid=$pc[$course->id]->pcid;
				//$DB->insert_record('academy_profile_feedback',$f);
			}else{
				$tmpcourse = new object();
				//$tmpcourse->apid = $profile->id;
				$tmpcourse->courseid = $course->id;
				$tmpcourse->hidden = 0;
				//$f->apcid = $DB->insert_record('academy_profile_course',$tmpcourse);
				//$DB->insert_record('academy_profile_feedback',$f);
			}

		}
	}
}

//!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

// Finish the page
echo $OUTPUT->footer();

?>
