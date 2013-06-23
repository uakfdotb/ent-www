<?php

define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : 'forum/';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
require($phpbb_root_path . 'common.' . $phpEx);
require($phpbb_root_path . 'includes/functions_user.'.$phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup();

?>

<html>
<body>

<p>This function requires whitelist. But if you're whitelisted, you can enter a player name here and get his or her IP address.</p>

<form method="get" action="iplookup.php">
Player (name@realm): <input type="text" name="player"> <input type="submit" value="Search">
</form>

<table>
<tr>
	<th>IP</th>
</tr>

<?php

include("include/common.php");
include("include/dbconnect.php");
include("include/iplookup.php");

$player = array("", "");
$hours = 24 * 40;

if(isWhitelist($_SERVER['REMOTE_ADDR']) || isadmin($user->data['user_id'])) {
	if(isset($_REQUEST['player'])) {
		$player = getPlayer($_REQUEST['player']);
	}

	if(isset($_REQUEST['hours'])) {
		$hours = $_REQUEST['hours'];
	}
}

$result = iplookup($player[0], $player[1], $hours);

while($row = $result->fetch()) {
	echo "<tr>\n";
	echo "\t<td>" . htmlspecialchars($row[0]) . "</td>\n";
	echo "</tr>\n";
}

?>

</table>
</body>
</html>
