<?php
/**
 * Tool Timestamp – Main Index Page
 * (Shows & updates submission and grade timestamps, with assignment links)
 *
 * @package    tool_timestamp
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once('pagination.php'); // Your custom pagination function
require_once($CFG->dirroot.'/course/lib.php'); // Needed for get_coursemodule_from_instance()

// admin_externalpage_setup('tooltimestamp'); // Uncomment if needed

// Set up the page.
$title = get_string('pluginname', 'tool_timestamp');
$pagetitle = $title;
$url = new moodle_url("/admin/tool/timestamp/index.php");
$PAGE->set_url($url);
$PAGE->set_title($title);
$PAGE->set_heading($title);

require_login(); // Ensure user is logged in.

$output = $PAGE->get_renderer('tool_timestamp');

// Standard header/heading.
echo $OUTPUT->header();
echo $OUTPUT->heading($pagetitle);

global $SESSION, $DB;

/** =========================================================================
 *  1) Check "Show All" toggle
 *  =========================================================================*/
$showallstudent = 0;
if (isset($_GET['showallstudent']) && $_GET['showallstudent'] == 1) {
    $showallstudent = 1;
}

/** =========================================================================
 *  2) Handle "Update" form submission
 *  =========================================================================*/
if (!empty($_POST['updateform'])) {

    // Arrays from the form.
    $useridarray   = $_POST['userid'];
    $graderidarray = $_POST['graderid'];
    $assidarray    = $_POST['assid'];
    $gradeitemid   = $_POST['gradeitemid'];
    $updateidarray = $_POST['updateidarray']; // submission IDs.

    $u1 = false;
    $u2 = false;
    $c = 0;

    foreach ($updateidarray as $key => $submissionid) {

        $newdate1_field = "newdate1_".$submissionid; // new submission date
        $newdate2_field = "newdate2_".$submissionid; // new grade date

        /** --------------------------
         * A) Update submission date
         * -------------------------*/
        if (!empty($_POST[$newdate1_field])) {
            $newtimestamp1 = strtotime($_POST[$newdate1_field]);

            // FUTURE-DATE CHECK
            if ($newtimestamp1 > time()) {
                echo "<script>alert('Submission date is in the future. Please check.');</script>";
            } else {
                // Retrieve current submission info.
                $current = $DB->get_record('assign_submission', [
                    'id' => $submissionid
                ], 'id, attemptnumber, assignment, userid, timemodified', MUST_EXIST);

                $curattempt = $current->attemptnumber;
                $assignid   = $current->assignment;
                $curuserid  = $current->userid;

                // 1) Check previous attempt
                if ($curattempt > 0) {
                    $prev = $DB->get_record('assign_submission', [
                        'assignment'    => $assignid,
                        'userid'        => $curuserid,
                        'attemptnumber' => ($curattempt - 1)
                    ], 'id, timemodified', IGNORE_MULTIPLE);

                    if ($prev && $newtimestamp1 <= $prev->timemodified) {
                        echo "<script>alert('Attempt #"
                            .($curattempt+1)." date must be AFTER Attempt #"
                            .($curattempt)." date!');</script>";
                        // Skip this update
                        continue;
                    }
                }

                // 2) Check next attempt
                $next = $DB->get_record('assign_submission', [
                    'assignment'    => $assignid,
                    'userid'        => $curuserid,
                    'attemptnumber' => ($curattempt + 1)
                ], 'id, timemodified', IGNORE_MULTIPLE);

                if ($next && $newtimestamp1 >= $next->timemodified) {
                    echo "<script>alert('Attempt #"
                        .($curattempt+1)." date must be BEFORE Attempt #"
                        .($curattempt+2)." date!');</script>";
                    // Skip
                    continue;
                }

                // If checks pass, do the update in DB
                $u1 = $DB->execute("
                    UPDATE {assign_submission}
                       SET timemodified = :ts
                     WHERE id = :subid
                ", ['ts' => $newtimestamp1, 'subid' => $submissionid]);

                // Also update submission files
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

        /** ------------------------
         * B) Update grade date
         * ------------------------*/
        if (!empty($_POST[$newdate2_field])) {
            $newtimestamp2 = strtotime($_POST[$newdate2_field]);

            $userid   = $useridarray[$c];
            $graderid = $graderidarray[$c];
            $assignid = $assidarray[$c];
            $gid      = $gradeitemid[$c]; // {assign_grades}.id

            // FUTURE-DATE CHECK
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
                       AND gi.itemmodule   = 'assign'
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
    } // End foreach

    // After all updates, redirect with success or error indicator
    $sort     = (empty($_POST['sort']))     ? 1 : $_POST['sort'];
    $sorttype = (empty($_POST['sorttype'])) ? '' : $_POST['sorttype'];
    $page     = (!empty($_POST['page']))    ? $_POST['page'] : 1;
    $showall  = (!empty($_REQUEST['showallstudent'])) ? $_REQUEST['showallstudent'] : 0;

    if ($u1 == true || $u2 == true) {
        $urltogo = $CFG->wwwroot."/admin/tool/timestamp/index.php?"
                   ."page={$page}&update=1&sort={$sort}&sorttype={$sorttype}"
                   ."&showallstudent={$showall}";
    } else {
        $urltogo = $CFG->wwwroot."/admin/tool/timestamp/index.php?"
                   ."page={$page}&update=0&sort={$sort}&sorttype={$sorttype}"
                   ."&showallstudent={$showall}";
    }

    echo '<div style="padding-left: 38px;">'
        .'<img src="'.$CFG->wwwroot.'/admin/tool/timestamp/templates/images/loader.gif" border="0">'
        .'</div>';
    echo '<script>window.location.href="'.$urltogo.'";</script>';
    exit;
}

/** =========================================================================
 *  3) Show success/error messages if ?update=1 or ?update=0
 *  =========================================================================*/
if (isset($_GET['update'])) {
    if ($_GET['update'] == 1) {
        echo '<div class="success-msg"><i class="fa fa-check"></i> '
            .'The data has been successfully updated!</div>';
    } else {
        echo '<div class="error-msg"><i class="fa fa-times-circle"></i> '
            .'Some error occured! Please try again later or contact support.</div>';
    }
}

/** =========================================================================
 *  4) Sorting logic (maintain session)
 *  =========================================================================*/
if (isset($_GET['sorttype'])) {
    // If sorting by submission date
    if ($_GET['sorttype'] == "submission") {
        if ($_GET['sort'] == 1) {
            $SESSION->sortorder = " ORDER BY x.timemodified ASC";
            $SESSION->sortval   = 2;
            $SESSION->sorttype  = "submission";
        } else {
            $SESSION->sortorder = " ORDER BY x.timemodified DESC";
            $SESSION->sortval   = 1;
            $SESSION->sorttype  = "submission";
        }
    // If sorting by grade date
    } else if ($_GET['sorttype'] == "grades") {
        if ($_GET['sort'] == 1) {
            $SESSION->sortorder = " ORDER BY g.timemodified ASC";
            $SESSION->sortval   = 2;
            $SESSION->sorttype  = "grades";
        } else {
            $SESSION->sortorder = " ORDER BY g.timemodified DESC";
            $SESSION->sortval   = 1;
            $SESSION->sorttype  = "grades";
        }
    // If sorting by assignment name
    } else if ($_GET['sorttype'] == "assignment") {
        if ($_GET['sort'] == 1) {
            $SESSION->sortorder = " ORDER BY z.name ASC";
            $SESSION->sortval   = 2;
            $SESSION->sorttype  = "assignment";
        } else {
            $SESSION->sortorder = " ORDER BY z.name DESC";
            $SESSION->sortval   = 1;
            $SESSION->sorttype  = "assignment";
        }
    }
} else {
    // Default if none set
    if (empty($SESSION->sortorder)) {
        $SESSION->sortorder = " ORDER BY x.id DESC";
        $SESSION->sortval   = 1;
        $SESSION->sorttype  = "";
    }
}

// For toggling sort direction in the UI:
if (@$SESSION->sortval == 1) {
    $sortvalold = 2;
} else {
    $sortvalold = 1;
}

/** =========================================================================
 *  5) Pagination setup
 *  =========================================================================*/
if (isset($_REQUEST["page"])) {
    $page = (int)$_REQUEST["page"];
} else {
    $page = 1;
}
$setLimit  = 10; // 10 records per page
$pageLimit = ($page * $setLimit) - $setLimit;

/** =========================================================================
 *  6) Build "Auto-Complete" strings
 *  =========================================================================*/
// For courses
$list_all_courses = $DB->get_records_sql("
    SELECT id AS courseid, fullname AS coursename
      FROM {course}
     WHERE 1
");
$coursestr = '';
foreach ($list_all_courses as $course) {
    $coursestr .= '"'.$course->coursename."|".$course->courseid.'",';
}
$coursestr = "[".$coursestr."]";

// For students (by name)
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

// For students (by username)
$list_all_students_username = $DB->get_records_sql("
    SELECT u.username, u.id AS studentuserid
      FROM {user} u
");
$studentsstrusername = '';
foreach ($list_all_students_username as $u) {
    $studentsstrusername .= '"'.$u->username."|".$u->studentuserid.'",';
}
$studentsstrusername = "[".$studentsstrusername."]";

// For assignments
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
 *  7) Handle searching/filtering
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

// "Show all" => reset everything
if (isset($_GET['showall']) && $_GET['showall'] == '1') {
    $SESSION->courseid        = '';
    $SESSION->studentid       = '';
    $SESSION->studentuserid   = '';
    $SESSION->assessmentid    = '';
    $SESSION->studentname     = '';
    $SESSION->studentusername = '';
    $SESSION->coursename      = '';
    $SESSION->assessmentname  = '';
    $SESSION->sortorder       = " ORDER BY x.id DESC";
    $SESSION->sortval         = 1;
}

/** =========================================================================
 *  8) Build conditions for the assignment lookup
 *     (We will fetch assignments first, then fetch attempts per assignment)
 *  =========================================================================*/
$assign_conditions = [];
$assign_params = [];

// If filtering by course:
if (!empty($SESSION->courseid)) {
    $assign_conditions[] = "course = :cid";
    $assign_params['cid'] = $SESSION->courseid;
}
// If filtering by a specific assignment:
if (!empty($SESSION->assessmentid)) {
    $assign_conditions[] = "id = :aid";
    $assign_params['aid'] = $SESSION->assessmentid;
}

// Combine into WHERE clause.
$assign_where = '';
if (!empty($assign_conditions)) {
    $assign_where = "WHERE " . implode(" AND ", $assign_conditions);
}

// If user specifically chose to sort by assignment name, handle that here:
$assignmentsort = '';
if (!empty($SESSION->sorttype) && $SESSION->sorttype == 'assignment') {
    // If the user toggled ascending/descending:
    if (!empty($SESSION->sortval) && $SESSION->sortval == 1) {
        $assignmentsort = "ORDER BY name DESC"; // sort=1 => desc
    } else {
        $assignmentsort = "ORDER BY name ASC";  // sort=2 => asc
    }
} else {
    // Default fallback if not sorting by assignment name:
    $assignmentsort = "ORDER BY id DESC";
}

// Final assignment SQL
$assign_sql = "SELECT * FROM {assign} {$assign_where} {$assignmentsort}";
$allassignments = $DB->get_records_sql($assign_sql, $assign_params);

/** =========================================================================
 *  9) For each assignment, fetch attempts (submissions)
 *  =========================================================================*/
$arr      = []; // nested array by assignment => attempts
$list_all = []; // to gather all attempts for counting/pagination.

foreach ($allassignments as $assignment) {

    $assignmentid = $assignment->id;

    // Attempt query:
    $attemptsql = "
        SELECT
            x.id AS rowid,
            x.userid,
            x.timemodified,
            x.assignment,
            x.attemptnumber AS attemptnum,
            y.firstname,
            y.lastname,
            y.id AS studentid,
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
           AND g.userid     = x.userid
           AND g.attemptnumber = x.attemptnumber
     LEFT JOIN {user} us ON g.grader = us.id
         WHERE x.assignment = :assignmentid
    ";
    $attemptparams = ['assignmentid' => $assignmentid];

    // Append student filters (by ID or username) if set:
    if (!empty($SESSION->studentid)) {
        $attemptsql .= " AND x.userid = :stuid ";
        $attemptparams['stuid'] = $SESSION->studentid;
    } else if (!empty($SESSION->studentuserid)) {
        $attemptsql .= " AND x.userid = :stuusernameid ";
        $attemptparams['stuusernameid'] = $SESSION->studentuserid;
    }

    // Add the existing sort order (e.g. submission date, grade date)
    if (!empty($SESSION->sortorder)) {
        $attemptsql .= " {$SESSION->sortorder} ";
    } else {
        $attemptsql .= " ORDER BY x.id DESC ";
    }

    $theseattempts = $DB->get_records_sql($attemptsql, $attemptparams);

    // Collect them into $list_all (for total counting).
    $list_all = array_merge($list_all, array_values($theseattempts));

    // Build a nested structure for this assignment:
    if (!isset($arr[$assignmentid])) {
        $arr[$assignmentid] = [
            'assignment' => $assignment,
            'attempts'   => []
        ];
    }

    foreach ($theseattempts as $at) {
        $arr[$assignmentid]['attempts'][] = $at;
    }
}

/** =========================================================================
 * 10) If not "show all," apply pagination slicing at the attempt level
 *  =========================================================================*/
$totalattempts = count($list_all);

if ($showallstudent == 1) {
    $SESSION->showallstudent = 1;
} else {
    $SESSION->showallstudent = 0;

    // Flatten everything
    $flattened = [];
    foreach ($arr as $aid => $block) {
        foreach ($block['attempts'] as $attempt) {
            $flattened[] = ['assignmentid' => $aid, 'attempt' => $attempt];
        }
    }
    // Slice for pagination
    $paged_flat = array_slice($flattened, $pageLimit, $setLimit);

    // Rebuild nested array
    $paged_arr = [];
    foreach ($paged_flat as $f) {
        $aid  = $f['assignmentid'];
        $at   = $f['attempt'];
        if (!isset($paged_arr[$aid])) {
            $paged_arr[$aid] = [
                'assignment' => $arr[$aid]['assignment'],
                'attempts'   => []
            ];
        }
        $paged_arr[$aid]['attempts'][] = $at;
    }

    $arr = $paged_arr;
}

/** =========================================================================
 * 11) Prepare final data for the renderer (including assignment link)
 *  =========================================================================*/
$renderarray = [];

foreach ($arr as $aid => $block) {
    $assignment = $block['assignment'];
    $attempts   = $block['attempts'];

    // Attempt to find the coursemodule for linking
    // (If assignment->course is set, we can do:)
    $cm = get_coursemodule_from_instance('assign', $assignment->id, $assignment->course, false, IGNORE_MISSING);

    // Build the assignment link (if found)
    if ($cm) {
        $assignmenturl = $CFG->wwwroot.'/mod/assign/view.php?id='.$cm->id;
    } else {
        // fallback if not found
        $assignmenturl = '#';
    }

    // We'll hold the linked name:
    $assignmentlinkedname = '<a href="'.$assignmenturl.'" target="_blank">'
                           .format_string($assignment->name).'</a>';

    foreach ($attempts as $row) {
        // Format submission date
        $submissiondate = (!empty($row->timemodified))
            ? date("F j, Y, g:i a", $row->timemodified)
            : 'NA';

        // Format grade date
        $gradedate = (!empty($row->gtimemodified))
            ? date("F j, Y, g:i a", $row->gtimemodified)
            : 'NA';

        // Grader name
        $gradername = (!empty($row->graderid))
            ? ($row->graderfirstname.' '.$row->graderlastname)
            : 'NA';

        // Attempt # (db is 0-based)
        $displayattempt = 'Attempt '.($row->attemptnum + 1);

        // If grade date doesn't exist, disable update field
        $grade_exists = (!empty($row->gtimemodified)) ? '' : 'disabled';

        // Build final row array
        $renderarray[] = [
            'baseurl'         => $CFG->wwwroot,
            'rowid'           => $row->rowid,
            'userid'          => $row->userid,
            'name'            => $row->firstname.' '.$row->lastname,
            // Use our assignment hyperlink
            'assignmentname'  => $assignmentlinkedname,
            'assignmentid'    => $assignment->id,
            'timemodified'    => $submissiondate,
            'timemodifiedg'   => $gradedate,
            'gradeexists'     => $grade_exists,
            'gradername'      => $gradername,
            'graderid'        => $row->graderid,
            'gradeitemid'     => $row->gradeitemid,
            'attemptnum'      => $displayattempt
        ];
    }
}

/** =========================================================================
 * 12) Pass data to your renderable class
 *  =========================================================================*/
if ($SESSION->sortval == 1) {
    $sortvalold = 2;
} else {
    $sortvalold = 1;
}

$renderable = new \tool_timestamp\output\index_page(
    $renderarray,
    $coursestr,
    $assessmentstr,
    $studentsstr,
    $studentsstrusername,
    $page,
    $totalattempts,             // total records for pagination
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
 * 13) Show pagination if not "show all"
 *  =========================================================================*/
if ($showallstudent != 1) {
    echo '<br/><br/>';
    echo displayPaginationHere($totalattempts, $setLimit, $page);
}

echo $OUTPUT->footer();
