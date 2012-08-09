<?php

// valoare initiala pentru orice modul de feedback
define('FEEDBACK_NOT_ALLOWED', 0);

// valoare pentru modulele de feedback deschise
define('FEEDBACK_ALLOWED', 1);

// valoare pentru modulele de feedback inchise
define('FEEDBACK_CLOSED', -1);

// valoarea implicita la adaugarea in DB
define('DEFAULT_FEEDBACK_ALLOWED', FEEDBACK_NOT_ALLOWED);

// feedback-ul se da de catre instructor
define('TEACHER_FOR_STUDENT', 1);

// feedback-ul se da de catre student
define('STUDENT_FOR_TEACHER', 2);

// feedback-ul vizeaza prezentarea
define('FEEDBACK_TYPE_PRE', 1);

// feedback-ul vizeaza laboratorul
define('FEEDBACK_TYPE_LAB', 2);

// scoruri pentru activitatea studentului in cadrul laboratorului
define('LAB_ABSENT', 0);
define('LAB_STARTED', 1);
define('LAB_HALFWAY', 2);
define('LAB_DONE', 3);

// id-ul rolului "student"
define('STUDENT_ROLE', 5);

// valoarea "checked" pentru advanced checkboxes din locallib.php
define('CHECKED', 1);

