<?php

require_once ('MysqliDb.php');

if ( Haoli58Helper::isDevelopment() ) {
    $dbhost = "127.0.0.1";
    define("MYSQLUSER","xxx");
    define("MYSQLPASSWD","xxx");
    define("MYSQLDATABASE","xxx");
}
else if (APPLICATION_ENV == "staging") {
	$dbhost = "xxx";
}
else {
	$dbhost = "xxx";
    define("MYSQLUSER","xxx");
    define("MYSQLPASSWD","xxx");
    define("MYSQLDATABASE","xxx");
}



$mysqli = new MysqliDb (
			Array (
                'host' 			=> $dbhost,
                'username' 		=> MYSQLUSER, 
                'password' 		=> MYSQLPASSWD,
                'db'			=> MYSQLDATABASE,
                'charset' 		=> 'utf8'
            )
);

/*

Running raw SQL queries

<pre>
$users = $db->rawQuery('SELECT * from users where id >= ?', Array (10));
foreach ($users as $user) {
    print_r ($user);
}
</pre>

To avoid long if checks there are couple helper functions to work with raw query select results:

Get 1 row of results:

<pre>
$user = $db->rawQueryOne ('select * from users where id=?', Array(10));
echo $user['login'];
// Object return type
$user = $db->ObjectBuilder()->rawQueryOne ('select * from users where id=?', Array(10));
echo $user->login;
</pre>

Get 1 column value as a string:

<pre>
$password = $db->rawQueryValue ('select password from users where id=? limit 1', Array(10));
echo "Password is {$password}";
</pre>

NOTE: for a rawQueryValue() to return string instead of an array 'limit 1' should be added to the end of the query.
Get 1 column value from multiple rows:

<pre>
$logins = $db->rawQueryValue ('select login from users limit 10');
foreach ($logins as $login)
    echo $login;
 </pre>


More advanced examples:

<pre>
$params = Array(1, 'admin');
$users = $db->rawQuery("SELECT id, firstName, lastName FROM users WHERE id = ? AND login = ?", $params);
print_r($users); // contains Array of returned rows

// will handle any SQL query
$params = Array(10, 1, 10, 11, 2, 10);
$q = "(
    SELECT a FROM t1
        WHERE a = ? AND B = ?
        ORDER BY a LIMIT ?
) UNION (
    SELECT a FROM t2 
        WHERE a = ? AND B = ?
        ORDER BY a LIMIT ?
)";
$resutls = $db->rawQuery ($q, $params);
print_r ($results); // contains Array of returned rows
</pre>

*/

?>