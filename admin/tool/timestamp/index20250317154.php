<?php
// Standard GPL and phpdocs

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once('pagination.php');

// Uncomment if you've defined a page in settings.php with this name:
// admin_externalpage_setup('tooltimestamp');

// Set up the page
$title = get_string('pluginname', 'tool_timestamp');
$pagetitle = $title;
$url = new moodle_url("/admin/tool/timestamp/index.php");
$PAGE->set_url($url);
$PAGE->set_title($title);
$PAGE->set_heading($title);
require_login(); // Ensure the user is logged in

$output = $PAGE->get_renderer('tool_timestamp');

// Standard header/heading
echo $OUTPUT->header();
echo $OUTPUT->heading($pagetitle);

global $SESSION, $DB;

/** =========================================================================
 *  1) If "showallstudent=1" is set, we keep that in a variable
 *     (though be cautious with large datasets).
 *  =========================================================================*/
$showallstudent = 0;
if (isset($_GET['showallstudent']) && $_GET['showallstudent'] == 1) {
    $showallstudent = 1; // We'll preserve the code but note it can be huge
}

/** =========================================================================
 *  2) Handle "Update" Form Submission (the manual date overrides)
 *     Add future date checks here.
 *  =========================================================================*/
if (isset($_POST['updateform']) && $_POST['updateform'] != '') {

    // Arrays from the form
    $useridarray   = $_POST['userid'];
    $graderidarray = $_POST['graderid'];
    $assidarray    = $_POST['assid'];
    $itemidarray   = $_POST['itemid'];
    $gradeitemid   = $_POST['gradeitemid'];
    $updateidarray = $_POST['updateidarray']; // submission IDs

    $u1 = false; 
    $u2 = false;

    $c = 0;
    foreach ($updateidarray as $key => $submissionid) {
        // Form fields for new dates
        $newdate1_field = "newdate1_".$submissionid; // submission date
        $newdate2_field = "newdate2_".$submissionid; // grade date

        // 1) If a new submission date is provided
        if (!empty($_POST[$newdate1_field])) {
            $newtimestamp1 = strtotime($_POST[$newdate1_field]);

            // FUTURE DATE CHECK
            if ($newtimestamp1 > time()) {
                // Show JS alert and SKIP updating
                echo "<script>alert('Submission date is in the future. Please check.');</script>";
            } else {

                // === NEW CODE FOR ATTEMPT ORDER CHECK (submission only) ===
                // 1. Get info about the current submission
                $current = $DB->get_record('assign_submission', [
                    'id' => $submissionid
                ], 'id, attemptnumber, assignment, userid, timemodified', MUST_EXIST);

                // 2. If attemptnumber > 0, fetch the previous attempt
                if ($current->attemptnumber > 0) {
                    $previous = $DB->get_record('assign_submission', [
                        'assignment'    => $current->assignment,
                        'userid'        => $current->userid,
                        'attemptnumber' => ($current->attemptnumber - 1)
                    ], 'id, timemodified', IGNORE_MULTIPLE);

                    // If we found a previous attempt, compare the new date
                    if (!empty($previous)) {
                        // Must be strictly AFTER the previous attempt
                        if ($newtimestamp1 <= $previous->timemodified) {
                            echo "<script>alert('New attempt date must be AFTER the previous attempt date!');</script>";
                            // Skip updating this row
                            continue;
                        }
                    }
                }
                // === END NEW CODE ===

                // Update {assign_submission}
                $u1 = $DB->execute("
                    UPDATE {assign_submission}
                       SET timemodified = :ts
                     WHERE id = :subid
                ", ['ts' => $newtimestamp1, 'subid' => $submissionid]);

                // Update submission files
                $DB->execute("
                    UPDATE {files}
                       SET timemodified = :ts
                     WHERE component = 'assignsubmission_file'
                       AND filearea   = 'submission_files'
                       AND itemid     = :subid
                       AND source <> ''
                       AND filename <> ''
                ", ['ts' => $newtimestamp1, 'subid' => $submissionid]);
            }
        }

        // 2) If a new grade date is provided
        if (!empty($_POST[$newdate2_field])) {
            $newtimestamp2 = strtotime($_POST[$newdate2_field]);

            $userid   = $useridarray[$c];
            $graderid = $graderidarray[$c];
            $assignid = $assidarray[$c];
            $gid      = $gradeitemid[$c]; // {assign_grades}.id

            // FUTURE DATE CHECK
            if ($newtimestamp2 > time()) {
                echo "<script>alert('Grade date is in the future. Please check.');</script>";
            } else if (!empty($gid)) {
                // Update {assign_grades}
                $u2 = $DB->execute("
                    UPDATE {assign_grades}
                       SET timemodified = :ts
                     WHERE id = :gradeid
                ", ['ts' => $newtimestamp2, 'gradeid' => $gid]);

                // Update feedback files
                $DB->execute("
                    UPDATE {files}
                       SET timemodified = :ts
                     WHERE component = 'assignfeedback_file'
                       AND filearea   = 'feedback_files'
                       AND itemid     = :gradeid
                ", ['ts' => $newtimestamp2, 'gradeid' => $gid]);

                // Update {grade_grades} if relevant
                $list_select = $DB->get_record_sql("
                    SELECT gi.id AS gid
                      FROM {grade_items} gi
                     WHERE gi.iteminstance = :assignid
                       AND gi.itemmodule = 'assign'
                ", ['assignid' => $assignid]);

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
    } // end foreach

    // Sorting/pagination parameters for redirect
    $sort     = (empty($_POST['sort'])) ? 1 : $_POST['sort'];
    $sorttype = (empty($_POST['sorttype'])) ? '' : $_POST['sorttype'];
    $page     = (!empty($_POST['page'])) ? $_POST['page'] : 1;
    $showall  = (!empty($_REQUEST['showallstudent'])) ? $_REQUEST['showallstudent'] : 0;

    // Decide success or fail
    if ($u1 == true || $u2 == true) {
        $urltogo = $CFG->wwwroot."/admin/tool/timestamp/index.php?page={$page}&update=1&sort={$sort}&sorttype={$sorttype}&showallstudent={$showall}";
    } else {
        $urltogo = $CFG->wwwroot."/admin/tool/timestamp/index.php?page={$page}&update=0&sort={$sort}&sorttype={$sorttype}&showallstudent={$showall}";
    }

    // Show interim "loading" then redirect
    ?>
    <div style="padding-left: 38px;">
        <img src="<?php echo $CFG->wwwroot; ?>/admin/tool/timestamp/templates/images/loader.gif" border="0">
    </div>
    <script>window.location.href='<?php echo $urltogo; ?>';</script>
    <?php
    exit;
}

/** =========================================================================
 *  3) Show Success/Error if ?update=1 or ?update=0
 *  =========================================================================*/
if (isset($_GET['update'])) {
    if ($_GET['update'] == 1) {
        echo '<div class="success-msg"><i class="fa fa-check"></i> The data has been successfully updated!</div>';
    } else {
        echo '<div class="error-msg"><i class="fa fa-times-circle"></i>Some error occured! Please try again later or contact support.</div>';
    }
}

/** =========================================================================
 *  4) Sorting Logic (submission, grades, assignment, or default)
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
    // Default
    if (empty($SESSION->sortorder)) {
        $SESSION->sortorder = " ORDER BY x.id DESC";
        $SESSION->sortval   = 1;
        $SESSION->sorttype  = "";
    }
}

// For toggling sort direction in the UI
if (@$SESSION->sortval == 1) {
    $sortvalold = 2;
} else {
    $sortvalold = 1;
}

/** =========================================================================
 *  5) Pagination Setup
 *  =========================================================================*/
if (isset($_REQUEST["page"])) {
    $page = (int)$_REQUEST["page"];
} else {
    $page = 1;
}
$setLimit  = 10; // We fix 10 records per page
$pageLimit = ($page * $setLimit) - $setLimit;

/** =========================================================================
 *  6) Build "Auto-Complete" Strings for Course/Student/Assessment
 *  =========================================================================*/
$list_all_courses = $DB->get_records_sql("SELECT id AS courseid, fullname AS coursename FROM {course} WHERE 1");
$coursestr = '';
foreach ($list_all_courses as $course) {
    $coursestr .= '"'.$course->coursename."|".$course->courseid.'",';
}
$coursestr = "[".$coursestr."]";

$list_all_students = $DB->get_records_sql("
    SELECT DISTINCT u.id AS studentid, u.firstname, u.lastname
      FROM {user} u
      JOIN {user_enrolments} ue ON ue.userid = u.id
      JOIN {enrol} e ON e.id = ue.enrolid
      JOIN {role_assignments} ra ON ra.userid = u.id
      JOIN {context} ct ON ct.id = ra.contextid AND ct.contextlevel=50
      JOIN {role} r ON r.id=ra.roleid AND r.shortname='student'
     WHERE e.status=0
       AND u.suspended=0
       AND u.deleted=0
       AND ue.status=0
");
$studentsstr = '';
foreach ($list_all_students as $stu) {
    $fullname = $stu->firstname." ".$stu->lastname;
    $studentsstr .= '"'.$fullname."|".$stu->studentid.'",';
}
$studentsstr = "[".$studentsstr."]";

$list_all_students_username = $DB->get_records_sql("SELECT u.username, u.id AS studentuserid FROM {user} u");
$studentsstrusername = '';
foreach ($list_all_students_username as $u) {
    $studentsstrusername .= '"'.$u->username."|".$u->studentuserid.'",';
}
$studentsstrusername = "[".$studentsstrusername."]";

$list_all_assessments = $DB->get_records_sql("
    SELECT z.id AS assignmentid, z.name AS assignmentname, z.course
      FROM {assign} z
");
$assessmentstr = '';
foreach ($list_all_assessments as $as) {
    $assessmentstr .= '"'.$as->assignmentname."|".$as->assignmentid.'",';
}
$assessmentstr = "[".$assessmentstr."]";

/** =========================================================================
 *  7) Handle Searching / Filtering (store in SESSION)
 *  =========================================================================*/
if (isset($_POST['search']) && $_POST['search'] == " Search ") {

    // Course
    if (!empty($_POST['courseid']) && !empty($_POST['coursename'])) {
        $SESSION->courseid   = $_POST['courseid'];
        $SESSION->coursename = $_POST['coursename'];
    } else if (empty($_POST['coursename'])) {
        $SESSION->courseid   = '';
        $SESSION->coursename = '';
    }

    // Student by name
    if (!empty($_POST['studentid']) && !empty($_POST['studentname'])) {
        $SESSION->studentid   = $_POST['studentid'];
        $SESSION->studentname = $_POST['studentname'];
    } else if (empty($_POST['studentname'])) {
        $SESSION->studentid   = '';
        $SESSION->studentname = '';
    }

    // Assessment
    if (!empty($_POST['assessmentid']) && !empty($_POST['assessmentname'])) {
        $SESSION->assessmentid   = $_POST['assessmentid'];
        $SESSION->assessmentname = $_POST['assessmentname'];
    } else if (empty($_POST['assessmentname'])) {
        $SESSION->assessmentid   = '';
        $SESSION->assessmentname = '';
    }

    // Student by username
    if (!empty($_POST['studentuserid']) && !empty($_POST['studentusername'])) {
        $SESSION->studentuserid   = $_POST['studentuserid'];
        $SESSION->studentusername = $_POST['studentusername'];
    } else if (empty($_POST['studentusername'])) {
        $SESSION->studentuserid   = '';
        $SESSION->studentusername = '';
    }
}

// "Show all" => reset
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
 *  8) Build the Big IF/ELSE for Filters, 
 *     but REMOVE the default scenario to show NO results by default
 *  =========================================================================*/
if (!isset($SESSION->courseid))      { $SESSION->courseid = ''; }
if (!isset($SESSION->studentid))     { $SESSION->studentid = ''; }
if (!isset($SESSION->studentuserid)) { $SESSION->studentuserid = ''; }
if (!isset($SESSION->assessmentid))  { $SESSION->assessmentid = ''; }

$sortorder = (!empty($SESSION->sortorder)) ? $SESSION->sortorder : " ORDER BY x.id DESC";

$sql_all = '';
$sql     = '';

// 1) Course only
if (!empty($SESSION->courseid) && empty($SESSION->studentid) && empty($SESSION->assessmentid)) {

    $sql_all = "SELECT
        x.id AS rowid,
        x.userid,
        x.timemodified,
        x.assignment,
        x.attemptnumber AS attemptnum,
        y.firstname,
        y.lastname,
        us.firstname AS graderfirstname,
        us.lastname  AS graderlastname,
        us.id        AS graderuserid,
        z.id AS assignmentid,
        z.name AS assignmentname,
        z.course,
        g.id AS gradeitemid,
        g.userid AS uid,
        g.grader AS graderid,
        g.assignment AS ass,
        g.timemodified AS gtimemodified
      FROM {assign_submission} x
      LEFT JOIN {user} y ON x.userid = y.id
      LEFT JOIN {assign} z ON x.assignment = z.id
      LEFT JOIN {assign_grades} g
             ON g.assignment = x.assignment
            AND g.userid = x.userid
            AND g.attemptnumber = x.attemptnumber
      LEFT JOIN {user} us ON g.grader = us.id
      WHERE z.course = {$SESSION->courseid}
      $sortorder";

    $sql = $sql_all . " LIMIT {$pageLimit}, {$setLimit}";

// 2) Student only
} else if (empty($SESSION->courseid) && !empty($SESSION->studentid) && empty($SESSION->assessmentid)) {

    $sql_all = "SELECT
        x.id AS rowid,
        x.userid,
        x.timemodified,
        x.assignment,
        x.attemptnumber AS attemptnum,
        y.firstname,
        y.lastname,
        y.id,
        us.firstname AS graderfirstname,
        us.lastname  AS graderlastname,
        us.id        AS graderuserid,
        z.id AS assignmentid,
        z.name AS assignmentname,
        z.course,
        g.id AS gradeitemid,
        g.userid AS uid,
        g.grader AS graderid,
        g.assignment AS ass,
        g.timemodified AS gtimemodified
      FROM {assign_submission} x
      JOIN {user} y ON x.userid = y.id
      JOIN {assign} z ON x.assignment = z.id
      LEFT JOIN {assign_grades} g
             ON g.assignment = x.assignment
            AND g.userid = x.userid
            AND g.attemptnumber = x.attemptnumber
      LEFT JOIN {user} us ON g.grader = us.id
      WHERE x.userid = {$SESSION->studentid}
      $sortorder";

    $sql = $sql_all . " LIMIT {$pageLimit}, {$setLimit}";

// 3) Course + assignment
} else if (!empty($SESSION->courseid) && empty($SESSION->studentid) && !empty($SESSION->assessmentid)) {
    $sql_all = "SELECT
        x.id AS rowid,
        x.userid,
        x.timemodified,
        x.assignment,
        x.attemptnumber AS attemptnum,
        y.firstname,
        y.lastname,
        y.id,
        us.firstname AS graderfirstname,
        us.lastname  AS graderlastname,
        us.id        AS graderuserid,
        z.id AS assignmentid,
        z.name AS assignmentname,
        z.course,
        g.id AS gradeitemid,
        g.userid AS uid,
        g.grader AS graderid,
        g.assignment AS ass,
        g.timemodified AS gtimemodified
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
      $sortorder";

    $sql = $sql_all . " LIMIT {$pageLimit}, {$setLimit}";

// 4) Course + student (no assignment)
} else if (!empty($SESSION->courseid) && !empty($SESSION->studentid) && empty($SESSION->assessmentid)) {
    $sql_all = "SELECT
        x.id AS rowid,
        x.userid,
        x.timemodified,
        x.assignment,
        x.attemptnumber AS attemptnum,
        y.firstname,
        y.lastname,
        y.id,
        us.firstname AS graderfirstname,
        us.lastname  AS graderlastname,
        us.id        AS graderuserid,
        z.id AS assignmentid,
        z.name AS assignmentname,
        z.course,
        g.id AS gradeitemid,
        g.userid AS uid,
        g.grader AS graderid,
        g.assignment AS ass,
        g.timemodified AS gtimemodified
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
      $sortorder";

    $sql = $sql_all . " LIMIT {$pageLimit}, {$setLimit}";

// 5) Course + student + assignment
} else if (!empty($SESSION->courseid) && !empty($SESSION->studentid) && !empty($SESSION->assessmentid)) {
    $sql_all = "SELECT
        x.id AS rowid,
        x.userid,
        x.timemodified,
        x.assignment,
        x.attemptnumber AS attemptnum,
        y.firstname,
        y.lastname,
        y.id,
        us.firstname AS graderfirstname,
        us.lastname  AS graderlastname,
        us.id        AS graderuserid,
        z.id AS assignmentid,
        z.name AS assignmentname,
        z.course,
        g.id AS gradeitemid,
        g.userid AS uid,
        g.grader AS graderid,
        g.assignment AS ass,
        g.timemodified AS gtimemodified
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
      $sortorder";

    $sql = $sql_all . " LIMIT {$pageLimit}, {$setLimit}";

// 6) Student by username
} else if (empty($SESSION->courseid) && !empty($SESSION->studentuserid) && empty($SESSION->assessmentid)) {
    $sql_all = "SELECT
        x.id AS rowid,
        x.userid,
        x.timemodified,
        x.assignment,
        x.attemptnumber AS attemptnum,
        y.firstname,
        y.lastname,
        y.id,
        us.firstname AS graderfirstname,
        us.lastname  AS graderlastname,
        us.id        AS graderuserid,
        z.id AS assignmentid,
        z.name AS assignmentname,
        z.course,
        g.id AS gradeitemid,
        g.userid AS uid,
        g.grader AS graderid,
        g.assignment AS ass,
        g.timemodified AS gtimemodified
      FROM {assign_submission} x
      JOIN {user} y ON x.userid = y.id
      JOIN {assign} z ON x.assignment = z.id
      LEFT JOIN {assign_grades} g
             ON g.assignment = x.assignment
            AND g.userid = x.userid
            AND g.attemptnumber = x.attemptnumber
      LEFT JOIN {user} us ON g.grader = us.id
      WHERE x.userid = {$SESSION->studentuserid}
      $sortorder";

    $sql = $sql_all . " LIMIT {$pageLimit}, {$setLimit}";

// 7) Course + studentuserid
} else if (!empty($SESSION->courseid) && !empty($SESSION->studentuserid) && empty($SESSION->assessmentid)) {
    $sql_all = "SELECT
        x.id AS rowid,
        x.userid,
        x.timemodified,
        x.assignment,
        x.attemptnumber AS attemptnum,
        y.firstname,
        y.lastname,
        y.id,
        us.firstname AS graderfirstname,
        us.lastname  AS graderlastname,
        us.id        AS graderuserid,
        z.id AS assignmentid,
        z.name AS assignmentname,
        z.course,
        g.id AS gradeitemid,
        g.userid AS uid,
        g.grader AS graderid,
        g.assignment AS ass,
        g.timemodified AS gtimemodified
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
      $sortorder";

    $sql = $sql_all . " LIMIT {$pageLimit}, {$setLimit}";

// 8) Course + studentuserid + assignment
} else if (!empty($SESSION->courseid) && !empty($SESSION->studentuserid) && !empty($SESSION->assessmentid)) {
    $sql_all = "SELECT
        x.id AS rowid,
        x.userid,
        x.timemodified,
        x.assignment,
        x.attemptnumber AS attemptnum,
        y.firstname,
        y.lastname,
        y.id,
        us.firstname AS graderfirstname,
        us.lastname  AS graderlastname,
        us.id        AS graderuserid,
        z.id AS assignmentid,
        z.name AS assignmentname,
        z.course,
        g.id AS gradeitemid,
        g.userid AS uid,
        g.grader AS graderid,
        g.assignment AS ass,
        g.timemodified AS gtimemodified
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
      $sortorder";

    $sql = $sql_all . " LIMIT {$pageLimit}, {$setLimit}";

// ELSE: no filters => do nothing (leave $sql/$sql_all blank)
} else {
    $sql_all = '';
    $sql     = '';
}

/** =========================================================================
 *  9) Execute Queries & Possibly Show All
 *  =========================================================================*/
$list_all = [];
$list = [];

// Only get records if we have an actual SQL query
if (!empty($sql_all)) {
    $list_all = $DB->get_records_sql($sql_all);
}

if ($showallstudent == 1 && !empty($sql_all)) {
    // Possibly huge query
    $list = $DB->get_records_sql($sql_all);
    $SESSION->showallstudent = 1;
} else if (!empty($sql)) {
    // Standard pagination
    $list = $DB->get_records_sql($sql);
    $SESSION->showallstudent = 0;
}

/** =========================================================================
 * 10) Build $arr for the Renderer (Include Attempt #)
 *  =========================================================================*/
$arr = array();
foreach ($list as $row) {
    // Submission date
    if (!empty($row->timemodified)) {
        $submissiondate = date("F j, Y, g:i a", $row->timemodified);
    } else {
        $submissiondate = 'NA';
    }

    // Grade date
    if (!empty($row->gtimemodified)) {
        $gtimemodified = date("F j, Y, g:i a", $row->gtimemodified);
        $grade_exists  = '';
    } else {
        $gtimemodified = 'NA';
        $grade_exists  = 'disabled';
    }

    // Grader
    if (empty($row->graderid)) {
        $gradername = 'NA';
    } else {
        $gradername = $row->graderfirstname." ".$row->graderlastname;
    }

    // Attempt number (0-based in DB)
    $attemptnum = (isset($row->attemptnum)) ? $row->attemptnum : 0;
    $displayattempt = 'Attempt ' . ($attemptnum + 1);

    $arr[] = [
       'baseurl'         => $CFG->wwwroot,
       'rowid'           => $row->rowid,
       'userid'          => $row->userid,
       'name'            => $row->firstname.' '.$row->lastname,
       'assignmentname'  => $row->assignmentname,
       'assignmentid'    => $row->assignmentid,
       'timemodified'    => $submissiondate,
       'timemodifiedg'   => $gtimemodified,
       'gradeexists'     => $grade_exists,
       'gradername'      => $gradername,
       'graderid'        => $row->graderid,
       'gradeitemid'     => $row->gradeitemid,
       'attemptnum'      => $displayattempt
    ];
}

/** =========================================================================
 * 11) Build and Render the Page
 *  =========================================================================*/
if ($SESSION->sortval == 1) {
    $sortvalold = 2;
} else {
    $sortvalold = 1;
}

// Pass final array to renderable
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
 * 12) Show Pagination if Not "Show All"
 *  =========================================================================*/
if ($showallstudent != 1) {
    echo '<br/><br/>';
    echo displayPaginationHere(count($list_all), $setLimit, $page);
}

echo $OUTPUT->footer();
