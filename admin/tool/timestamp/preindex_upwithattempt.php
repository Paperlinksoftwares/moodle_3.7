<?php
// Standard GPL and phpdocs
require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once('pagination.php');

//admin_externalpage_setup('index'); // (Enable if needed)

// Set up the page.
$title = get_string('pluginname', 'tool_timestamp');
$pagetitle = $title;
$url = new moodle_url("/admin/tool/timestamp/index.php");
$PAGE->set_url($url);
$PAGE->set_title($title);
$PAGE->set_heading($title);
require_login();

// Our custom renderer
$output = $PAGE->get_renderer('tool_timestamp');

echo $OUTPUT->header();
echo $OUTPUT->heading($pagetitle);

global $SESSION, $DB;

/** =========================================================================
 *  1) Handle "Update" Form Submission
 *  =========================================================================*/
if (isset($_GET['showallstudent']) && $_GET['showallstudent'] == 1) {
    $showallstudent = 1;
}

// If the admin pressed "Update All" or "Update" for certain rows...
if (isset($_POST['updateform']) && $_POST['updateform'] != '') {

    $useridarray    = $_POST['userid'];      // array of user IDs
    $graderidarray  = $_POST['graderid'];    // array of grader IDs
    $assidarray     = $_POST['assid'];       // array of assignment IDs
    $itemidarray    = $_POST['itemid'];      // (not entirely used?)
    $gradeitemid    = $_POST['gradeitemid']; // array of {assign_grades} IDs
    $updateidarray  = $_POST['updateidarray']; // array of submission IDs

    $u1 = false; 
    $u2 = false;

    $c = 0;
    foreach ($updateidarray as $key => $val) {
        // $val is the submission ID
        $newdate1 = "newdate1_".$val; // submission date input
        $newdate2 = "newdate2_".$val; // grade date input

        // 1) If submission new date is set
        if (!empty($_POST[$newdate1])) {
            $newtimestamp1 = strtotime($_POST[$newdate1]);
            // Update assign_submission
            $u1 = $DB->execute("
                UPDATE {assign_submission}
                   SET timemodified = :ts
                 WHERE id = :subid
            ", ['ts' => $newtimestamp1, 'subid' => $val]);

            // Update submission files
            $DB->execute("
                UPDATE {files}
                   SET timemodified = :ts
                 WHERE component = 'assignsubmission_file'
                   AND filearea = 'submission_files'
                   AND itemid = :subid
                   AND source <> ''
                   AND filename <> ''
            ", ['ts' => $newtimestamp1, 'subid' => $val]);
        }

        // 2) If grade new date is set
        if (!empty($_POST[$newdate2])) {
            $newtimestamp2 = strtotime($_POST[$newdate2]);

            // The ID in $gradeitemid[$c] is from {assign_grades}
            $gradeid = $gradeitemid[$c];
            $userid  = $useridarray[$c];
            $assignid= $assidarray[$c];

            if (!empty($gradeid)) {
                // Update assign_grades
                $u2 = $DB->execute("
                    UPDATE {assign_grades}
                       SET timemodified = :ts
                     WHERE id = :gid
                ", ['ts' => $newtimestamp2, 'gid' => $gradeid]);

                // Update feedback files
                $DB->execute("
                    UPDATE {files}
                       SET timemodified = :ts
                     WHERE component = 'assignfeedback_file'
                       AND filearea   = 'feedback_files'
                       AND itemid     = :gid
                ", ['ts' => $newtimestamp2, 'gid' => $gradeid]);

                // Also update grade_grades table
                // First get the relevant grade_item for this assignment
                $list_select = $DB->get_record_sql("
                    SELECT gi.id AS gid
                      FROM {grade_items} gi
                     WHERE gi.iteminstance = :aid
                       AND gi.itemmodule = 'assign'
                ", ['aid' => $assignid]);

                if (!empty($list_select)) {
                    $DB->execute("
                        UPDATE {grade_grades}
                           SET timemodified = :ts
                         WHERE itemid = :giid
                           AND userid = :uid
                    ", [
                        'ts'   => $newtimestamp2,
                        'giid' => $list_select->gid,
                        'uid'  => $userid
                    ]);
                }
            }
        }
        $c++;
    } // end foreach ($updateidarray)

    // Figure out sorting/pagination parameters for redirect
    $sort     = (!empty($_POST['sort'])) ? $_POST['sort'] : 1;
    $sorttype = (!empty($_POST['sorttype'])) ? $_POST['sorttype'] : '';
    $page     = (!empty($_POST['page'])) ? $_POST['page'] : 1;
    $showall  = (!empty($_REQUEST['showallstudent'])) ? $_REQUEST['showallstudent'] : 0;

    // If any update returned true, we consider success
    if ($u1 == true || $u2 == true) {
        $urltogo = $CFG->wwwroot."/admin/tool/timestamp/index.php?page={$page}&update=1&sort={$sort}&sorttype={$sorttype}&showallstudent={$showall}";
    } else {
        $urltogo = $CFG->wwwroot."/admin/tool/timestamp/index.php?page={$page}&update=0&sort={$sort}&sorttype={$sorttype}&showallstudent={$showall}";
    }
    ?>
    <div style="padding-left: 38px;">
        <img src="<?php echo $CFG->wwwroot; ?>/admin/tool/timestamp/templates/images/loader.gif" border="0">
    </div>
    <script>window.location.href='<?php echo $urltogo; ?>';</script>
    <?php
    exit;
}

/** =========================================================================
 *  2) Show Success/Error Notice (if ?update=1 or ?update=0)
 *  =========================================================================*/
if (isset($_GET['update'])) {
    if ($_GET['update'] == 1) {
        echo '<div class="success-msg"><i class="fa fa-check"></i> The data has been successfully updated!</div>';
    } else {
        echo '<div class="error-msg"><i class="fa fa-times-circle"></i> Some error occurred! Please try again or contact support.</div>';
    }
}

/** =========================================================================
 *  3) Handle Sorting
 *  =========================================================================*/
if (isset($_GET['sorttype']) && $_GET['sorttype'] == "submission") {
    if ($_GET['sort'] == 1) {
        $SESSION->sortorder = " ORDER BY x.timemodified ASC";
        $SESSION->sortval   = 2;
        $SESSION->sorttype  = "submission";
    } else {
        $SESSION->sortorder = " ORDER BY x.timemodified DESC";
        $SESSION->sortval   = 1;
        $SESSION->sorttype  = "submission";
    }
} else if (isset($_GET['sorttype']) && $_GET['sorttype'] == "grades") {
    if ($_GET['sort'] == 1) {
        $SESSION->sortorder = " ORDER BY g.timemodified ASC";
        $SESSION->sortval   = 2;
        $SESSION->sorttype  = "grades";
    } else {
        $SESSION->sortorder = " ORDER BY g.timemodified DESC";
        $SESSION->sortval   = 1;
        $SESSION->sorttype  = "grades";
    }
} else if (isset($_GET['sorttype']) && $_GET['sorttype'] == "assignment") {
    if ($_GET['sort'] == 1) {
        $SESSION->sortorder = " ORDER BY z.name ASC";
        $SESSION->sortval   = 2;
        $SESSION->sorttype  = "assignment";
    } else {
        $SESSION->sortorder = " ORDER BY z.name DESC";
        $SESSION->sortval   = 1;
        $SESSION->sorttype  = "assignment";
    }
} else {
    // Default sort if not set
    if (empty($SESSION->sortorder)) {
        $SESSION->sortorder = " ORDER BY x.id DESC";
        $SESSION->sortval   = 1;
        $SESSION->sorttype  = "";
    }
}

// For toggling sort direction in UI
if (!empty($SESSION->sortval) && $SESSION->sortval == 1) {
    $sortvalold = 2;
} else {
    $sortvalold = 1;
}

/** =========================================================================
 *  4) Pagination Setup
 *  =========================================================================*/
$page = (isset($_REQUEST["page"])) ? (int)$_REQUEST["page"] : 1;
$setLimit  = 10; // how many records per page
$pageLimit = ($page * $setLimit) - $setLimit;

/** =========================================================================
 *  5) Gather Auto-Complete Data for the Form (Courses, Students, Assignments)
 *  =========================================================================*/
$list_all_courses = $DB->get_records_sql("
    SELECT id AS courseid, fullname AS coursename
      FROM {course}
    WHERE 1
");
$coursestr = '';
if ($list_all_courses) {
    foreach ($list_all_courses as $c) {
        $coursestr .= '"'.$c->coursename."|".$c->courseid.'",';
    }
}
$coursestr = "[".$coursestr."]";

$list_all_students = $DB->get_records_sql("
    SELECT DISTINCT u.id AS studentid, u.firstname, u.lastname
      FROM {user} u
      JOIN {user_enrolments} ue ON ue.userid = u.id
      JOIN {enrol} e           ON e.id = ue.enrolid
      JOIN {role_assignments} ra ON ra.userid = u.id
      JOIN {context} ct        ON ct.id = ra.contextid AND ct.contextlevel=50
      JOIN {role} r            ON r.id=ra.roleid AND r.shortname='student'
     WHERE e.status=0
       AND u.suspended=0
       AND u.deleted=0
       AND ue.status=0
");
$studentsstr = '';
if ($list_all_students) {
    foreach ($list_all_students as $s) {
        $fullname = $s->firstname." ".$s->lastname;
        $studentsstr .= '"'.$fullname."|".$s->studentid.'",';
    }
}
$studentsstr = "[".$studentsstr."]";

$list_all_students_username = $DB->get_records_sql("
    SELECT u.username, u.id AS studentuserid
      FROM {user} u
");
$studentsstrusername = '';
if ($list_all_students_username) {
    foreach ($list_all_students_username as $su) {
        $studentsstrusername .= '"'.$su->username."|".$su->studentuserid.'",';
    }
}
$studentsstrusername = "[".$studentsstrusername."]";

$list_all_assessments = $DB->get_records_sql("
    SELECT z.id AS assignmentid, z.name AS assignmentname, z.course
      FROM {assign} z
");
$assessmentstr = '';
if ($list_all_assessments) {
    foreach ($list_all_assessments as $a) {
        $assessmentstr .= '"'.$a->assignmentname."|".$a->assignmentid.'",';
    }
}
$assessmentstr = "[".$assessmentstr."]";

/** =========================================================================
 *  6) Handle Searching/Filtering (store in SESSION)
 *  =========================================================================*/
if (isset($_POST['search']) && $_POST['search'] == " Search ") {
    // If user typed in a course name but no ID => do not set course ID
    if (!empty($_POST['courseid']) && !empty($_POST['coursename'])) {
        $SESSION->courseid   = $_POST['courseid'];
        $SESSION->coursename = $_POST['coursename'];
    } else if (empty($_POST['coursename'])) {
        $SESSION->courseid   = '';
        $SESSION->coursename = '';
    }
    if (!empty($_POST['studentid']) && !empty($_POST['studentname'])) {
        $SESSION->studentid   = $_POST['studentid'];
        $SESSION->studentname = $_POST['studentname'];
    } else if (empty($_POST['studentname'])) {
        $SESSION->studentid   = '';
        $SESSION->studentname = '';
    }
    if (!empty($_POST['assessmentid']) && !empty($_POST['assessmentname'])) {
        $SESSION->assessmentid   = $_POST['assessmentid'];
        $SESSION->assessmentname = $_POST['assessmentname'];
    } else if (empty($_POST['assessmentname'])) {
        $SESSION->assessmentid   = '';
        $SESSION->assessmentname = '';
    }
    if (!empty($_POST['studentuserid']) && !empty($_POST['studentusername'])) {
        $SESSION->studentuserid   = $_POST['studentuserid'];
        $SESSION->studentusername = $_POST['studentusername'];
    } else if (empty($_POST['studentusername'])) {
        $SESSION->studentuserid   = '';
        $SESSION->studentusername = '';
    }
}

// If a user pressed "Show all" link
if (isset($_GET['showall']) && $_GET['showall'] == '1') {
    $SESSION->courseid       = '';
    $SESSION->studentid      = '';
    $SESSION->studentuserid  = '';
    $SESSION->assessmentid   = '';
    $SESSION->studentname    = '';
    $SESSION->studentusername= '';
    $SESSION->coursename     = '';
    $SESSION->assessmentname = '';
    $SESSION->sortorder      = " ORDER BY x.id DESC";
    $SESSION->sortval        = 1;
}

/** =========================================================================
 *  7) Build the Main SQL Depending on Filters
 *     (We preserve your existing big IF/ELSE blocks, but remove "max(id)"
 *      and match attempts by "g.attemptnumber = x.attemptnumber")
 *  =========================================================================*/

$sortorder = (!empty($SESSION->sortorder)) ? $SESSION->sortorder : " ORDER BY x.id DESC";

if (!isset($SESSION->courseid))      { $SESSION->courseid = ''; }
if (!isset($SESSION->studentid))     { $SESSION->studentid = ''; }
if (!isset($SESSION->studentuserid)) { $SESSION->studentuserid = ''; }
if (!isset($SESSION->assessmentid))  { $SESSION->assessmentid = ''; }

$sql_all = '';
$sql     = '';

// 1) Course only
if (!empty($SESSION->courseid) && empty($SESSION->studentid) && empty($SESSION->assessmentid)) {
    $sql_all = "SELECT x.id AS rowid, x.userid, x.timemodified, x.assignment,
                       y.firstname, y.lastname, 
                       us.firstname AS graderfirstname, us.lastname AS graderlastname, us.id AS graderuserid,
                       z.id AS assignmentid, z.name AS assignmentname, z.course,
                       g.id AS gradeitemid, g.userid AS uid, g.grader AS graderid, g.assignment AS ass, g.timemodified AS gtimemodified
                  FROM {assign_submission} x
                  LEFT JOIN {user} y ON x.userid = y.id
                  LEFT JOIN {assign} z ON x.assignment = z.id
                  LEFT JOIN {assign_grades} g 
                         ON g.assignment = x.assignment
                        AND g.userid = x.userid
                        AND g.attemptnumber = x.attemptnumber
                  LEFT JOIN {user} us ON g.grader = us.id
                 WHERE z.course = {$SESSION->courseid}
               {$sortorder}";

    $sql = $sql_all . " LIMIT {$pageLimit}, {$setLimit}";

    // Build an assessment list for that course
    $list_all_assessments = $DB->get_records_sql("
        SELECT z.id AS assignmentid, z.name AS assignmentname, z.course
          FROM {assign} z
         WHERE z.course = {$SESSION->courseid}
           AND z.grade != 0
    ");
    $assessmentstr = '';
    if ($list_all_assessments) {
        foreach ($list_all_assessments as $la) {
            $assessmentstr .= '"'.$la->assignmentname."|".$la->assignmentid.'",';
        }
    }
    $assessmentstr = "[".$assessmentstr."]";

// 2) Student only
} else if (empty($SESSION->courseid) && !empty($SESSION->studentid) && empty($SESSION->assessmentid)) {
    $sql_all = "SELECT x.id AS rowid, x.userid, x.timemodified, x.assignment,
                       y.firstname, y.lastname, y.id,
                       us.firstname AS graderfirstname, us.lastname AS graderlastname, us.id AS graderuserid,
                       z.id AS assignmentid, z.name AS assignmentname, z.course,
                       g.id AS gradeitemid, g.userid AS uid, g.grader AS graderid, g.assignment AS ass, g.timemodified AS gtimemodified
                  FROM {assign_submission} x
                  JOIN {user} y ON x.userid = y.id
                  JOIN {assign} z ON x.assignment = z.id
                  LEFT JOIN {assign_grades} g 
                         ON g.assignment = x.assignment
                        AND g.userid = x.userid
                        AND g.attemptnumber = x.attemptnumber
                  LEFT JOIN {user} us ON g.grader = us.id
                 WHERE x.userid = {$SESSION->studentid}
               {$sortorder}";

    $sql = $sql_all . " LIMIT {$pageLimit}, {$setLimit}";

// 3) Course + assignment
} else if (!empty($SESSION->courseid) && empty($SESSION->studentid) && !empty($SESSION->assessmentid)) {
    $sql_all = "SELECT x.id AS rowid, x.userid, x.timemodified, x.assignment,
                       y.firstname, y.lastname, y.id,
                       us.firstname AS graderfirstname, us.lastname AS graderlastname, us.id AS graderuserid,
                       z.id AS assignmentid, z.name AS assignmentname, z.course,
                       g.id AS gradeitemid, g.userid AS uid, g.grader AS graderid, g.assignment AS ass, g.timemodified AS gtimemodified
                  FROM {assign_submission} x
                  JOIN {user} y ON x.userid = y.id
                  JOIN {assign} z ON x.assignment = z.id
                  LEFT JOIN {assign_grades} g 
                         ON g.assignment = x.assignment
                        AND g.userid = x.userid
                        AND g.attemptnumber = x.attemptnumber
                  LEFT JOIN {user} us ON g.grader = us.id
                 WHERE z.course = {$SESSION->courseid}
                   AND z.id = {$SESSION->assessmentid}
               {$sortorder}";

    $sql = $sql_all . " LIMIT {$pageLimit}, {$setLimit}";

    // Build an assessment list for that course
    $list_all_assessments = $DB->get_records_sql("
        SELECT z.id AS assignmentid, z.name AS assignmentname, z.course
          FROM {assign} z
         WHERE z.course = {$SESSION->courseid}
           AND z.grade != 0
    ");
    $assessmentstr = '';
    if ($list_all_assessments) {
        foreach ($list_all_assessments as $la) {
            $assessmentstr .= '"'.$la->assignmentname."|".$la->assignmentid.'",';
        }
    }
    $assessmentstr = "[".$assessmentstr."]";

// 4) Course + student (no assignment)
} else if (!empty($SESSION->courseid) && !empty($SESSION->studentid) && empty($SESSION->assessmentid)) {
    $sql_all = "SELECT x.id AS rowid, x.userid, x.timemodified, x.assignment,
                       y.firstname, y.lastname, y.id,
                       us.firstname AS graderfirstname, us.lastname AS graderlastname, us.id AS graderuserid,
                       z.id AS assignmentid, z.name AS assignmentname, z.course,
                       g.id AS gradeitemid, g.userid AS uid, g.grader AS graderid, g.assignment AS ass, g.timemodified AS gtimemodified
                  FROM {assign_submission} x
                  JOIN {user} y ON x.userid = y.id
                  JOIN {assign} z ON x.assignment = z.id
                  LEFT JOIN {assign_grades} g 
                         ON g.assignment = x.assignment
                        AND g.userid = x.userid
                        AND g.attemptnumber = x.attemptnumber
                  LEFT JOIN {user} us ON g.grader = us.id
                 WHERE x.userid = {$SESSION->studentid}
                   AND z.course = {$SESSION->courseid}
               {$sortorder}";

    $sql = $sql_all . " LIMIT {$pageLimit}, {$setLimit}";

    // Build an assessment list for that course
    $list_all_assessments = $DB->get_records_sql("
        SELECT z.id AS assignmentid, z.name AS assignmentname, z.course
          FROM {assign} z
         WHERE z.course = {$SESSION->courseid}
           AND z.grade != 0
    ");
    $assessmentstr = '';
    if ($list_all_assessments) {
        foreach ($list_all_assessments as $la) {
            $assessmentstr .= '"'.$la->assignmentname."|".$la->assignmentid.'",';
        }
    }
    $assessmentstr = "[".$assessmentstr."]";

// 5) Course + student + assignment
} else if (!empty($SESSION->courseid) && !empty($SESSION->studentid) && !empty($SESSION->assessmentid)) {
    // Notice we now join attempts on x.attemptnumber = g.attemptnumber
    $sql_all = "SELECT x.id AS rowid, x.userid, x.timemodified, x.assignment,
                       y.firstname, y.lastname, y.id,
                       us.firstname AS graderfirstname, us.lastname AS graderlastname, us.id AS graderuserid,
                       z.id AS assignmentid, z.name AS assignmentname, z.course,
                       g.id AS gradeitemid, g.userid AS uid, g.grader AS graderid, g.assignment AS ass, g.timemodified AS gtimemodified
                  FROM {assign_submission} x
                  JOIN {user} y ON x.userid = y.id
                  JOIN {assign} z ON x.assignment = z.id
                  LEFT JOIN {assign_grades} g 
                         ON g.assignment = x.assignment
                        AND g.userid = x.userid
                        AND g.attemptnumber = x.attemptnumber
                  LEFT JOIN {user} us ON g.grader = us.id
                 WHERE x.userid = {$SESSION->studentid}
                   AND z.course = {$SESSION->courseid}
                   AND z.id = {$SESSION->assessmentid}
               {$sortorder}";

    $sql = $sql_all." LIMIT {$pageLimit}, {$setLimit}";

    $list_all_assessments = $DB->get_records_sql("
        SELECT z.id AS assignmentid, z.name AS assignmentname, z.course
          FROM {assign} z
         WHERE z.course = {$SESSION->courseid}
           AND z.grade!='0'
    ");
    $assessmentstr = '';
    if ($list_all_assessments) {
        foreach ($list_all_assessments as $la) {
            $assessmentstr .= '"'.$la->assignmentname."|".$la->assignmentid.'",';
        }
    }
    $assessmentstr = "[".$assessmentstr."]";

// 6) Student by username (studentuserid)
} else if (empty($SESSION->courseid) && !empty($SESSION->studentuserid) && empty($SESSION->assessmentid)) {
    $sql_all = "SELECT x.id AS rowid, x.userid, x.timemodified, x.assignment,
                       y.firstname, y.lastname, y.id,
                       us.firstname AS graderfirstname, us.lastname AS graderlastname, us.id AS graderuserid,
                       z.id AS assignmentid, z.name AS assignmentname, z.course,
                       g.id AS gradeitemid, g.userid AS uid, g.grader AS graderid, g.assignment AS ass, g.timemodified AS gtimemodified
                  FROM {assign_submission} x
                  JOIN {user} y ON x.userid = y.id
                  JOIN {assign} z ON x.assignment = z.id
                  LEFT JOIN {assign_grades} g 
                         ON g.assignment = x.assignment
                        AND g.userid = x.userid
                        AND g.attemptnumber = x.attemptnumber
                  LEFT JOIN {user} us ON g.grader = us.id
                 WHERE x.userid = {$SESSION->studentuserid}
               {$sortorder}";
    $sql = $sql_all . " LIMIT {$pageLimit}, {$setLimit}";

// 7) Course + studentuserid
} else if (!empty($SESSION->courseid) && !empty($SESSION->studentuserid) && empty($SESSION->assessmentid)) {
    $sql_all = "SELECT x.id AS rowid, x.userid, x.timemodified, x.assignment,
                       y.firstname, y.lastname, y.id,
                       us.firstname AS graderfirstname, us.lastname AS graderlastname, us.id AS graderuserid,
                       z.id AS assignmentid, z.name AS assignmentname, z.course,
                       g.id AS gradeitemid, g.userid AS uid, g.grader AS graderid, g.assignment AS ass, g.timemodified AS gtimemodified
                  FROM {assign_submission} x
                  JOIN {user} y ON x.userid = y.id
                  JOIN {assign} z ON x.assignment = z.id
                  LEFT JOIN {assign_grades} g 
                         ON g.assignment = x.assignment
                        AND g.userid = x.userid
                        AND g.attemptnumber = x.attemptnumber
                  LEFT JOIN {user} us ON g.grader = us.id
                 WHERE x.userid = {$SESSION->studentuserid}
                   AND z.course = {$SESSION->courseid}
               {$sortorder}";
    $sql = $sql_all . " LIMIT {$pageLimit}, {$setLimit}";

    $list_all_assessments = $DB->get_records_sql("
        SELECT z.id AS assignmentid, z.name AS assignmentname, z.course
          FROM {assign} z
         WHERE z.course = {$SESSION->courseid}
           AND z.grade!='0'
    ");
    $assessmentstr = '';
    if ($list_all_assessments) {
        foreach ($list_all_assessments as $la) {
            $assessmentstr .= '"'.$la->assignmentname."|".$la->assignmentid.'",';
        }
    }
    $assessmentstr = "[".$assessmentstr."]";

// 8) Course + studentuserid + assignment
} else if (!empty($SESSION->courseid) && !empty($SESSION->studentuserid) && !empty($SESSION->assessmentid)) {
    $sql_all = "SELECT x.id AS rowid, x.userid, x.timemodified, x.assignment,
                       y.firstname, y.lastname, y.id,
                       us.firstname AS graderfirstname, us.lastname AS graderlastname, us.id AS graderuserid,
                       z.id AS assignmentid, z.name AS assignmentname, z.course,
                       g.id AS gradeitemid, g.userid AS uid, g.grader AS graderid, g.assignment AS ass, g.timemodified AS gtimemodified
                  FROM {assign_submission} x
                  JOIN {user} y ON x.userid = y.id
                  JOIN {assign} z ON x.assignment = z.id
                  LEFT JOIN {assign_grades} g
                         ON g.assignment = x.assignment
                        AND g.userid = x.userid
                        AND g.attemptnumber = x.attemptnumber
                  LEFT JOIN {user} us ON g.grader = us.id
                 WHERE x.userid = {$SESSION->studentuserid}
                   AND z.course = {$SESSION->courseid}
                   AND z.id = {$SESSION->assessmentid}
               {$sortorder}";
    $sql = $sql_all . " LIMIT {$pageLimit}, {$setLimit}";

    $list_all_assessments = $DB->get_records_sql("
        SELECT z.id AS assignmentid, z.name AS assignmentname, z.course
          FROM {assign} z
         WHERE z.course = {$SESSION->courseid}
           AND z.grade!='0'
    ");
    $assessmentstr = '';
    if ($list_all_assessments) {
        foreach ($list_all_assessments as $la) {
            $assessmentstr .= '"'.$la->assignmentname."|".$la->assignmentid.'",';
        }
    }
    $assessmentstr = "[".$assessmentstr."]";

// 9) Default scenario (no filters)
} else {
    $sql_all = "SELECT x.id AS rowid, x.userid, x.timemodified, x.assignment,
                       y.firstname, y.lastname, y.id,
                       us.firstname AS graderfirstname, us.lastname AS graderlastname, us.id AS graderuserid,
                       z.id AS assignmentid, z.name AS assignmentname,
                       g.userid AS uid, g.id AS gradeitemid, g.grader AS graderid, g.assignment AS ass, g.timemodified AS gtimemodified
                  FROM {assign_submission} x
                  JOIN {user} y ON x.userid = y.id
                  JOIN {assign} z ON x.assignment = z.id
                  LEFT JOIN {assign_grades} g
                         ON g.assignment = x.assignment
                        AND g.userid = x.userid
                        AND g.attemptnumber = x.attemptnumber
                  LEFT JOIN {user} us ON g.grader = us.id
                 WHERE 1=1
               {$sortorder}";

    $sql = $sql_all." LIMIT {$pageLimit}, {$setLimit}";
}

/** =========================================================================
 *  8) Execute Queries & Possibly Show All
 *  =========================================================================*/
$list_all = $DB->get_records_sql($sql_all);

if (!empty($_GET['showallstudent']) && $_GET['showallstudent'] == 1) {
    $SESSION->showallstudent = 1;
    $list = $DB->get_records_sql($sql_all);
} else {
    $SESSION->showallstudent = 0;
    $list = $DB->get_records_sql($sql);
}

/** =========================================================================
 *  9) Build an Array for the Renderer
 *  =========================================================================*/
$arr = [];
if (!empty($list)) {
    foreach ($list as $rec) {
        // Format grade modified date
        if (!empty($rec->gtimemodified)) {
            $gtimemodified = date("F j, Y, g:i a", $rec->gtimemodified);
            $grade_exists  = '';
        } else {
            $gtimemodified = 'NA';
            $grade_exists  = 'disabled';
        }
        // Grader
        if (empty($rec->graderid)) {
            $gradername = 'NA';
        } else {
            $gradername = $rec->graderfirstname . ' ' . $rec->graderlastname;
        }
        // Submission date
        $submissiondate = (!empty($rec->timemodified))
                          ? date("F j, Y, g:i a", $rec->timemodified)
                          : 'NA';

        $arr[] = [
            'baseurl'         => $CFG->wwwroot,
            'rowid'           => $rec->rowid,
            'userid'          => $rec->userid,
            'name'            => $rec->firstname.' '.$rec->lastname,
            'assignmentname'  => $rec->assignmentname,
            'assignmentid'    => $rec->assignmentid,
            'timemodified'    => $submissiondate,
            'timemodifiedg'   => $gtimemodified,
            'gradeexists'     => $grade_exists,
            'gradername'      => $gradername,
            'graderid'        => $rec->graderid,
            'gradeitemid'     => $rec->gradeitemid
        ];
    }
}

/** =========================================================================
 * 10) Pass Data to the Renderer
 * =========================================================================*/
if ($SESSION->sortval == 1) {
    $sortvalold = 2;
} else {
    $sortvalold = 1;
}

// This renderable presumably generates HTML. 
// Keep it the same as your plugin’s original usage.
$renderable = new \tool_timestamp\output\index_page(
    $arr,
    $coursestr,
    $assessmentstr,
    $studentsstr,
    $studentsstrusername,
    $page,
    count($list_all),
    @$SESSION->studentname,
    @$SESSION->coursename,
    @$SESSION->assessmentname,
    @$SESSION->studentusername,
    @$SESSION->courseid,
    @$SESSION->assessmentid,
    @$SESSION->studentid,
    @$SESSION->studentuserid,
    @$SESSION->sortval,
    $sortvalold,
    @$SESSION->sorttype,
    @$SESSION->showallstudent
);
echo $output->render($renderable);

/** =========================================================================
 * 11) Show Pagination if Not “Show All”
 * =========================================================================*/
if (empty($_GET['showallstudent']) || $_GET['showallstudent'] != 1) {
    echo '<br><br>';
    echo displayPaginationHere(count($list_all), $setLimit, $page);
}

echo $OUTPUT->footer();
