<?php
// USAGE: test-ldap.php <username>
// This will check the given <username> for access and group membership.  The <username> is required.
// Note that the username and password used for the bind() operation is defined as a constant and is not
// necessarily the same as the username given as argument to the script.

// These constants need values which are environment-specific.
define('HOSTNAME', 'ldap://ACADEMIC.academic.accit.nsw.edu.au');  // This was a normal FQHN, e.g. "server.domain.org.au"
define('USERNAME', '');  // This is a Windows login workgroup style username, e.g. "WORKGROUP\joe.bloggs"
define('PASSWORD', '');  // Plain text password, e.g. "password"
define('BASE_DN', 'CN=super admin,OU=Students,OU=ACCIT,DC=academic,DC=accit,DC=nsw,DC=edu,DC=au');   // The base distinguished name, consisting of several domain components, e.g. "DC=domain,DC=org,DC=au"
define('USER_OU', 'OU=Students,OU=ACCIT');   // The organisation unit hierarchy describing where users are, e.g. "OU=Users,OU=Department,OU=TheCompany"
define('GROUP_DN', '*');  // Pipe-separated list of distinguished names of groups to match against, e.g. "CN=UserGroup,OU=Department,OU=TheCompany,DC=domain,DC=org,DC=au|CN=AdminGroup,OU=Department,OU=TheCompany,DC=domain,DC=org,DC=au"
define('ATTRIBUTE', ''); // The attribute used to match against the entered username, e.g. in our case, "sAMAccountName"

$ldap = ldap_connect(HOSTNAME);
ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);

if (!ldap_bind($ldap, USERNAME, PASSWORD)) {
	ldap_unbind($ldap);
	die('Authentication error' . PHP_EOL);
}

$search = sprintf('%s,%s', USER_OU, BASE_DN);
$filter = sprintf('%s=%s', ATTRIBUTE, (isset($argv[1]) ? $argv[1] : ''));
$results = ldap_search($ldap, $search, $filter, array( ATTRIBUTE, 'givenName', 'sn', 'memberOf' ));
if (!$results) {
	ldap_unbind($ldap);
	die('Search error' . PHP_EOL);
}

$entry = ldap_first_entry($ldap, $results);
if (!$entry) {
	ldap_unbind($ldap);
	die('No results found' . PHP_EOL);
}

$attrs = ldap_get_attributes($ldap, $entry);

echo 'Success! Found ' . $attrs['givenName'][0] . ' ' . $attrs['sn'][0] . PHP_EOL;

$keys = array_filter(array_keys($attrs), function ($item) {
	return !is_numeric($item);
});
sort($keys);
print_r($keys);

print_r($attrs['memberOf']);
$groups = explode('|', GROUP_DN);
$match = sizeof(array_intersect($groups, $attrs['memberOf'])) > 0;
echo $match ? 'Found group, user can login' . PHP_EOL : 'Did not find group' . PHP_EOL;

ldap_unbind($ldap);
