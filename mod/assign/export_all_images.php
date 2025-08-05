<?php
// export_images.php
// Export all image submissions for one student across courses.
// ZIP layout: StudentName/CourseShortname/image.jpg

// 0) Disable PHP/Apache output compression for a clean binary stream
@apache_setenv('no-gzip', 1);
@ini_set('zlib.output_compression', 'Off');

// 1) Bootstrap Moodle & File API
require_once('../../config.php');
require_once($CFG->libdir . '/filelib.php');

// 2) Param & permissions
$studentid = required_param('studentid', PARAM_INT);
require_login();
$syscontext = context_system::instance();
require_capability('moodle/site:viewparticipants', $syscontext);

// Return URL on error
$returnurl = new moodle_url(
    '/grade/report/overview/studentgradeprogressadmin.php',
    ['userid' => $studentid]
);

// 3) Load student for folder naming
$user = $DB->get_record('user',
    ['id' => $studentid],
    'firstname,lastname',
    IGNORE_MISSING
);
if (!$user) {
    redirect($returnurl,
        get_string('invaliduserid','assign'),
        null,
        \core\output\notification::NOTIFY_ERROR
    );
}
$studentfolder = preg_replace('/[^A-Za-z0-9_-]/','_',
    "{$user->firstname}_{$user->lastname}"
);

// 4) Gather all image files for this student
$fs    = get_file_storage();
$files = [];

// 4a) All courses enrolled
$courses = enrol_get_users_courses($studentid, true, 'id,shortname');
foreach ($courses as $course) {
    $coursefolder = preg_replace('/[^A-Za-z0-9_-]/','_',$course->shortname);

    // 4b) All assign activities
    $assigns = $DB->get_records('assign',['course'=>$course->id]);
    foreach ($assigns as $assign) {
        if (!$cm = get_coursemodule_from_instance('assign',$assign->id,$course->id)) {
            continue;
        }
        $ctx = context_module::instance($cm->id);

        // 4c) This student’s submission
        $submission = $DB->get_record('assign_submission',[
            'assignment'=>$assign->id,'userid'=>$studentid
        ], '*', IGNORE_MISSING);
        if (!$submission) {
            continue;
        }

        // 4d) Files in submission_files area
        $stored = $fs->get_area_files(
            $ctx->id,
            'assignsubmission_file',
            'submission_files',
            $submission->id,
            '', false
        );
        foreach ($stored as $sf) {
            if (strpos($sf->get_mimetype(),'image/')!==0) {
                continue;
            }
            $relpath = "{$studentfolder}/{$coursefolder}/".$sf->get_filename();
            $files[$relpath] = $sf;
        }
    }
}

if (empty($files)) {
    redirect($returnurl,
        get_string('nothingtodo','assign'),
        null,
        \core\output\notification::NOTIFY_WARNING
    );
}

// 5) Create the ZIP on disk via Moodle’s packer
$tempzip = tempnam(sys_get_temp_dir(),'stuimg_');
$packer = get_file_packer('application/zip');
try {
    $packer->archive_to_pathname($files, $tempzip);
} catch (Exception $e) {
    @unlink($tempzip);
    redirect($returnurl,
        get_string('zipunavailable','assign'),
        null,
        \core\output\notification::NOTIFY_ERROR
    );
}

// 6) Stream the ZIP manually
while (ob_get_level()) {
    ob_end_clean();
}

$filename = "images_{$studentfolder}.zip";
$filesize = filesize($tempzip) ?: 0;

header('Content-Description: File Transfer');
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="'.basename($filename).'"');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: '.$filesize);

readfile($tempzip);

// 7) Cleanup & exit
unlink($tempzip);
exit;
