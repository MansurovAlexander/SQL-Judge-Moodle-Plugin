<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/local/sqljudge/judgelib.php');

global $COURSE;

class sqljudge_dbadd_form extends moodleform {

    public function definition() {
        $mform = $this->_form;

        $mform->addElement('header', 'general', 
            get_string('dbadd', 'local_sqljudge'));

        $mform->addElement('text', 'name', 
            get_string('dbadd_name', 'local_sqljudge'));
        $mform->setType('name', PARAM_TEXT);

        $mform->addElement('text', 'description', 
            get_string('dbadd_description', 'local_sqljudge'));
        $mform->setType('description', PARAM_TEXT);

        $mform->addElement('select', 'dbms', 
            get_string('dbadd_dbms', 'local_sqljudge'),
            sqljudge_get_supported_dbms_list());
        $mform->setType('dbms', PARAM_ALPHA);

        $mform->addElement( 'filepicker', 'db_script',
            get_string('dbadd_loadfile', 'local_sqljudge'),
            [
                'accepted_types' => 'zip',
            ]);

        $this->add_action_buttons(true, get_string('dbadd_submit', 'local_sqljudge'));
    }
}

class sqljudge_dbdrop_form extends moodleform {
    public function definition() {

        $mform = $this->_form;

        $mform->addElement('header', 'general', get_string('dbman', 'local_sqljudge'));

        $databases = get_databases();
        if (!empty($databases)) {
            $mform->addElement('select', 'databaseid', 
                get_string('dbman_dbselect', 'local_sqljudge'), 
                $databases);
            $mform->setType('databaseid', PARAM_INT);
            $mform->setDefault('databaseid', reset($databases));
        }

        $dropButtons = array();
        $dropButtons[] = &$mform->createElement('submit', 'dropdelete', 
            get_string('dbman_dropdelete', 'local_sqljudge'));
        $mform->addGroup($dropButtons, 'submitButtons', '', array(' '), false);
    }
}
$url_params = $_SERVER['QUERY_STRING'];
$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_url('/local/sqljudge/index.php');
$PAGE->set_title(get_string('pluginname', 'local_sqljudge'));
$PAGE->set_heading("$SITE->shortname: " . get_string('pluginname', 'local_sqljudge'));

$output = $PAGE->get_renderer('local_sqljudge');

/// Output starts here
echo $output->header();
/// About
echo $output->heading(get_string('about', 'local_sqljudge'), 1);

if (!empty($url_params)) {
    //Если произошел редирект с редактирования задания
    echo $output->container(get_string('aboutcontent', 'local_sqljudge') . PHP_EOL . "<a href='/course/modedit.php?" . $url_params . "'>" . get_string('return_to_task', 'local_sqljudge') . "</a>", 'box copyright');
} else 
    echo $output->container(get_string('aboutcontent', 'local_sqljudge'), 'box copyright');

// Process form submission
$dbdrop_form = new sqljudge_dbdrop_form();
$dbadd_form = new sqljudge_dbadd_form();

if ($dbdropdata = $dbdrop_form->get_data()) {
    if (!empty($dbdropdata->dropdelete)) {
        $delete_path = '/api/databases/' . $dbdropdata->databaseid;
        $respcode = delete_data($delete_path);

        if ($respcode === 200) {
            echo $output->notification(
                get_string('dbman_deleted', 'local_sqljudge'), 'notifysuccess');
                redirect($CFG->wwwroot . "/local/sqljudge/index.php?" . $url_params);
        } else {
            echo $output->notification(
                get_string('dbman_error', 'local_sqljudge') . ':' . $respcode, 'notifyerror');
                redirect($CFG->wwwroot . "/local/sqljudge/index.php?" . $url_params);
        }
    }
}

if ($data = $dbadd_form->get_data()) {
    // Form submitted and data is valid
    $name = str_replace(' ', '-', strtolower($data->name));
    $description = $data->description;
    $dbms = $data->dbms;
    $db_name = $dbadd_form->get_new_filename('db_script');
    $db_file_content = $dbadd_form->get_file_content("db_script");
    
    //Convert file content to base64
    $db_file_content_base64 = base64_encode($db_file_content) ;

    //Create JSON string with db data
    $db_json_array = array('fileName' => $name, 'fileExtension' => "zip",
    "base64File" => $db_file_content_base64, "description" => $description, "dbms" => "1");
    $db_json_array = json_encode($db_json_array);
    
    $post_path = '/api/databases';
    $resp = post_data($db_json_array, $post_path);
    
    //Validate responce
    if ($resp != 200){
        echo $resp;
        echo $output->notification(
            get_string('dbman_error', 'local_sqljudge'), 'notifyerror');
            redirect($CFG->wwwroot . "/local/sqljudge/index.php?" . $url_params);
    } else {
        echo $output->notification('Data inserted successfully.', 'notifysuccess');
        redirect($CFG->wwwroot . "/local/sqljudge/index.php?" . $url_params);
    }
}

$dbcreate_form->display();
$dbadd_form->display();

echo $output->footer();