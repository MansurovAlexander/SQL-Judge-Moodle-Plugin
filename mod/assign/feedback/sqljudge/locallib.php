<?php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/filelib.php');
require_once($CFG->libdir . '/questionlib.php');
require_once($CFG->dirroot . '/local/sqljudge/judgelib.php');
require_once($CFG->dirroot . '/mod/assign/feedbackplugin.php');
require_once(dirname(__FILE__) . '/lib.php');
class assign_feedback_sqljudge extends assign_feedback_plugin {
    public function get_name() {
        return get_string('pluginname', 'assignfeedback_sqljudge');
    }

    public function get_settings(MoodleQuickForm $mform) {
        global $CFG, $COURSE, $OUTPUT;
        
        $url_params = $_SERVER['QUERY_STRING'];
        
        $sqljudge_ctrl = new sqljudge_controller();
        
        $sqljudge = array();
        
        // get existing sqljudge settings
        $update = optional_param('update', 0, PARAM_INT);
        if (!empty($update)) {
            $cm = get_coursemodule_from_id('', $update, 0, false, MUST_EXIST);
            $get_path = '/api/assignes/' . $cm->instance;
            $sqljudge = $sqljudge_ctrl->get_data($get_path)[1];
        }

        $databases = $sqljudge_ctrl->get_databases();
        $mform->addElement('select',  'testdb',  
            get_string('testdb', 'assignfeedback_sqljudge') . ' ' . 
            $OUTPUT->help_icon('testdb', 'assignfeedback_sqljudge'), 
            $databases);
        $mform->setDefault('testdb', empty($sqljudge) ? reset($databases) : $sqljudge["database"]);
        $mform->hideIf('testdb', 'assignfeedback_sqljudge_enabled', 'notchecked');

        $mform->addElement('button', 'dbsettings_redirect',
            get_string('dbsettings_redirect', 'assignfeedback_sqljudge'),
            "onclick=\"location.href = '" . $CFG->wwwroot . "/local/sqljudge/index.php?" . $url_params . "';\"");
        $mform->hideIf('dbsettings_redirect', 'assignfeedback_sqljudge_enabled', 'notchecked');

        // script for checking answers
        $mform->addElement('textarea', 'checkscript', 
            get_string('checkscript', 'assignfeedback_sqljudge') . ' ' . 
            $OUTPUT->help_icon('checkscript', 'assignfeedback_sqljudge'),
            'wrap="virtual" rows="8" cols="100"');
        $mform->setDefault('checkscript', 
            empty($sqljudge) ? get_string('checkscript_template', 'assignfeedback_sqljudge') 
                             : $sqljudge["script"]);
        $mform->hideIf('checkscript', 'assignfeedback_sqljudge_enabled', 'notchecked');

        // banned or required keywords/phrases
        $mform->addElement('textarea', 'bannedwords', 
            get_string('bannedwords', 'assignfeedback_sqljudge'), 
            'wrap="virtual" rows="8" cols="100"');
        $mform->setDefault('bannedwords', 
            empty($sqljudge) ? get_string('bannedwords_template', 'assignfeedback_sqljudge') 
                             : $sqljudge["bannedwords"]);
        $mform->hideIf('bannedwords', 'assignfeedback_sqljudge_enabled', 'notchecked');

        $mform->addElement('textarea', 'timelimit',
            get_string('timelimit', 'assignfeedback_sqljudge'), 'wrap="virtual" rows="1" cols="4"');
        $mform->setDefault('timelimit', empty($sqljudge) ? 0 : $sqljudge["time"]);
        $mform->hideIf('timelimit', 'assignfeedback_sqljudge_enabled', 'notchecked');

        $mform->addElement('textarea', 'memorylimit',
            get_string('memorylimit', 'assignfeedback_sqljudge'), 'wrap="virtual" rows="1" cols="4"');
        $mform->setDefault('memorylimit', empty($sqljudge) ? 0 : $sqljudge["memory"]);
        $mform->hideIf('memorylimit', 'assignfeedback_sqljudge_enabled', 'notchecked');
    }

    public function save_settings(stdClass $data) {
        global $DB;
        $sqljudge_ctrl = new sqljudge_controller();
        //Добавление задания
        //GET /api/assignes/:id
        $get_path = '/api/assignes/' . ($this->assignment->get_instance()->id);
        $resp_data = $sqljudge_ctrl->get_data($get_path);
        $resp_code = $resp_data[0];
        if ($resp_code != 404)
            return $this->update_instance($data, $this->assignment->get_instance()->id);
        else
            return $this->add_instance($data, $this->assignment->get_instance()->id);
    }
    
    public function view_header() {
        global $USER;
        $sqlj_controller = new sqljudge_controller();
        $output = $this->view_judge_info() . 
            '<div class="py-2">';
        if(array_key_exists('CheckScripts',$_POST)) {
            $assign_id = $this->assignment->get_instance()->id;
            $sqlj_controller->check_answer($this->assignment, $assign_id, $USER->id, $_POST['InputedScripts']);
        }
        return $output . '</div>';
    }

    function view_judge_info() {
        $sqljudge_ctrl = new sqljudge_controller();
        $get_path = '/api/assignes/' . $this->assignment->get_instance()->id;
        $assignment_sqlj = $sqljudge_ctrl->get_data($get_path)[1];

        $table = new html_table();
        $table->id = 'assignment_sqljudge_information';
        $table->attributes['class'] = 'generaltable';
        $table->size = array('30%', '');

        $get_path = '/api/databases/' . $assignment_sqlj["database"];
        $testdb = $sqljudge_ctrl->get_data($get_path)[1];
	
	
        $table->data[] = array(
            get_string('dbms', 'assignfeedback_sqljudge'),
            $testdb["dbmsName"]);

        $table->data[] = array(
            get_string('dbname', 'assignfeedback_sqljudge'),
            $testdb["fileName"]);

        $table->data[] = array(
            get_string('dbdescription', 'assignfeedback_sqljudge'),
            $testdb["description"]);

        $table->data[] = array(
            get_string('timelimit', 'assignfeedback_sqljudge'),
            $assignment_sqlj["time"]);
        
        $table->data[] = array(
            get_string('memorylimit', 'assignfeedback_sqljudge'),
            $assignment_sqlj["memory"]);

        $table->data[] = array(
            "Форма для ввода скриптов:",
            "<form method=\"post\">
            <textarea id=\"id_onlinetext_editor\" name=\"InputedScripts\" class=\"form-control\" rows=\"15\" cols=\"80\" spellcheck=\"true\"></textarea><br />
            <input type=\"submit\" class=\"btn btn-info\" id=\"CheckScripts\" name=\"CheckScripts\"/>
            </form>"
        );
        return html_writer::table($table);
    }

    public function view_summary(stdClass $grade, &$showviewlink) {
	    $showviewlink = true;

        $table = new html_table();
        
        $table->id = 'assignment_sqljudge_summary';
        $table->attributes['class'] = 'generaltable';
        $table->size = array('30%', '80%');

        $itemname = get_string('total_grade', 'assignfeedback_sqljudge');
            $item = html_writer::tag('span', $grade->grade);
            $table->data = array_merge(array(array($itemname, $item)), $table->data);

        $output = html_writer::table($table);
        return $output;
    }

    public function view(stdClass $grade) {
        global $DB, $OUTPUT;
        $sqljudge_ctrl = new sqljudge_controller();
        $table = new html_table();
        $table->id = 'assignment_sqljudge_summary';
        $table->attributes['class'] = 'generaltable';
        $table->size = array('30%', '80%');

        $sqlj_submission = $this->get_sqlj_submission($grade->userid);
        $get_path = '/api/statuses';
        $stats_description = $sqljudge_ctrl->get_data($get_path)[1];
        // Status
        foreach ($sqlj_submission as $task => $value) {
            $itemstyle = $value["status"] == SQLJ_STATUS_CORRECT_ANSWER
                ? 'notifysuccess'//'label label-success'
                : 'notifyproblem';//'label label-warning';
            $itemname = html_writer::tag('h5', 
                html_writer::tag('span', 
                    get_string('status_' . str_replace(' ', '_', strtolower($stats_description[$value["status"]]["name"]))
                    , 'assignfeedback_sqljudge'), array('class' => $itemstyle)));
            $item = html_writer::tag('h6', 
                html_writer::tag('span',
                    get_string('student_script', 'assignfeedback_sqljudge') . $value["script"]));
            array_push($table->data, array($itemname, $item));
        }

        $output = html_writer::table($table);
        return $output;
    }

    function get_sqlj_submission($user_id) {
        $assign_id = $this->assignment->get_instance()->id;
        $sqljudge_ctrl = new sqljudge_controller();
        $get_path = '/api/submissions/' . $user_id . '/' . $assign_id;
        $sqlj_submission = $sqljudge_ctrl->get_data($get_path)[1];
        return $sqlj_submission;
    }
    
    //POST /api/assignes
    function add_instance(stdClass $sqljAssign, $assignId) {
        $sqljudge_ctrl = new sqljudge_controller();
        if ($assignId) {
            $assign_json_array = array('assignId' => intval($assignId), 'time' => intval($sqljAssign->timelimit),
            'memory' => intval($sqljAssign->memorylimit), 'script' => $sqljAssign->checkscript, 'database' => intval($sqljAssign->testdb),
            'bannedwords' => $sqljAssign->bannedwords);
    
            $assign_json_array = json_encode($assign_json_array);
    
            $post_path = '/api/assignes/';
    
            $resp_code = $sqljudge_ctrl->post_data($assign_json_array, $post_path);
        }
        return $assignId;
    }
    
    
    function update_instance($sqljAssign, $assignId) {
        $returnid = null;
        $sqljudge_ctrl = new sqljudge_controller();

        if ($assignId) {
            $get_path = '/api/assignes/' . $assignId;
            $old_sqlj_assignment = $sqljudge_ctrl->get_data($get_path)[1];
            if (!empty($old_sqlj_assignment)) {
                $sqlj_assignment = array('assignId' => intval($assignId), 'time' => intval($sqljAssign->timelimit),
                'memory' => intval($sqljAssign->memorylimit), 'script' => $sqljAssign->checkscript, 'database' => intval($sqljAssign->testdb),
                'bannedwords' => $sqljAssign->bannedwords);
                $sqlj_assignment = json_encode($sqlj_assignment);
                $update_path = '/api/assignes/' . $assignId;
                $returnid = $sqljudge_ctrl->post_data($sqlj_assignment, $update_path);
            }
        }
        return $returnid;
    }

    public function is_empty(stdClass $grade) {
        $sqlj_submission = $this->get_sqlj_submission($grade->userid);
        
        return is_null($sqlj_submission);
    }

    public function delete_instance() {
        global $CFG, $DB;

        $sqljudge_ctrl = new sqljudge_controller();

        $delete_path = '/api/assignes/' . $this->assignment;
        $resp_code = $sqljudge_ctrl->delete_data($delete_path);
        if ($resp_code != 200)
            return false;
        
        return true;
    }

}