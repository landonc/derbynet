<?php session_start(); ?>
<?php
require_once('inc/data.inc');
require_once('inc/authorize.inc');
require_permission(SET_UP_PERMISSION);  // TODO: What's the correct permission?
require_once('inc/timer-state.inc');
require_once('inc/replay.inc');
require_once('inc/kiosks.inc');
?>
<html>
<head>
<title>Race Coordinator Page</title>
<?php require('inc/stylesheet.inc'); ?>
<link rel="stylesheet" type="text/css" href="css/jquery.mobile-1.4.2.css"/>
<link rel="stylesheet" type="text/css" href="css/coordinator.css"/>
<script type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript">
// We're using jQuery Mobile for its nice mobile/tablet-friendly UI
// elements.  By default, jQM also wants to hijack page loading
// functionality, and perform page loads via ajax, a "service" we
// don't really want.  Fortunately, it's possible to turn that stuff
// off.
$(document).bind("mobileinit", function() {
				   $.extend($.mobile, {
					 ajaxEnabled: false
					 });
				 });
</script>
<script type="text/javascript" src="js/jquery.mobile-1.4.2.min.js"></script>
<script type="text/javascript" src="js/coordinator.js"></script>
</head>
<body>
<?php
$banner_title = 'Race Coordinator Page';
require('inc/banner.inc');
?>
<div class="control_column">


<div class="control_group heat_control_group">
  <h3>Sequence Control</h3>

  <!-- TODO: Display current heat (round and heat); update -->
  <!-- TODO: Select racing round explicitly -->

  <!-- TODO: Start/stop racing: explicit am-racing state. -->

  <div class="centered_flipswitch">
  <!-- label for="is-currently-racing">Now Racing</label -->
  <input type="checkbox" data-role="flipswitch" name="is-currently-racing" id="is-currently-racing"
    checked="checked"
    data-on-text="Racing" data-off-text="Not Racing"/>
<!-- onchange= -->
  </div>


  <div class="block_buttons">
    <input type="button" data-enhanced="true" value="Skip Heat" onclick="handle_skip_heat()"/><br/>
    <input type="button" data-enhanced="true" value="Previous Heat" onclick="handle_previous_heat()"/><br/>
    <!-- TODO: manual heat results -->
    <input type="button" data-enhanced="true" value="Manual Results"/><br/>
  </div>

</div>


<div class="control_group timer_control_group">
  <!-- TODO: Show timer status -->
  <h3>Timer Status</h3>
  <p><b><?php
    $tstate = get_timer_state();
    if ($tstate == TIMER_NOT_CONNECTED) {
        echo 'NOT CONNECTED';
    } else if ($tstate == TIMER_CONNECTED) {
        echo 'CONNECTED ('.(time() - read_raceinfo('timer_last_contact')).' sec.)';
    } else if ($tstate == TIMER_PREPARED) {
        echo 'Prepared';
    } else if ($tstate == TIMER_RUNNING) {
        echo 'Race running';
    } else {
        echo 'Unknown ('.$tstate.')';
    }
?></b></p>
  <p>The track has <?php echo get_lane_count(); ?> lane(s).</p>
  <!-- TODO: Timer events:
  Timer to database:
      Hello/identity (type of timer, number of lanes)
      Heartbeat
      Gate opened
      Race finished
  DB to timer:
      Prepare timer (with lane mask)
      Number of lanes (configured by race coordinator)
      Heat cancel/abort
  -->
</div>

<div class="control_group replay_control_group">
  <!-- TODO: Show instant-replay status -->
  <h3>Instant Replay Status</h3>
  <p>Remote replay at <?= read_replay_host_and_port() ?></p>
  <div class="block_buttons">
    <input type="button" data-enhanced="true" value="Test Replay" onclick="handle_test_replay();"/>
  </div>
</div>



<div class="kiosk_control_group">
<div class="block_buttons">
<?php
try {
    drop_old_kiosks();
    $stmt = $db->query('SELECT address, name, page, last_contact FROM Kiosks ORDER BY name, last_contact');
    foreach ($stmt as $row) {
        echo '<div class="control_group kiosk_control">'."\n";
        echo   '<p>Kiosk ';
        echo   '<span class="kiosk_control_name">'.htmlspecialchars($row['name']).'</span> ';
        echo   '<span class="kiosk_control_address">'.htmlspecialchars($row['address']).'</span> ';
        echo   '</p>';
        echo '<p class="last_contact">Last contact: '.$row['last_contact'].'</p>'."\n";

        echo   '<label for="kiosk-page">Display:</label>'; // TODO: Fire action when selection changes.
        echo   '<select name="kiosk-page" data-kiosk-address="'.htmlspecialchars($row['address']).'"'
               .' onchange="handle_kiosk_page_change(this)"'
               .'>'."\n";
        echo   '<optgroup>'."\n";

        scan_kiosk_pages('kiosks', $row['page']);
        scan_kiosk_pages('local'.DIRECTORY_SEPARATOR.'kiosks', $row['page']);

        echo   '</optgroup>'."\n";
        echo   '</select>'."\n";
        echo '</div>'."\n";
    }
} catch (PDOException $p) {
    // TODO: No Kiosks table
    echo '<h2>No Kiosks table defined.</h2>';  // TODO: Or other problem...
    // TODO: What if not throwing exceptions?
    // TODO: Create kiosk table on demand
    // TODO: Allow assigning name to kiosk
}
?>
</div>
</div>
</div>

<div class="control_column">

<div id="scheduling-control-group" class="scheduling_control_group">
  <p>Waiting for coordinator-poll query...</p>
</div>

</div>

</body>
</html>