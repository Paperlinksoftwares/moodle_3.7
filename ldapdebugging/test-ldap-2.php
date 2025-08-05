#!/usr/bin/php
<?php

# Parse options
$opts = getopt('h:n:u:p:b:s:', array( 'help' ));

if (isset($opts['help']) && $opts['help']) {
	echo <<<ENDHELP
Usage:
    $argv[0] [-h HOSTNAME] [-n PORTNUM] [-u USERNAME] [-p PASSWORD] [-b BASEDN] [-s SEARCH]
Where:
    HOSTNAME is the LDAP hostname to connect to; omit to use default (pool.ldap.csiro.au)
    PORTNUM is the port number to connect to; omit to use default (389)
    USERNAME is the username passed to ldap_bind(); omit to bind anonymously
    PASSWORD is the password passed to ldap_bind(); omit to bind without a password
    BASEDN is the base DN passed to ldap_search(); omit to use the default (DC=nexus,DC=csiro,DC=au)
    SEARCH is the search string passed to ldap_search(); omit to use the default (sAMAccountName=gib392)
ENDHELP
;
	exit(0);
}

# Extract options into variables
$hostname = isset($opts['h']) ? $opts['h'] : 'pool.ldap.csiro.au';
$port_num = isset($opts['n']) ? intval($opts['n']) : 389;
$username = isset($opts['u']) ? $opts['u'] : null;
$password = isset($opts['p']) ? $opts['p'] : null;
$base_dn = isset($opts['b']) ? $opts['b'] : 'DC=nexus,DC=csiro,DC=au';
$search = isset($opts['s']) ? $opts['s'] : 'sAMAccountName=gib392';

# Connect to LDAP
echo "Executing: ldap_connect('$hostname', $port_num)" . PHP_EOL;
$ldap = ldap_connect($hostname, $port_num);

# Protocol version 3 and no referrals are required for AD
ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);

# Bind to LDAP
echo "Executing: ldap_bind(<ldap>, '$username', '$password')" . PHP_EOL;
$bind_result = ldap_bind($ldap, $username, $password);
if (!$bind_result) {
	echo "Error: Could not bind: " . PHP_EOL . ldap_error($ldap) . PHP_EOL;
	exit(100);
}

# Perform search
echo "Executing: ldap_search(<ldap>, '$base_dn', '$search')" . PHP_EOL;
$results = ldap_search($ldap, $base_dn, $search);
if (!$results) {
	echo "Error: Could not search" . PHP_EOL . ldap_error($ldap) . PHP_EOL;
	exit(200);
}

# Output results
echo "Got results fro LDAP search..." . PHP_EOL;
print_r(ldap_get_entries($ldap, $results));
