<?php
// download.php — export filtered timestamp data to CSV

// 1) Bootstrap Moodle
require_once(__DIR__ . '/../../../config.php');
defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/adminlib.php');

// 2) Security
require_login();
require_capability('moodle/site:config', context_system::instance());

// 3) Clear buffers so headers will work
while (ob_get_level()) {
    ob_end_clean();
}

global $SESSION, $DB;

// 4) Build WHERE & params from your existing session‐based filters
$where  = [];
$params = [];

if (!empty($SESSION->courseid)) {
    $where[]            = 'z.course = :courseid';
    $params['courseid'] = $SESSION->courseid;
}
if (!empty($SESSION->studentid)) {
    $where[]             = 'x.userid = :studentid';
    $params['studentid'] = $SESSION->studentid;
}
if (!empty($SESSION->studentuserid)) {
    $where[]                = 'x.userid = :studentuserid';
    $params['studentuserid'] = $SESSION->studentuserid;
}
if (!empty($SESSION->assessmentid)) {
    $where[]                 = 'z.id = :assessmentid';
    $params['assessmentid']   = $SESSION->assessmentid;
}

// If no filters and not “show all”, stop.
$showall = optional_param('showall', 0, PARAM_INT);
if (empty($where) && !$showall) {
    print_error('nofilters', 'tool_timestamp');
}

// 5) Build WHERE clause
$where_sql = '';
if (!$showall && !empty($where)) {
    $where_sql = ' WHERE ' . implode(' AND ', $where);
}

// 6) Sort order
$sortorder = !empty($SESSION->sortorder)
    ? $SESSION->sortorder
    : ' ORDER BY x.id DESC';

// 7) **SELECT x.id first** so each row has a unique key
$sql = "
    SELECT
      x.id            AS rowid,
      x.userid,
      x.timemodified,
      x.assignment,
      x.attemptnumber AS attemptnum,
      y.firstname,
      y.lastname,
      us.firstname    AS graderfirstname,
      us.lastname     AS graderlastname,
      z.name          AS assignmentname,
      g.timemodified  AS gtimemodified
    FROM {assign_submission} x
    LEFT JOIN {user} y    ON x.userid = y.id
    LEFT JOIN {assign} z  ON x.assignment = z.id
    LEFT JOIN {assign_grades} g
           ON g.assignment    = x.assignment
          AND g.userid        = x.userid
          AND g.attemptnumber = x.attemptnumber
    LEFT JOIN {user} us   ON g.grader = us.id
    {$where_sql}
    {$sortorder}
";

// 8) Fetch all matching records
$records = $DB->get_records_sql($sql, $params);

// 9) Send CSV headers
$filename = 'timestamp_export_' . date('Ymd_His') . '.csv';
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$fh = fopen('php://output', 'w');

// 10) Column headers
fputcsv($fh, [
    get_string('studentname',    'tool_timestamp'),
    get_string('assignment',     'tool_timestamp'),
    get_string('submissiontime', 'tool_timestamp'),
    get_string('gradetime',      'tool_timestamp'),
    get_string('gradername',     'tool_timestamp'),
    get_string('attempt',        'tool_timestamp'),
]);

// 11) Output each row
foreach ($records as $row) {
    $submission = $row->timemodified
        ? userdate($row->timemodified, '%Y-%m-%d %H:%M:%S')
        : 'NA';
    $graded = $row->gtimemodified
        ? userdate($row->gtimemodified, '%Y-%m-%d %H:%M:%S')
        : 'NA';
    $grader = trim($row->graderfirstname . ' ' . $row->graderlastname) ?: 'NA';
    $attempt = 'Attempt ' . ($row->attemptnum + 1);

    fputcsv($fh, [
        $row->firstname . ' ' . $row->lastname,
        $row->assignmentname,
        $submission,
        $graded,
        $grader,
        $attempt
    ]);
}

fclose($fh);
exit;
