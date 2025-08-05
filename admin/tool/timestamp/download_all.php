<?php
// download_all.php — export ALL timestamp data to CSV (with UTF-8 BOM)

// -----------------------------------------------------------------------------
// 1) Bootstrap Moodle environment
//    - Load the main config to set up $CFG, database connections, etc.
//    - Ensure this file is only called from within Moodle.
// -----------------------------------------------------------------------------
require_once(__DIR__ . '/../../../config.php');
defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/adminlib.php');

// -----------------------------------------------------------------------------
// 2) Security checks
//    - Ensure the user is logged in.
//    - Ensure the user has the ‘site:config’ capability (typically admins).
// -----------------------------------------------------------------------------
require_login();
require_capability('moodle/site:config', context_system::instance());

// -----------------------------------------------------------------------------
// 3) Clear any existing output buffers
//    - This prevents any accidental whitespace or output from breaking CSV headers.
// -----------------------------------------------------------------------------
while (ob_get_level()) {
    ob_end_clean();
}

// -----------------------------------------------------------------------------
// 4) Prepare and execute SQL query (ignoring any filters)
//    - Join submissions to assignments, courses, users, grades, and graders.
//    - Order by submission record ID ascending.
// -----------------------------------------------------------------------------
global $DB;
$sql = "
    SELECT
      x.id            AS rowid,
      c.fullname      AS coursename,
      y.firstname     AS firstname,
      y.lastname      AS lastname,
      z.name          AS assignmentname,
      x.timemodified  AS submissiontime,
      g.timemodified  AS gradetime,
      us.firstname    AS graderfirstname,
      us.lastname     AS graderlastname,
      x.attemptnumber AS attemptnum
    FROM {assign_submission} x
    LEFT JOIN {assign}        z ON x.assignment = z.id
    LEFT JOIN {course}        c ON z.course     = c.id
    LEFT JOIN {user}          y ON x.userid     = y.id
    LEFT JOIN {assign_grades} g
           ON g.assignment    = x.assignment
          AND g.userid        = x.userid
          AND g.attemptnumber = x.attemptnumber
    LEFT JOIN {user}         us ON g.grader      = us.id
    ORDER BY x.id ASC
";
$records = $DB->get_records_sql($sql, []);

// -----------------------------------------------------------------------------
// 5) Send HTTP headers for CSV download
//    - Tell the browser this is a UTF-8 CSV attachment.
//    - Provide a filename with a timestamp.
// -----------------------------------------------------------------------------
$filename = 'timestamp_export_all_' . date('Ymd_His') . '.csv';
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

// -----------------------------------------------------------------------------
// 6) Emit UTF-8 BOM
//    - Ensures that Excel recognizes the file as UTF-8 and displays characters correctly.
// -----------------------------------------------------------------------------
echo "\xEF\xBB\xBF";

// -----------------------------------------------------------------------------
// 7) Stream CSV content
//    - Open the PHP output stream as a file handle.
//    - Write header row (column labels).
//    - Loop through each record and write one CSV row per submission.
// -----------------------------------------------------------------------------
$fh = fopen('php://output', 'w');

// Write column headers
fputcsv($fh, [
    get_string('coursename',     'tool_timestamp'),
    get_string('studentname',    'tool_timestamp'),
    get_string('assignment',     'tool_timestamp'),
    get_string('submissiontime', 'tool_timestamp'),
    get_string('gradetime',      'tool_timestamp'),
    get_string('gradername',     'tool_timestamp'),
    get_string('attempt',        'tool_timestamp'),
]);

// Write data rows
foreach ($records as $r) {
    fputcsv($fh, [
        // Course name
        $r->coursename,
        // Student full name
        $r->firstname . ' ' . $r->lastname,
        // Assignment name
        $r->assignmentname,
        // Submission timestamp (formatted) or 'NA' if missing
        $r->submissiontime
            ? userdate($r->submissiontime, '%Y-%m-%d %H:%M:%S')
            : 'NA',
        // Grade timestamp (formatted) or 'NA' if missing
        $r->gradetime
            ? userdate($r->gradetime, '%Y-%m-%d %H:%M:%S')
            : 'NA',
        // Grader full name or 'NA'
        trim($r->graderfirstname . ' ' . $r->graderlastname) ?: 'NA',
        // Attempt number (1-based)
        'Attempt ' . ($r->attemptnum + 1),
    ]);
}

// -----------------------------------------------------------------------------
// 8) Clean up and exit
//    - Close the output stream handle.
//    - End script execution.
// -----------------------------------------------------------------------------
fclose($fh);
exit;
