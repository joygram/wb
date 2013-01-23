<?php
	require_once 'localtime.php';
?>

<h1>This is page1</h1>

<?php
if (isset($_SESSION['offset'])) {
	echo "<p>Local time is set.</p>";
} else {
	echo "<p>Local time not set yet. Please go to the other page to set it.</p>";
}
?>

<p>Go to <a href="page2.php">page2</a>.</p>

<p>The current date is: <?php echo format_datetime(time()) ?></p>

<p>This is a link to <a href="http://www.google.com/">Google</a>, it should not have an offset query string.</p>
