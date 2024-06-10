<?php

require_once(dirname(__FILE__) . '/../../config.php');


global $DB, $OUTPUT, $PAGE;

function post_data($json_data, $post_path) {
    $backendAddress = get_config('local_sqljudge', 'backendaddress');
    $backendPort = explode(':', $backendAddress)[1];

    $headers = array(
        "Accept: application/json",
        "Content-Type: application/json"
    );

    //POST data to server
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $backendAddress . $post_path,
        CURLOPT_PORT => $backendPort,
        CURLOPT_POST => 1,
        CURLOPT_POSTFIELDS => $json_data,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_FOLLOWLOCATION => true,
    ));
    
    //for debug only
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

    $resp = curl_exec($curl);
    $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    return $httpCode;
}

$submissionId = intval(optional_param('id', -1, PARAM_INT));

$url = new moodle_url('/local/sqljudge/check.php');
$url->param('id', $submissionId);
$PAGE->set_url($url);
$submission = $DB->get_record('assignsubmission_onlinetext', array('submission'=>$submissionid));
        
$assign = $DB->get_record_sql(
    'SELECT a.course, a.id, s.userid, ot.onlinetext 
    FROM {assign_submission} s
    JOIN {assign} a
        ON s.assignment = a.id
        JOIN {assignsubmission_onlinetext} ot
            ON s.id = ot.submission
    WHERE s.id = ?', array($submissionId));

require_login($assign->course, false, null, false, true);

$backendAddress = get_config('local_sqljudge', 'backendaddress');
$backendPort = explode(':', $backendAddress)[1];
/*
Submission JSON
{
    "assignId": $assign->id,
    "studentId": $assign->userid,
    "script": $assign->onlinetext,
}
*/
$submission_json_array = array('submissionId' =>intval($submissionId), 'assignId' => intval($assign->id), 'studentId' => intval($assign->userid), 'script' => $assign->onlinetext);
$submission_json_array = json_encode($submission_json_array, true);
$post_path = '/api/submissions/check';
$respcode = post_data($submission_json_array, $post_path);

echo $OUTPUT->header();

if ($respcode === 200) {
    echo $OUTPUT->notification(get_string('checked', 'assignfeedback_sqljudge'), 'notifysuccess');
} else if ($respcode === 422) {
    echo $OUTPUT->notification(get_string('checkfailed', 'assignfeedback_sqljudge'), 'notifyerror');
} else {
    echo $OUTPUT->notification(
        get_string('checkerrorcode', 'assignfeedback_sqljudge', $respcode), 
        'notifyerror');
}

echo '<a href="javascript:history.back()">' . get_string('backtopageyouwereon') . '</a>';

echo $OUTPUT->footer();