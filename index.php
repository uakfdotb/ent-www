<?php
/*

	ent-www
	Copyright [2012-2013] [Jack Lu]

	This file is part of the ent-www source code.

	ent-www is free software: you can redistribute it and/or modify
	it under the terms of the GNU Affero General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	ent-www source code is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU Affero General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with ent-www source code. If not, see <http://www.gnu.org/licenses/>.

*/

define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : 'forum/';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/bbcode.' . $phpEx);
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup('viewforum');

$search_limit = 20;

$forum_id = array(4);
$forum_id_where = create_where_clauses($forum_id, 'forum');

$posts_ary = array(
'SELECT' => 'p.*, t.*',

'FROM' => array(
POSTS_TABLE => 'p',
),

'LEFT_JOIN' => array(
array(
'FROM' => array(TOPICS_TABLE => 't'),
'ON' => 't.topic_first_post_id = p.post_id'
)
),

'WHERE' => str_replace( array('WHERE ', 'forum_id'), array('', 't.forum_id'), $forum_id_where) . '
AND t.topic_status <> ' . ITEM_MOVED . '
AND t.topic_approved = 1',

'ORDER_BY' => 'p.post_id DESC',
);

$posts = $db->sql_build_query('SELECT', $posts_ary);

$posts_result = $db->sql_query_limit($posts, $search_limit);

while ($posts_row = $db->sql_fetchrow($posts_result))
{
$topic_title = $posts_row['topic_title'];
$topic_author = get_username_string('full', $posts_row['topic_poster'], $posts_row['topic_first_poster_name'], $posts_row['topic_first_poster_colour']);
$topic_date = $user->format_date($posts_row['topic_time']);
$topic_link = append_sid("{$phpbb_root_path}viewtopic.$phpEx", "t=" . $posts_row['topic_id']);

$post_text = censor_text($posts_row['post_text']);

$bbcode = new bbcode(base64_encode($bbcode_bitfield));
$bbcode->bbcode_second_pass($post_text, $posts_row['bbcode_uid'], $posts_row['bbcode_bitfield']);

$post_text = smiley_text(bbcode_nl2br($post_text));

$template->assign_block_vars('announcements', array(
'TOPIC_TITLE' => censor_text($topic_title),
'TOPIC_AUTHOR' => $topic_author,
'TOPIC_DATE' => $topic_date,
'TOPIC_LINK' => $topic_link,
'POST_TEXT' => censor_text($post_text),
));
}

page_header('ENT Gaming - Announcements');

$template->set_filenames(array(
'body' => 'announcements.html'
));

page_footer();

/* create_where_clauses( int[] gen_id, String type )
* This function outputs an SQL WHERE statement for use when grabbing
* posts and topics */

function create_where_clauses($gen_id, $type)
{
global $db, $auth;

$size_gen_id = sizeof($gen_id);

switch($type)
{
case 'forum':
$type = 'forum_id';
break;
case 'topic':
$type = 'topic_id';
break;
default:
trigger_error('No type defined');
}

// Set $out_where to nothing, this will be used of the gen_id
// size is empty, in other words "grab from anywhere" with
// no restrictions
$out_where = '';

if ($size_gen_id > 0)
{
// Get a list of all forums the user has permissions to read
$auth_f_read = array_keys($auth->acl_getf('f_read', true));

if ($type == 'topic_id')
{
$sql = 'SELECT topic_id FROM ' . TOPICS_TABLE . '
WHERE ' . $db->sql_in_set('topic_id', $gen_id) . '
AND ' . $db->sql_in_set('forum_id', $auth_f_read);

$result = $db->sql_query($sql);

while ($row = $db->sql_fetchrow($result))
{
// Create an array with all acceptable topic ids
$topic_id_list[] = $row['topic_id'];
}

unset($gen_id);

$gen_id = $topic_id_list;
$size_gen_id = sizeof($gen_id);
}

$j = 0;

for ($i = 0; $i < $size_gen_id; $i++)
 {
$id_check = (int) $gen_id[$i]; // If the type is topic, all checks have been made and the query can start to be built if( $type == 'topic_id' ) { $out_where .= ($j == 0) ? 'WHERE ' . $type . ' = ' . $id_check . ' ' : 'OR ' . $type . ' = ' . $id_check . ' '; } // If the type is forum, do the check to make sure the user has read permissions else if( $type == 'forum_id' && $auth->acl_get('f_read', $id_check) )
{
$out_where .= ($j == 0) ? 'WHERE ' . $type . ' = ' . $id_check . ' ' : 'OR ' . $type . ' = ' . $id_check . ' ';
}

$j++;
}
}

if ($out_where == '' && $size_gen_id > 0)
{
trigger_error('A list of topics/forums has not been created');
}

return $out_where;
}

?>
