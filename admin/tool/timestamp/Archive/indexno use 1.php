<?php
// Standard GPL and phpdocs

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once('pagination.php');

// If you have it defined in settings.php, you can do:
// admin_externalpage_setup('tooltimestamp');

$title = get_string('pluginname', 'tool_timestamp');
$pagetitle = $title;
$url = new moodle_url("/admin/tool/timestamp/index.php");
$PAGE->set_url($url);
$PAGE->set_title($title);
$PAGE->set_heading($title);
require_login();

$output = $PAGE->get_renderer('tool_timestamp');

// Print standard header
echo $OUTPUT->header();
echo $OUTPUT->heading($pagetitle);

global $SESSION, $DB;

/** =========================================================================
 *  1) Handle "Update" Form Submission (future date checks)
 *  =========================================================================*/
if (!empty($_POST['updateform'])) {
    $useridarray   = $_POST['userid']       ?? [];
    $graderidarray = $_POST['graderid']     ?? [];
    $assidarray    = $_POST['assid']        ?? [];
    $gradeitemid   = $_POST['gradeitemid']  ?? [];
    $updateidarray = $_POST['updateidarray']?? [];

    $u1 = false; 
    $u2 = false;
    for ($c = 0; $c < count($updateidarray); $c++) {
        $submissionid = $updateidarray[$c];
        $newdate1_key = "newdate1_{$submissionid}";
        $newdate2_key = "newdate2_{$submissionid}";

        // 1) If submission date
        if (!empty($_POST[$newdate1_key])) {
            $ts1 = strtotime($_POST[$newdate1_key]);
            if ($ts1 > time()) {
                echo "<script>alert('Submission date is in the future. Please check.');</script>";
            } else {
                // Update assign_submission
                $u1 = $DB->execute(
                    "UPDATE {assign_submission} SET timemodified = :ts WHERE id = :id",
                    ['ts' => $ts1, 'id' => $submissionid]
                );
                // Update submission files
                $DB->execute(
                    "UPDATE {files} SET timemodified = :ts
                      WHERE component = 'assignsubmission_file'
                        AND filearea = 'submission_files'
                        AND itemid = :id
                        AND source <> '' AND filename <> ''",
                    ['ts' => $ts1, 'id' => $submissionid]
                );
            }
        }

        // 2) If grade date
        if (!empty($_POST[$newdate2_key])) {
            $ts2      = strtotime($_POST[$newdate2_key]);
            $gradeid  = $gradeitemid[$c] ?? 0;
            $userid   = $useridarray[$c] ?? 0;
            $assignid = $assidarray[$c]  ?? 0;

            if ($ts2 > time()) {
                echo "<script>alert('Grade date is in the future. Please check.');</script>";
            } else if (!empty($gradeid)) {
                // Update assign_grades
                $u2 = $DB->execute(
                    "UPDATE {assign_grades} SET timemodified = :ts WHERE id = :gid",
                    ['ts' => $ts2, 'gid' => $gradeid]
                );
                // Update feedback files
                $DB->execute(
                    "UPDATE {files} SET timemodified = :ts
                      WHERE component = 'assignfeedback_file'
                        AND filearea = 'feedback_files'
                        AND itemid   = :gid",
                    ['ts' => $ts2, 'gid' => $gradeid]
                );
                // Update grade_grades
                $gi = $DB->get_record_sql(
                    "SELECT id AS gid
                       FROM {grade_items}
                      WHERE iteminstance = :aid
                        AND itemmodule = 'assign'",
                    ['aid' => $assignid]
                );
                if (!empty($gi->gid)) {
                    $DB->execute(
                        "UPDATE {grade_grades}
                          SET timemodified = :ts
                          WHERE itemid = :giid
                            AND userid = :uid",
                        ['ts' => $ts2, 'giid' => $gi->gid, 'uid' => $userid]
                    );
                }
            }
        }
    } // end for

    // Sorting & redirect
    $sorttype = $_POST['sorttype'] ?? '';
    $sort     = $_POST['sort']     ?? 1;
    $page     = $_POST['page']     ?? 1;
    $showall  = $_REQUEST['showallstudent'] ?? 0;

    $updateStatus = ($u1 || $u2) ? 1 : 0;
    $url = $CFG->wwwroot."/admin/tool/timestamp/index.php?page={$page}&update={$updateStatus}&sort={$sort}&sorttype={$sorttype}&showallstudent={$showall}";
    echo "<div><img src='{$CFG->wwwroot}/admin/tool/timestamp/templates/images/loader.gif'></div>
          <script>window.location='{$url}';</script>";
    exit;
}

/** =========================================================================
 *  2) Show success or error if ?update=1 or 0
 *  =========================================================================*/
if (isset($_GET['update'])) {
    if ($_GET['update'] == 1) {
        echo "<div class='success-msg'><i class='fa fa-check'></i> Data updated successfully!</div>";
    } else {
        echo "<div class='error-msg'><i class='fa fa-times-circle'></i> Some error occurred! Please try again or contact support.</div>";
    }
}

/** =========================================================================
 *  3) Sorting Logic
 *  =========================================================================*/
if (!isset($SESSION->sortorder)) {
    $SESSION->sortorder = " ORDER BY x.id DESC";
    $SESSION->sortval   = 1;
    $SESSION->sorttype  = "";
}

if (isset($_GET['sorttype'])) {
    $stype = $_GET['sorttype'];
    $s     = $_GET['sort'] ?? 1;
    if ($stype == "submission") {
        $SESSION->sortorder = ($s==1)
            ? " ORDER BY x.timemodified ASC"
            : " ORDER BY x.timemodified DESC";
        $SESSION->sortval   = ($s==1)? 2 : 1;
        $SESSION->sorttype  = "submission";
    } else if ($stype == "grades") {
        $SESSION->sortorder = ($s==1)
            ? " ORDER BY g.timemodified ASC"
            : " ORDER BY g.timemodified DESC";
        $SESSION->sortval   = ($s==1)? 2 : 1;
        $SESSION->sorttype  = "grades";
    } else if ($stype == "assignment") {
        $SESSION->sortorder = ($s==1)
            ? " ORDER BY z.name ASC"
            : " ORDER BY z.name DESC";
        $SESSION->sortval   = ($s==1)? 2 : 1;
        $SESSION->sorttype  = "assignment";
    }
}

// Toggle for UI
$sortvalold = ($SESSION->sortval == 1) ? 2 : 1;

/** =========================================================================
 *  4) Pagination Setup
 *  =========================================================================*/
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$setLimit = 10;
$pageLimit = ($page * $setLimit) - $setLimit;

// Show all param
$showallstudent = (!empty($_GET['showallstudent']) && $_GET['showallstudent']==1) ? 1 : 0;

/** =========================================================================
 *  5) Searching / Filtering (Store in $SESSION)
 *  =========================================================================*/
if (!empty($_POST['search']) && $_POST['search'] == " Search ") {
    // Course
    if (!empty($_POST['courseid']) && !empty($_POST['coursename'])) {
        $SESSION->courseid   = $_POST['courseid'];
        $SESSION->coursename = $_POST['coursename'];
    } else if (empty($_POST['coursename'])) {
        $SESSION->courseid = '';
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

// If showall=1 link clicked, clear filters
if (!empty($_GET['showall']) && $_GET['showall'] == '1') {
    $SESSION->courseid       = '';
    $SESSION->studentid      = '';
    $SESSION->studentuserid  = '';
    $SESSION->assessmentid   = '';
    $SESSION->coursename     = '';
    $SESSION->studentname    = '';
    $SESSION->studentusername= '';
    $SESSION->assessmentname = '';
    $SESSION->sortorder      = " ORDER BY x.id DESC";
    $SESSION->sortval        = 1;
}

/** =========================================================================
 *  6) Build a Single Dynamic SQL with Attemptnumber
 *     Instead of big if/else, we do a conditions array.
 *  =========================================================================*/
$conds   = [];
$params  = [];

// Filter by course
if (!empty($SESSION->courseid)) {
    $conds[]            = "z.course = :courseid";
    $params['courseid'] = $SESSION->courseid;
}
// Filter by student (ID from the name list)
if (!empty($SESSION->studentid)) {
    $conds[]           = "x.userid = :studentid";
    $params['studentid'] = $SESSION->studentid;
}
// Filter by student (ID from the username list)
if (!empty($SESSION->studentuserid)) {
    $conds[]               = "x.userid = :studentuserid";
    $params['studentuserid'] = $SESSION->studentuserid;
}
// Filter by assignment
if (!empty($SESSION->assessmentid)) {
    $conds[]                 = "z.id = :assid";
    $params['assid']         = $SESSION->assessmentid;
}

$whereSQL = "";
if (!empty($conds)) {
    $whereSQL = " AND ".implode(" AND ", $conds);
}

$sortorder = $SESSION->sortorder;

$sql_all = "
  SELECT
    x.id AS rowid,
    x.userid,
    x.timemodified,
    x.assignment,
    x.attemptnumber AS attemptnum,  -- attempt number
    y.firstname,
    y.lastname,
    z.id AS assignmentid,
    z.name AS assignmentname,
    g.id AS gradeitemid,
    g.userid AS uid,
    g.grader AS graderid,
    g.assignment AS ass,
    g.timemodified AS gtimemodified,
    us.firstname AS graderfirstname,
    us.lastname AS graderlastname
  FROM {assign_submission} x
    JOIN {user} y     ON x.userid = y.id
    JOIN {assign} z   ON x.assignment = z.id
    LEFT JOIN {assign_grades} g
      ON (g.assignment = x.assignment
          AND g.userid = x.userid
          AND g.attemptnumber = x.attemptnumber)
    LEFT JOIN {user} us ON g.grader = us.id
  WHERE 1=1
  {$whereSQL}
  {$sortorder}
";

$sql_paginated = $sql_all." LIMIT {$pageLimit}, {$setLimit}";

// All results
$list_all = $DB->get_records_sql($sql_all, $params);

// Decide if "show all" or paginated
if ($showallstudent == 1) {
    $list = $list_all; // caution: can be big
    $SESSION->showallstudent = 1;
} else {
    $list = $DB->get_records_sql($sql_paginated, $params);
    $SESSION->showallstudent = 0;
}

/** =========================================================================
 *  7) Build $arr for the Renderer
 *  =========================================================================*/
$arr = [];
foreach ($list as $r) {
    $submissiondate = (!empty($r->timemodified))
        ? date("F j, Y, g:i a", $r->timemodified)
        : 'NA';

    $gtimemodified = 'NA';
    $grade_exists  = 'disabled';
    if (!empty($r->gtimemodified)) {
        $gtimemodified = date("F j, Y, g:i a", $r->gtimemodified);
        $grade_exists  = '';
    }

    $gradername = 'NA';
    if (!empty($r->graderid)) {
        $gradername = $r->graderfirstname.' '.$r->graderlastname;
    }

    // Attempt number
    $attemptnum = (!empty($r->attemptnum)) ? $r->attemptnum : 0;

    $arr[] = [
      'baseurl'         => $CFG->wwwroot,
      'rowid'           => $r->rowid,
      'userid'          => $r->userid,
      'name'            => $r->firstname.' '.$r->lastname,
      'assignmentname'  => $r->assignmentname,
      'assignmentid'    => $r->assignmentid,
      'timemodified'    => $submissiondate,
      'timemodifiedg'   => $gtimemodified,
      'gradeexists'     => $grade_exists,
      'gradername'      => $gradername,
      'graderid'        => $r->graderid,
      'gradeitemid'     => $r->gradeitemid,
      'attemptnum'      => $attemptnum
    ];
}

/** =========================================================================
 *  8) Render the Page
 *  =========================================================================*/
$renderable = new \tool_timestamp\output\index_page(
    $arr,
    // If you have auto-complete arrays, pass them here. 
    // For brevity we skip them or set them to "".
    $coursestr           = '',
    $assessmentstr       = '',
    $studentsstr         = '',
    $studentsstrusername = '',
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
 *  9) Pagination (only if not showing all)
 *  =========================================================================*/
if (!$showallstudent) {
    echo '<br><br>';
    echo displayPaginationHere(count($list_all), $setLimit, $page);
}

echo $OUTPUT->footer();
