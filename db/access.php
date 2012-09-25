<?php

defined('MOODLE_INTERNAL') || die();

$capabilities = array(


    'mod/feedbackccna:addinstance' => array(

        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'legacy' => array(
	    'editingteacher' => CAP_ALLOW,
            'teacher' => CAP_ALLOW,
	    'manager' => CAP_ALLOW
	)
    ),

    'mod/feedbackccna:feedallow' => array(

        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'legacy' => array(
            'editingteacher' => CAP_ALLOW,
            'teacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    'mod/feedbackccna:feededit' => array(

        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'legacy' => array(
            'manager' => CAP_ALLOW
        )
    ),

    'mod/feedbackccna:rateteacher' => array(

        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'legacy' => array(
            'student' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    'mod/feedbackccna:ratestudent' => array(

        'captype' => 'write',
        'contextlevel' => CONTEXT_MODULE,
        'legacy' => array(
            'editingteacher' => CAP_ALLOW,
            'teacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW
        )
    ),

    'mod/feedbackccna:admin' => array(

        'captype' => 'write',
        'contextlevel' => CONTEXT_SYSTEM,
        'legacy' => array(
            'manager' => CAP_ALLOW
        )
    )
);

