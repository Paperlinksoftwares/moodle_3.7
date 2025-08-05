<?php defined('BASEPATH') OR exit('No direct script access allowed');

/* ----------------------------------------------------------------------------
 * Easy!Appointments - Open Source Web Scheduler
 *
 * @package     EasyAppointments
 * @author      A.Tselegidis <alextselegidis@gmail.com>
 * @copyright   Copyright (c) 2013 - 2018, Alex Tselegidis
 * @license     http://opensource.org/licenses/GPL-3.0 - GPLv3
 * @link        http://easyappointments.org
 * @since       v1.0.0
 * ---------------------------------------------------------------------------- */

/**
 * User Model
 *
 * Contains current user's methods.
 *
 * @package Models
 */
class Unitrequest_Model extends CI_Model {
    /**
     * Returns the user from the database for the "settings" page.
     *
     * @param int $user_id User record id.
     *
     * @return array Returns an array with user data.
     *
     * @todo Refactor this method as it does not do as it states.
     */
    public function get_info($user_id)
    {
        

$this->db->select('*');
$this->db->from('mdl_booking_unit_request');
$this->db->where('user_id', $user_id);
$this->db->order_by('id', 'desc');
$this->db->limit(1);
$query = $this->db->get();
//echo $this->db->last_query();
//die;
$row = $query->result();
return $row[0];

        //$unitinfo = $this->db->get_where('mdl_booking_unit_request', ['user_id' => $user_id])->order_by('id', 'DESC')->row_array();
        //echo $this->db->last_query();

      //  return $unitinfo;
    }

}