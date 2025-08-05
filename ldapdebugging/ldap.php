<?php 
// mydap version 4
// https://samjlevy.com/mydap/

function mydap_start($username,$password,$host,$port=389) {
	global $mydap;
	if(isset($mydap)) die('Error, LDAP connection already established');

	// Connect to AD
	$mydap = ldap_connect($host,$port) or die('Error connecting to LDAP');
	
	ldap_set_option($mydap,LDAP_OPT_PROTOCOL_VERSION,3);
	@ldap_bind($mydap,$username,$password) or die('Error binding to LDAP: '.ldap_error($mydap));

	return true;
}

function mydap_end() {
	global $mydap;
	if(!isset($mydap)) die('Error, no LDAP connection established');

	// Close existing LDAP connection
	ldap_unbind($mydap);
}

function mydap_attributes($user_dn,$keep=false) {
	global $mydap;
	if(!isset($mydap)) die('Error, no LDAP connection established');
	if(empty($user_dn)) die('Error, no LDAP user specified');

	// Disable pagination setting, not needed for individual attribute queries
	ldap_control_paged_result($mydap,1);
 
	// Query user attributes
	$results = (($keep) ? ldap_search($mydap,$user_dn,'cn=*',$keep) : ldap_search($mydap,$user_dn,'cn=*'))
	or die('Error searching LDAP: '.ldap_error($mydap));

	$attributes = ldap_get_entries($mydap,$results);

	// Return attributes list
	if(isset($attributes[0])) return $attributes[0];
	else return array();
}

function mydap_members($object_dn,$object_class='g') {
	global $mydap;
	if(!isset($mydap)) die('Error, no LDAP connection established');
	if(empty($object_dn)) die('Error, no LDAP object specified');

	// Determine class of object we are dealing with

	// Groups, use range to overcome LDAP attribute limit
	if($object_class == 'g') {
		$output = array();
		$range_size = 1500;
		$range_start = 0;
		$range_end = $range_size - 1;
		$range_stop = false;

		do {
			// Query Group members
			$results = ldap_search($mydap,$object_dn,'cn=*',array("member;range=$range_start-$range_end")) or die('Error searching LDAP: '.ldap_error($mydap));
			$members = ldap_get_entries($mydap,$results);

			$member_base = false;

			// Determine array key of the member results

			// If array key matches the format of range=$range_start-* we are at the end of the results
			if(isset($members[0]["member;range=$range_start-*"])) {
				// Set flag to break the do loop
				$range_stop = true;
				// Establish the key of this last segment
				$member_base = $members[0]["member;range=$range_start-*"];

			// Otherwise establish the key of this next segment
			} elseif(isset($members[0]["member;range=$range_start-$range_end"]))
				$member_base = $members[0]["member;range=$range_start-$range_end"];

			if($member_base && isset($member_base['count']) && $member_base['count'] != 0) {
				// Remove 'count' element from array
				array_shift($member_base);

				// Append this segment of members to output
				$output = array_merge($output,$member_base);
			} else $range_stop = true;

			if(!$range_stop) {
				// Advance range
				$range_start = $range_end + 1;
				$range_end = $range_end + $range_size;
			}
		} while($range_stop == false);

	// Containers and Organizational Units, use pagination to overcome SizeLimit
	} elseif($object_class == 'c' || $object_class == "o") {

		$pagesize = 1000;
		$counter = "";
		do {
			ldap_control_paged_result($mydap,$pagesize,true,$counter);
			
			// Query Container or Organizational Unit members
			$results = ldap_search($mydap,$object_dn,'objectClass=user',array('sn')) or die('Error searching LDAP: '.ldap_error($mydap));
			$members = ldap_get_entries($mydap, $results);

			// Remove 'count' element from array
			array_shift($members);

			// Pull the 'dn' from each result, append to output
			foreach($members as $e) $output[] = $e['dn'];

			ldap_control_paged_result_response($mydap,$results,$counter);
		} while($counter !== null && $counter != "");
	
	// Invalid object_class specified
	} else die("Invalid mydap_member object_class, must be c, g, or o");

	// Return alphabetized member list
	sort($output);
	return $output;
}

function mydap_changePassword($username='',$newPassword='')
{
    global $mydap;
    $dn = "uid=".$username.",DC=academic,DC=accit,DC=nsw,DC=edu,DC=au";
    $newEntry = array('password' => $newPassword);
    if(ldap_mod_replace($mydap, $dn, $newEntry))
    {
        print "<p>Successfully Updated!</p>";
    }
    else
    {
        print "Failed to Update!".ldap_error($mydap);
    }
}
// ==================================================================================
// Example Usage
// ==================================================================================

// Establish connection
mydap_start(
	'CN=super admin,OU=Students,OU=ACCIT,DC=academic,DC=accit,DC=nsw,DC=edu,DC=au', // Active Directory search user
	'Jeeonflux2019', // Active Directory search user password
	'ldap://ACADEMIC.academic.accit.nsw.edu.au', // Active Directory server
	389 // Port (optional)
);

// Query users using mydap_members(object_dn,object_class)
// The object_dn parameter should be the distinguishedName of the object
// The object_class parameter should be 'c' for Container, 'g' for Group, or 'o' for Organizational Unit
// If left blank object_class will assume Group
// Ex: the default 'Users' object in AD is a Container
// The function returns an array of member distinguishedName's
$members = mydap_members('OU=Students,OU=ACCIT,DC=academic,DC=accit,DC=nsw,DC=edu,DC=au','c');
if(!$members) die('No members found, make sure you are specifying the correct object_class');

// Now collect attributes for each member pulled
// Specify user attributes we want to collect, to be used as the keep parameter of mydap_attributes
$keep = array('samaccountname');

// Iterate each member to get attributes
$i = 1; // For counting our output
foreach($members as $m) {
	// Query a user's attributes using mydap_attributes(member_dn,keep)
	// The member_dn is the step $m of this foreach
	$attr = mydap_attributes($m,$keep);

	// Each attribute is returned as an array, the first key is [count], [0]+ will contain the actual value(s)
	// You will want to make sure the key exists to account for situations in which the attribute is not returned (has no value)
	//$employeeID = isset($attr['streetAddress'][0]) ? $attr['streetAddress'][0] : "[no employee ID]";
	$samaccountname = isset($attr['samaccountname'][0]) ? $attr['samaccountname'][0] : "[no account name]";
        //$phone = isset($attr['telephoneNumber'][0]) ? $attr['telephoneNumber'][0] : "[no phone]";
	

	// Do what you will, such as store or display member information
	echo "$i. $samaccountname . <br>";

	$i++;
}

$username = 'accitstudent2';
$newPassword = 'Debraj@123';
mydap_changePassword($username,$newPassword);

//$dn = "OU=Students,OU=ACCIT,DC=academic,DC=accit,DC=nsw,DC=edu,DC=au";
$dn = "OU=Students,OU=ACCIT,DC=academic,DC=accit,DC=nsw,DC=edu,DC=au";
$modifs = [
    [
        "attrib"  => "samaccountname",
        "modtype" => LDAP_MODIFY_BATCH_REPLACE,
        "values"  => "accitstudent_2"
    ]
];
if(ldap_modify_batch($mydap, $dn, $modifs)) { echo 'Successfully Updated!'; } else { echo '<br>Failed!'.ldap_error($mydap);  }

// Here you could run another mydap_members() if needed, merge with previous results, etc.

// Close connection
mydap_end();

// Here you can open a new connection with mydap_connect() if needed, such as to a different AD server
?>