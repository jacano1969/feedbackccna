<?php

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");
require_once($CFG->dirroot.'/lib/accesslib.php');
require_once(dirname(__FILE__).'/db_functions.php');
require_once($CFG->dirroot.'/lib/outputcomponents.php');
require_once($CFG->dirroot.'/lib/outputrenderers.php');

// needed for rendering the stars
//echo '<script type="text/javascript" src="prototype.js"></script>
//      <script type="text/javascript" src="stars.js"></script>';

// we need this in order to display the tabs, in view.php and t_view.php
function build_tabs($active, $id, $n, $context) {

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

   // this block builds each tab separately, then puts them all in an array, \
    // which is then displayed
    $options[] = new tabobject('view',
        $CFG->wwwroot.'/mod/feedbackccna/view.php?'.$param1.'='.$param2,
        get_string('view', 'feedbackccna'),
        get_string('viewdesc', 'feedbackccna'), true);

    $options[] = new tabobject('t_view',
        $CFG->wwwroot.'/mod/feedbackccna/t_view.php?'.$param1.'='.$param2,
        get_string('t_view', 'feedbackccna'),
        get_string('t_viewdesc', 'feedbackccna'), true);

    $tabs = array($options);

    // this is a moodle function
    print_tabs($tabs, $currenttab, $inactive, $activetwo);
   //
}

function build_tabs_local($active, $number){

    global $CFG;

    $options = array();
    $inactive = array();
    $activetwo = array();
    $currenttab = $active;

    for ($i = 1; $i <= $number; $i ++) {

        $options[] = new tabobject('dash_'.$i,
            $CFG->wwwroot . '/mod/feedbackccna/dashboard.php?type='.$i,
            get_string('name_'.$i,'feedbackccna'),
            get_string('desc_'.$i,'feedbackccna'),
            true);

    }

    $tabs = array($options);

    print_tabs($tabs, $currenttab, $inactive, $activetwo);
}

// creates a moodleform instance, with a few added functionalities
class add_view_form extends moodleform {

    function definition() {

        global $CFG;
        global $usr;
        global $cm;
        global $new_array;
        global $DB;
        global $values;

        // default star values for each type of feedback
        $values = array(
            FEED_TYPE_PRE => '1',
            FEED_TYPE_LAB => '1'
        );

       // we get these as parameters - that's what _customdata is for
        $cm = $this->_customdata['cm'];
        $id = $this->_customdata['id'];
        $f_id = $this->_customdata['f_id'];
        $user_id = $this->_customdata['user_id'];
        $course_id = $this->_customdata['courseid'];
       //

       // I guess these are standard, but I'm not using them (maybe moodle is)
        $mform = &$this->_form;

        $mform->addElement('hidden', 'action');
        $mform->setType('action', PARAM_TEXT);

        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);
       //

        $context = get_context_instance(CONTEXT_MODULE, $cm->id);

        // this is actually the section id
        $section = $cm->section;

        // get ALL the modules!
        $new_array = get_feedback_module_teacher($course_id, $section, $f_id,
            STUDENT_FOR_TEACHER);

       // I'm actually wondering if this has any use - we're not displaying \
        // old feedback answers anymore
        $records = get_feedback_answer_records($course_id, $user_id, $section,
            $f_id, STUDENT_FOR_TEACHER);

        // basically, it updates the default values to match your old answers
        if (isset($records)) {

            foreach ($records as $record) {

                $values[$record->type] = $record->answer;

            }

        }
       //

        // this little guy saved me from a lot of class extensions
        // it clears your string caches, making sure you get up-to-date ones
        get_string_manager()->reset_caches();


       // ah! good, old hacks!
        // they help making some decisions (maybe even display an error)
         // see below the "foreach"

        // nothing becomes 0 when we have actualy displayed something
        $nothing = 1;

        // something becomes 1 when new_array is NOT_NULL
        $something = 0;
       //

       // so far, we only have two types of modules: \
         // presentation, and laboratory
        // if you need to add more, you may add other "elseif"s, \
         // or you may go for a "switch" statement

        // let's take each module at a time
        foreach ($new_array as $data) {

            // oh, we found one! let's make sure we remember that we did
            $something = 1;

            // is it a presentation-type?
            if ($data->type == FEED_TYPE_PRE) {

                // can we give ratings to the teacher?
                if (has_capability('mod/feedbackccna:rateteacher', $context)
                    // and are we allowed to answer this specific question?
                    and $data->allow == FEED_ALLOWED
                    // let's not bother the admin by displaying this
                    and (!has_capability('mod/feedbackccna:feededit', $context))
                    // let's make sure we're in the classroom
                    and (!get_user_absent($course_id, $user_id, $f_id)
                        // if not, at least make sure nobody else is \
                         // (maybe the teacher forgot to update \
                         // the presence list)
                        or !class_graded($course_id, $f_id))
                    // let's make sure we didn't answer already
                    and (!get_user_answer_true($course_id, $user_id,
                        FEED_TYPE_PRE, $f_id))) {

                      // Then, you have my permission to answer!

                        // we'll display something, so keep an eye on the hacks
                        $nothing = 0;

                        // display the header (basically, the box-thingy)
                        $mform->addElement('header', 'editorheader',
                            get_string('headerlabel_presentation',
                             'feedbackccna'));

                       // so, here are the fabled stars
                        // first of all, a container
                        $mform->addElement('html', '<script type="text/javascript" src="prototype.js"></script>
      <script type="text/javascript" src="stars.js"></script><div id = "star'.$data->id.
                            '1" ></div>');
                        // keep the value in a hidden element
                        $mform->addElement('hidden', 'value'.$data->id.
                            FEED_TYPE_PRE, null, array('id' => 'value'.
                            $data->id.FEED_TYPE_PRE, 'type' => 'hidden'));

                        // we'll create the star object here
                        $mform->addElement('html',
                            '<script type = "text/javascript">
                                var s1 = new Stars({
                                    maxRating: 5,
                                    imagePath: "images/",
                                    value: '.$values[FEED_TYPE_PRE].',
                                    container:"star'.$data->id.FEED_TYPE_PRE.'",
                                    bindField:"value'.$data->id.FEED_TYPE_PRE.'"
                                });
                            </script>');
                       //

                        // we only show one question at once. no flooding!
                        break;
                      //

                }

                // if the user is a teacher
                if (has_capability('mod/feedbackccna:feedallow', $context)) {

                    // the display rule stays
                    $nothing = 0;

                    // if they didn't allow students to answer a question yet, \
                     // they may do so now
                    if ($data->allow != FEED_ALLOWED) {

                        // again, add the header/box/whatever
                        $mform->addElement('header', 'editorheader',
                            get_string('headerlabel2_presentation',
                             'feedbackccna'));

                        // add a checkbox, asking to allow the answers
                        $mform->addElement('advcheckbox', 'check'.$data->id.'1',
                            get_string('checkbox', 'feedbackccna'), null,
                            null, array(0, 1));

                    // what if they allowed them to, but changed mind?
                    } else {

                        // let's see how many have answered
                        $number1 = get_user_answer_count($course_id,
                            FEED_TYPE_PRE, $f_id);
                        // we may also like to see how many are they in total
                        $number_total = get_user_total($context);

                        // header. see above
                        $mform->addElement('header', 'editorheader',
                            get_string('headerlabel3_presentation',
                             'feedbackccna'));

                        // this would display something like \
                         // "1 out of over 9000 students have answered"
                        $mform->addElement('static', 'text'.FEED_TYPE_PRE, null,
                            $number1.get_string('text_mid', 'feedbackccna').
                            $number_total.get_string('text_last', 'feedbackccna'
                        ));

                        // help the poor teacher!
                         // show him the names of the infidels!
                        $mform->addHelpButton('text'.FEED_TYPE_PRE,
                            'student_number1', 'feedbackccna');

                        $mform->addElement('html', '<br/>');

                        // this is serious. end this madness now!
                        $mform->addElement('advcheckbox', 'uncheck'.$data->id.
                            FEED_TYPE_PRE, get_string('checkbox2',
                             'feedbackccna'), null, null, array(0, 1));

                    }

                }

              // then, maybe it is a laboratory that we're talking about?
            } elseif ($data->type == FEED_TYPE_LAB) {

                // is we're dealing with a student, the same conditions apply
                if (has_capability('mod/feedbackccna:rateteacher', $context)
                    and $data->allow == FEED_ALLOWED
                    and (!has_capability('mod/feedbackccna:feededit', $context))
                    and (!get_user_absent($course_id, $user_id, $f_id)
                        or !class_graded($course_id, $f_id))
                    and (!get_user_answer_true($course_id, $user_id,
                        FEED_TYPE_LAB, $f_id))) {

                       // Then, you have my permission to answer!

                        $nothing = 0;

                        $mform->addElement('header', 'editorheader',
                            get_string('headerlabel_lab', 'feedbackccna'));

                        $mform->addElement('html', '<script type="text/javascript" src="prototype.js"></script>
      <script type="text/javascript" src="stars.js"></script><div id = "star'.$data->id.
                            FEED_TYPE_LAB.'"></div>');

                        $mform->addElement('hidden', 'value'.$data->id.
                            FEED_TYPE_LAB, null, array('id' => 'value'.
                            $data->id.FEED_TYPE_LAB, 'type' => 'hidden'));

                        $mform->addElement('html',
                            '<script type = "text/javascript">
                                var s2 = new Stars({
                                    maxRating: 5,
                                    imagePath: "images/",
                                    value: '.$values[FEED_TYPE_LAB].',
                                    container:"star'.$data->id.FEED_TYPE_LAB.'",
                                    bindField:"value'.$data->id.FEED_TYPE_LAB.'"
                                });
                            </script>');

                        break;

                }

                // but, if they're teachers, the same as above goes
                if (has_capability('mod/feedbackccna:feedallow', $context)) {

                    $nothing = 0;

                    if ($data->allow != FEED_ALLOWED) {

                        $mform->addElement('header', 'editorheader',
                            get_string('headerlabel2_lab', 'feedbackccna'));

                        $mform->addElement('advcheckbox', 'check'.$data->id.
                            FEED_TYPE_LAB, get_string('checkbox',
                             'feedbackccna'), null, null, array(0, 1));

                    } else {

                        $number2 = get_user_answer_count($course_id,
                            FEED_TYPE_LAB, $f_id);

                        $number_total = get_user_total($context);

                        $mform->addElement('header', 'editorheader',
                            get_string('headerlabel3_lab', 'feedbackccna'));

                        $mform->addElement('static', 'text'.FEED_TYPE_LAB, null,
                            $number2.get_string('text_mid', 'feedbackccna').
                            $number_total.get_string('text_last',
                             'feedbackccna'));

                        $mform->addHelpButton('text'.FEED_TYPE_LAB,
                            'student_number2', 'feedbackccna');

                        $mform->addElement('html', '<br/>');

                        $mform->addElement('advcheckbox', 'uncheck'.$data->id.
                            FEED_TYPE_LAB, get_string('checkbox2',
                             'feedbackccna'), null, null, array(0, 1));

                    }

                }

            }

        }
       //


       // let's see about those hacks :)

        // if we displayed nothing (shame on us)
        if ($nothing) {

            // if we had some modules, but we were somehow constrained
            if($something) {

                // the user is a student, so tell them their teacher is bad
                $mform->addElement('header', 'editorheader',
                    get_string('headerlabel_nothing', 'feedbackccna'));

                // and help them get to da choppa
                $mform->addElement('html', '<a href = "'.$CFG->wwwroot.
                    '/course/view.php?id='.$course_id.'" >Back to course </a>');

            // if there were none, send some error (you'll hate this one >:) )
            } else {

                print_error('Feedback category is non-existent! Please check '.
                    'that your modules have been correctly inserted into the '.
                    'database! Error sent from locallib.php');

                // when the above error appears, it means that the "new_array" \
                 // variable in this file is empty. please check the return \
                 // output of the function populating that variable

            }

          // so, we displayed something, after all?
        } else {

            // admin left aside, show a button to all students
            if (has_capability('mod/feedbackccna:rateteacher', $context)
                or has_capability('mod/feedbackccna:feedallow', $context)) {

                   // this block prints a submit button, so that we may \
                    // actually send that answer

                    print_container_start(false, 'singlebutton');
                    $this->add_action_buttons(false, get_string('submitlabel',
                        'feedbackccna'));
                    print_container_end();

                   //

            }

        }

    }

}

