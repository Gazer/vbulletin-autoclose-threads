<?php
/*======================================================================*\
|| #################################################################### ||
|| 3DG - Autoclose Threads
|| #################################################################### ||
\*======================================================================*/

// ######################## SET PHP ENVIRONMENT ###########################
error_reporting(E_ALL & ~E_NOTICE);
if (!is_object($vbulletin->db))
{
        exit;
}

// ########################################################################
// ######################### START MAIN SCRIPT ############################
// ########################################################################

if ($vbulletin->options['3dg_autoclose_onoff'] == 1)
{
  $excluded_forum_ids = unserialize($vbulletin->options['3dg_autoclose_forums']);

  $threads_filter = "";
  $thread_ids = unserialize($vbulletin->options['3dg_autoclose_threads']);
  if (count($thread_ids) > 0) {
    $threads_filter = "threadid NOT IN (" . implode(",", $thread_ids). ") AND ";
  }

  $forums = $vbulletin->db->query_read("SELECT forumid, 3dg_autoclose_perforum FROM " . TABLE_PREFIX . "forum");
  while ($forum = $vbulletin->db->fetch_array($forums)) {
    if (in_array($forum['forumid'], $excluded_forum_ids)) continue;

    if ($forum['3dg_autoclose_perforum'] != 0) {
      $age_3dg = $forum['3dg_autoclose_perforum'];
    } else {
      $age_3dg = $vbulletin->options['3dg_autoclose_postage'];
    }
    $vbulletin->db->query_write("
      UPDATE " . TABLE_PREFIX . "thread SET open = 0
      WHERE forumid = " . $forum['forumid'] . " AND open = 1 AND sticky = 0 AND $threads_filter lastpost < ".intval(TIMENOW-(86400*$age_3dg)));
  }
  $vbulletin->db->free_result($forums);
}

log_cron_action('Autoclose Completed', $nextitem, 1);

?>