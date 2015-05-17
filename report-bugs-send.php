<?php require 'include/prehtml.php'; ?>
<?php
$sql = "SELECT player_name FROM players WHERE id = '$eos_player_id'";
$eos_player_name = $db->query($sql)->fetchColumn();

if ($eos_firm_id) {
	$sql = "SELECT name FROM firms WHERE id = '$eos_firm_id'";
	$eos_firm_name = $db->query($sql)->fetchColumn();
} else {
	$eos_firm_name = 'N/A';
}

$message_subject = filter_var($_POST["subject"], FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
$message_body = filter_var($_POST["body"], FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
if (isset($_SESSION['rb_time'])) {
	$rb_time = filter_var($_SESSION['rb_time'], FILTER_SANITIZE_NUMBER_INT);
} else {
	$resp = array('success' => 0, 'msg' => 'Submission failed. Your browser doesn\'t support SESSIONS. Please use a modern browser such as Chrome or Firefox.');
	echo json_encode($resp);
	exit();
}

$timenow = time();
if (($timenow - $rb_time) < 5) {
	$resp = array('success' => 0, 'msg' => 'Submission failed. You were mistaken for a robot. If you are human, please spend a few more seconds before you submit the report.');
	echo json_encode($resp);
	exit();
}
if (($timenow - $rb_time) > 7200) {
	$resp = array('success' => 0, 'msg' => 'Submission failed. What took you so many hours? If you are human, please copy (Ctrl+C) your report body, close the dialog, re-open it, paste the text, and send it again.');
	echo json_encode($resp);
	exit();
}
if (!$message_subject) {
	$resp = array('success' => 0, 'msg' => 'Please write a title.');
	echo json_encode($resp);
	exit();
}
if (!$message_body) {
	$resp = array('success' => 0, 'msg' => 'Please write some details.');
	echo json_encode($resp);
	exit();
}

$message_body = $message_subject . '

' . $message_body . '

Player Id: ' . $eos_player_id . '
Player Name: ' . $eos_player_name . '
Firm Id: ' . $eos_firm_id . '
Firm Name: ' . $eos_firm_name . '
Current Time: ' . date("F j, Y, g:i A");

//Send the message
$sender = "admin@example.com";
$sender_name = "Example Mailer";
$recipient = "someguy@example.com";
$add_reply = "someguy@example.com";
$subject = "Bug Report - EoS - " . $message_subject;

$headers = "MIME-Version: 1.0\n";
$headers .= "From: $sender_name <$sender>\n";
$headers .= "X-Sender: <$sender>\n";
$headers .= "X-Mailer: Example Mailer 1.0\n"; //mailer
$headers .= "X-Priority: 3\n"; //1 UrgentMessage, 3 Normal
$headers .= "Content-Type: text/plain\n";
$headers .= "Return-Path: <$add_reply>\n";

mail($recipient, $subject, $message_body, $headers);

unset($_SESSION['rb_time']);
$resp = array('success' => 1);
echo json_encode($resp);
exit();
?>