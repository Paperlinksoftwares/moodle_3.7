<?php

/**
 * attendanceregister_tracked_users.class.php - Class containing Attendance Register's tracked Users and their summaries
 *
 * @package    mod
 * @subpackage attendanceregister
 * @version $Id
 * @author Lorenzo Nicora <fad@nicus.it>
 *
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Holds all tracked Users of an Attendance Register
 *
 * Implements method to return html_table to render it.
 *
 * @author nicus
 */
class attendanceregister_tracked_users {

    /**
     * Array of User
     */
    public $users;

    /**
     * Array if attendanceregister_user_aggregates_summary
     * keyed by $userId
     */
    public $usersSummaryAggregates;


    /**
     * Instance of attendanceregister_tracked_courses
     * containing all tracked Courses
     * @var type
     */
    public $trackedCourses;

    /**
     * Ref. to AttendanceRegister instance
     */
    private $register;

    /**
     * Ref to mod_attendanceregister_user_capablities instance
     */
    private $userCapabilites;


    /**
     * Constructor
     * Load all tracked User's and their summaris
     * Load list of tracked Courses
     * @param object $register
     * @param attendanceregister_user_capablities $userCapabilities
     */
    function __construct($register, attendanceregister_user_capablities $userCapabilities) { global $DB;
        $this->register = $register;
        $this->userCapabilities = $userCapabilities;
        $this->users = attendanceregister_get_tracked_users($register);
        $this->trackedCourses = new attendanceregister_tracked_courses($register);

        $trackedUsersIds = attendanceregister__extract_property($this->users, 'id');

        // Retrieve Aggregates summaries
      //  if(isset($_GET['searchbydate']) && $_GET['searchbydate']==1 && ($_GET['from_date']!='' || $_GET['to_date']!=''))
        //{
          //  $aggregates = attendanceregister__get_all_users_aggregate_summaries_search($register,strtotime($_GET['from_date']),strtotime($_GET['to_date']));
        //}
        //else
        //{
            $aggregates = attendanceregister__get_all_users_aggregate_summaries($register);
        //}
//        echo "<pre>";
//        print_r($aggregates); 
        if(isset($_GET['searchbydate']) && $_GET['searchbydate']==1 && ($_GET['from_date']!='' || $_GET['to_date']!='')) {
        $p=1;
        foreach($aggregates as $agg)
        {
            if($_GET['from_date']!='' && $_GET['to_date']=='')
            {
                $sql_search_record = $DB->get_record_sql("SELECT SUM(`duration`) as totalduration FROM {attendanceregister_session} as b WHERE b.`userid` = '".$agg->userid."' AND b.`register` = '".$register->id."' AND b.`login` >= '".strtotime($_GET['from_date'])."'");
            //print ($sql_search_record->totalduration);
             $sql_record_lastsessionend = $DB->get_record_sql("SELECT MAX(`logout`) as endsession FROM {attendanceregister_session} as b WHERE b.`userid` = '".$agg->userid."' AND b.`register` = '".$register->id."' AND b.`login` >= '".strtotime(@$_GET['from_date'])."'");
            
            }
            else if($_GET['from_date']=='' && $_GET['to_date']!='')
            {
                $sql_search_record = $DB->get_record_sql("SELECT SUM(`duration`) as totalduration FROM {attendanceregister_session} as b WHERE b.`userid` = '".$agg->userid."' AND b.`register` = '".$register->id."' AND b.`logout` <= '".strtotime($_GET['to_date'])."'");
            //print ($sql_search_record->totalduration);
             $sql_record_lastsessionend = $DB->get_record_sql("SELECT MAX(`logout`) as endsession FROM {attendanceregister_session} as b WHERE b.`userid` = '".$agg->userid."' AND b.`register` = '".$register->id."' AND b.`logout` <= '".strtotime(@$_GET['to_date'])."'");
            
            }
            else if($_GET['from_date']!='' && $_GET['to_date']!='')
            {
                $sql_search_record = $DB->get_record_sql("SELECT SUM(`duration`) as totalduration FROM {attendanceregister_session} as b WHERE b.`userid` = '".$agg->userid."' AND b.`register` = '".$register->id."' AND b.`login` >= '".strtotime($_GET['from_date'])."' AND b.`logout` <= '".strtotime($_GET['to_date'])."'");
            //print ($sql_search_record->totalduration);
             $sql_record_lastsessionend = $DB->get_record_sql("SELECT MAX(`logout`) as endsession FROM {attendanceregister_session} as b WHERE b.`userid` = '".$agg->userid."' AND b.`register` = '".$register->id."' AND b.`login` >= '".strtotime($_GET['from_date'])."' AND b.`logout` <= '".strtotime($_GET['to_date'])."'");
            
            }
            else
                
            {
                
            }
            //$sql_search_record = $DB->get_record_sql("SELECT SUM(`duration`) as totalduration FROM {attendanceregister_session} as b WHERE b.`userid` = '".$agg->userid."' AND b.`register` = '".$register->id."' AND b.`login` >= '".strtotime($_GET['from_date'])."' AND b.`logout` <= '".strtotime($_GET['to_date'])."'");
            //print ($sql_search_record->totalduration);
            // $sql_record_lastsessionend = $DB->get_record_sql("SELECT MAX(`logout`) as endsession FROM {attendanceregister_session} as b WHERE b.`userid` = '".$agg->userid."' AND b.`register` = '".$register->id."' AND b.`login` >= '".strtotime(@$_GET['from_date'])."' AND b.`logout` <= '".strtotime(@$_GET['to_date'])."'");
            
            if($sql_search_record->totalduration!='')
            {
                //$aggregates[$p]->duration = new stdClass();
                $aggregates[$agg->id]->duration = $sql_search_record->totalduration;
            }
            else
            {
               // $aggregates[$p]->duration = new stdClass();
                $aggregates[$agg->id]->duration = '0';
            }
            if($sql_record_lastsessionend->endsession!='')
            {
                //$aggregates[$p]->duration = new stdClass();
                $aggregates[$agg->id]->lastsessionlogout = $sql_record_lastsessionend->endsession;
            }
            else
            {
               // $aggregates[$p]->duration = new stdClass();
                $aggregates[$agg->id]->lastsessionlogout = '';
            }
            $p++;
        } }
//        echo "<pre>";
//        print_r($aggregates); 
        // Remap in an array of attendanceregister_user_aggregates_summary, mapped by userId
        $this->usersSummaryAggregates = array();
        foreach ($aggregates as $aggregate) {
            // Retain only tracked users
            if ( in_array( $aggregate->userid, $trackedUsersIds) ) {
                // Create User's attendanceregister_user_aggregates_summary instance if not exists
                if ( !isset( $this->usersSummaryAggregates[ $aggregate->userid ] )) {
                    $this->usersSummaryAggregates[ $aggregate->userid ] = new attendanceregister_user_aggregates_summary();
                }
                // Populate attendanceregister_user_aggregates_summary fields
                if( $aggregate->grandtotal ) {
                    $this->usersSummaryAggregates[ $aggregate->userid ]->grandTotalDuration = $aggregate->duration;
                    $this->usersSummaryAggregates[ $aggregate->userid ]->lastSassionLogout = $aggregate->lastsessionlogout;
                } else if ( $aggregate->total && $aggregate->onlinesess == 1 ) {
                $this->usersSummaryAggregates[ $aggregate->userid ]->onlineTotalDuration = $aggregate->duration;
                } else if ( $aggregate->total && $aggregate->onlinesess == 0 ) {
                    $this->usersSummaryAggregates[ $aggregate->userid ]->offlineTotalDuration = $aggregate->duration;
                }
            }
        }
    }

    /**
     * Build the html_table object to represent details
     * @return html_table
     */
    public function html_table() {
        
        global $OUTPUT, $doShowPrintableVersion;

        $strNotAvail = get_string('notavailable');

        $table = new html_table();
        $table->attributes['class'] .= ' attendanceregister_userlist table table-condensed table-bordered table-striped table-hover';

        /// Header

        $table->head = array(
            get_string('count', 'attendanceregister'),
            get_string('fullname', 'attendanceregister'),
            get_string('total_time_online', 'attendanceregister'),
            get_string('last_session_logout', 'attendanceregister'),
           // get_string('edit_total_time_online', 'attendanceregister'),
           // get_string('edit_last_session_end', 'attendanceregister'),
            get_string('action', 'attendanceregister')
        );
        $table->align = array('left', 'left', 'right');

        if ( $this->register->offlinesessions ) {
            $table->head[] = get_string('total_time_offline', 'attendanceregister');
            $table->align[] = 'right';
            $table->head[] = get_string('grandtotal_time', 'attendanceregister');
            $table->align[] = 'right';
        }

       // $table->head[] = get_string('last_session_logout', 'attendanceregister');
        //$table->align[] = 'left';


        /// Table Rows

        if( $this->users ) { //echo '<pre>'; print_r($this->usersSummaryAggregates); die;
            $rowcount = 0;
            foreach ($this->users as $user) {
                $rowcount++;

                $userAggregate = null;
                if ( isset( $this->usersSummaryAggregates[$user->id] ) ) {
                    $userAggregate = $this->usersSummaryAggregates[$user->id];
                }

                // Basic columns
               // $linkUrl = attendanceregister_makeUrl($this->register, $user->id);
                //$linkUrl = 'update.php?registerid='.$_GET['registerid'].'&userid='.$_GET['userid'];
                $fullnameWithLink = '<a href="#" onclick=\'javascript: redirectSession('.$user->id.');\' title="'.fullname($user).'">' . fullname($user) . '</a>';
                
                $onlineDuration = ($userAggregate)?( $userAggregate->onlineTotalDuration ):( null );
                $onlineDurationStr =  attendanceregister_format_duration($onlineDuration );
                
                
                $tableRow = new html_table_row( array( $rowcount, $fullnameWithLink, $onlineDurationStr) );

                // Add class for zebra stripes
                $tableRow->attributes['class'] .= (  ($rowcount % 2)?' attendanceregister_oddrow':' attendanceregister_evenrow' );

                // Optional columns
                if ( $this->register->offlinesessions ) {
                    $offlineDuration = ($userAggregate)?($userAggregate->offlineTotalDuration):( null );
                    $offlineDurationStr = attendanceregister_format_duration($offlineDuration);
                    $tableCell = new html_table_cell( $offlineDurationStr );
                    $tableRow->cells[] = $tableCell;

                    $grandtotalDuration = ($userAggregate)?($userAggregate->grandTotalDuration ):( null );
                    $grandtotalDurationStr = attendanceregister_format_duration($grandtotalDuration);
                    $tableCell = new html_table_cell( $grandtotalDurationStr );
                    $tableRow->cells[] = $tableCell;
                }

                $lastSessionLogoutStr = ($userAggregate)?( attendanceregister__formatDateTime( $userAggregate->lastSassionLogout ) ):( get_string('no_session','attendanceregister') );
                //$edit1 =  '<input type="text" name="duration'.$rowcount.'" id="duration'.$rowcount.'" class="newdate1" value="" size="16" />';
               //// $edit1 = '<input type="number" name="days'.$rowcount.'" id="days'.$rowcount.'" value="" min="0" style="width: 55px;" placeholder="Days">'
                    //    . '&nbsp;<input type="number" name="hours'.$rowcount.'" id="hours'.$rowcount.'" value="" min="0" style="width: 59px;" placeholder="Hours">'
                     //   . '&nbsp;<input type="number" name="minutes'.$rowcount.'" id="minutes'.$rowcount.'" value="" min="0" style="width: 70px;" placeholder="Minutes">'
                     //   . '&nbsp;<input type="number" name="seconds'.$rowcount.'" id="seconds'.$rowcount.'" value="" min="0" style="width: 69px;" placeholder="Seconds" />';
              //  $edit2 =  '<input type="text" name="newdate'.$rowcount.'" id="newdate'.$rowcount.'" value="" class="newdate1" size="16" />';
                
                $updatebutton =  '<input type="button" name="update" id="update" value=" View / Update sessions " onclick=\'javascript: redirectSession('.$user->id.');\'   />';
                $addbutton =  '<input type="button" class="create-session" value="Add session" onclick=\'javascript: document.getElementById("userid").value='.$user->id.';\'>';
                $tableCell = new html_table_cell( $lastSessionLogoutStr );
              //  $tableCell1 = new html_table_cell( $edit1 );
               // $tableCell2 = new html_table_cell( $edit2 );
                $tableCell3 = new html_table_cell( $updatebutton."&nbsp;&nbsp;".$addbutton );
                //$tableCell4 = new html_table_cell( $addbutton );
                
                 $tableRow->cells[] = $tableCell;
//$tableRow->cells[] = $tableCell1;
//$tableRow->cells[] = $tableCell2;
$tableRow->cells[] = $tableCell3;
//$tableRow->cells[] = $tableCell4;

                $table->data[] = $tableRow;
            }
        } else {
            // No User
            $row = new html_table_row();
            $labelCell = new html_table_cell();
            $labelCell->colspan = count($table->head);
            $labelCell->text = get_string('no_tracked_user', 'attendanceregister');
            $row->cells[] = $labelCell;
            $table->data[] = $row;
        }

        return $table;
    }
}

