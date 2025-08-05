<?php
// settings.php — register your index & export pages in the admin menu
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    // Top-level “Timestamp” under Site administration → Tools
    $ADMIN->add('tools', new admin_category(
        'tooltimestamp',
        get_string('pluginname', 'tool_timestamp')
    ));

    // View/filter UI
    $ADMIN->add('tooltimestamp', new admin_externalpage(
        'tooltimestampindex',
        get_string('pluginname', 'tool_timestamp'),
        new moodle_url('/admin/tool/timestamp/index.php')
    ));

    // Export to Excel
    $ADMIN->add('tooltimestamp', new admin_externalpage(
        'tooltimestampexport',
        get_string('exportpage', 'tool_timestamp'),
        new moodle_url('/admin/tool/timestamp/download.php')
    ));
}

// Export all Data to Excel
if ($hassiteconfig) {
    $ADMIN->add('tooltimestamp', new admin_externalpage(
        'tooltimestampexportall',
        get_string('exportallpage', 'tool_timestamp'),
        new moodle_url('/admin/tool/timestamp/download_all.php')
    ));
}
