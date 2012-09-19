<?php

defined('MOODLE_INTERNAL') || die;

$category = $ADMIN->locate('ccna',true);

if (empty($category)){
    $ADMIN->add('root', new admin_category('ccna', "CCNA"));
}

$ADMIN->add('ccna', new admin_externalpage('board',
    get_string('dashboard_name', 'feedbackccna'),
    $CFG->wwwroot."/mod/feedbackccna/dashboard.php", 'moodle/site:config'));

?>

