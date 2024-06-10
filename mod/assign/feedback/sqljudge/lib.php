<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/assign/feedback/sqljudge/locallib.php');
require_once($CFG->dirroot . '/mod/assign/locallib.php');

class sqljudge_controller{
    function check_service() {
        $host = get_config('local_sqljudge', 'backendaddress');
        $port = explode(':', $host)[1];
        $fp = fSockOpen($host, $port, $errno, $errstr, 2); 
        return $fp != false;
    }
    
    function get_databases() {
        if ($this->check_service()){
            $get_path = '/api/databases';
    
            $databases = $this->get_data($get_path)[1];
            // test database
            $options = array();
            if (!empty($databases)) {
                for ($i = 0; $i<count($databases); $i++){
                    $options[$databases[$i]["id"]] = $databases[$i]["dbmsName"] . ': ' . $databases[$i]["fileName"] . ' (' . $databases[$i]["description"] . ')';
                }
            }
        }
        return $options;
    }

    function post_data($json_data, $post_path) {
        if ($this->check_service()){
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
    }
    
    function get_data($get_path) {
        if ($this->check_service()) {
            $backendAddress = get_config('local_sqljudge', 'backendaddress');
            $backendPort = explode(':', $backendAddress)[1];
            
            $headers = array(
                "Accept: application/json",
                "Content-Type: application/json"
            );
        
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_HEADER => false,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_AUTOREFERER => true,
                CURLOPT_URL => $backendAddress . $get_path,
                CURLOPT_PORT => $backendPort,
                CURLOPT_HTTPHEADER => $headers,
            ));
                    
            //for debug only
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        
            $resp = curl_exec($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            if ($resp === false) {
                // Error occurred during the request
                $error = curl_error($curl);
                curl_close($curl);
                echo "Error: " . $error;
                exit();
            }
            curl_close($curl);
            // answer
            $answer = array();
            $answer = json_decode($resp, true);
            return array($httpCode, $answer);
        }
    }

    function update_data($json_data, $update_path) {
        if ($this->check_service()) {
            $backendAddress = get_config('local_sqljudge', 'backendaddress');
            $backendPort = explode(':', $backendAddress)[1];
        
            $headers = array(
                "Accept: application/json",
                "Content-Type: application/json"
            );
        
            //POST data to server
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $backendAddress . $update_path,
                CURLOPT_PORT => $backendPort,
                CURLOPT_CUSTOMREQUEST => "UPDATE",
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
    }

    function delete_data($delete_path) {
        $backendAddress = get_config('local_sqljudge', 'backendaddress');
        $backendPort = explode(':', $backendAddress)[1];
        
        $headers = array(
            "Accept: application/json",
            "Content-Type: application/json"
        );
    
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_CUSTOMREQUEST => "DELETE",
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_URL => $backendAddress . $delete_path,
            CURLOPT_PORT => $backendPort,
            CURLOPT_HEADER => true,
            CURLOPT_NOBODY => true,
            CURLOPT_HTTPHEADER => $headers,
        ));
                
        //for debug only
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    
        $resp = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        return $httpCode;
    }
    function check_answer($assignment, $assign_id, $student_id, $inputed_scripts) {
        GLOBAL $OUTPUT;

        $submission_json_array = array('assignId' => intval($assign_id), 'studentId' => intval($student_id), 'script' => $inputed_scripts);
        $submission_json_array = json_encode($submission_json_array, true);
        $post_path = '/api/submissions/check';
        $respcode = $this->post_data($submission_json_array, $post_path);
        
        if ($respcode === 200 || $respcode === 307) {
            $this->update_grade($assignment, $student_id);
            echo $OUTPUT->notification(get_string('checked', 'assignfeedback_sqljudge'), 'notifysuccess');
        } else if ($respcode === 422) {
            echo $OUTPUT->notification(get_string('checkfailed', 'assignfeedback_sqljudge'), 'notifyerror');
        } else {
            echo $OUTPUT->notification(
                get_string('checkerrorcode', 'assignfeedback_sqljudge', $respcode), 
                'notifyerror');
        }
    }

    function update_grade($assignment, $student_id) {

        $roleid = 5;

        // Название права, которое мы будем временно назначать (mod/assign:grade)
        $capability = 'mod/assign:grade';

        // Временно назначаем право current_userid в контексте текущего курса
        assign_capability($capability, CAP_ALLOW, $roleid, $assignment->get_context());
        
        try {
            $sqlj_submission = $this->get_sqlj_submission($student_id, $assignment);

            $success_answers = 0;

            if (isset($sqlj_submission)) {
                foreach ($sqlj_submission as $task => $value) {
                    $success_answers = $value["status"] == SQLJ_STATUS_CORRECT_ANSWER
                        ? $success_answers + intval(1)
                        : $success_answers;
                }

                $grade = $assignment->get_user_grade($student_id, true);
                $max_grade = (float) ($assignment->get_grade_item()->grademax);

                $user_grade = $success_answers * $max_grade / count($sqlj_submission);
                if (isset($user_grade)) {
                    $grade->grade = number_format($user_grade, 5, '.', '');
                    $assignment->save_grade($student_id, $grade);
                }
            }
        } catch (Exception $e) {
            unassign_capability($capability, $roleid, $assignment->get_context()->id);
        } finally {
            unassign_capability($capability, $roleid, $assignment->get_context()->id);
        }
    }

    function get_sqlj_submission($user_id, $assignment) {
        $assign_id = $assignment->get_instance()->id;
        $get_path = '/api/submissions/' . $user_id . '/' . $assign_id;
        $sqlj_submission = $this->get_data($get_path)[1];
        return $sqlj_submission;
    }
}
