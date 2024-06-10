<?php

$string['check'] = 'Check';
$string['checked'] = 'Checked';
$string['checkerrorcode'] = 'Request failed with HTTP code {$a}';
$string['checkfailed'] = 'Checking submission failed';
$string['checkscript'] = 'Check script';
$string['checkscript_help'] = 'This field is intended for entering scripts, which will later be used to check the scripts of students.
At the moment, the plugin is able to check the SELECT, INSERT, UPDATE, DELETE and CREATE INDEX script.
1. To check the SELECT queries, enter a script containing all the necessary fields and the correct table.
    Also pay attention to the description of the assignment, in order for students to receive the correct verification, it is necessary to report the output columns.
2. To check INSERT, UPDATE, DELETE queries, enter a SELECT script containing inserted, updated, or deleted data.
3. To check the CREATE INDEX, enter the SELECT script containing the required set of columns, as well as the execution time limit.';
$string['correctanswer'] = 'Correct answer';
$string['createdbscript'] = 'Script of DB creation';
$string['dbdescription'] = 'Database description';
$string['dbname'] = 'Database name';
$string['dbms'] = 'DBMS';
$string['dbmsfull'] = 'Database management system';
$string['enabled'] = 'SQL Judge';
$string['enabled_help'] = 'Automatically grade submitted SQL code by testing them against predefined behaviour.';
$string['hint'] = 'Hint';
$string['hinthelp'] = 'May help students on wrong answer';
$string['maxtime'] = 'Maximum time';
$string['maxtimehelp'] = 'Default maximum script running time for all assignments on the site';
$string['bannedwords'] = 'Banned or required keywords/phrases';
$string['mustcontainhelp'] = 'One phrase per line\nLines with banned phrases should start with !\nLines starting with # are commented out';
$string['output'] = 'Judge system output';
$string['pluginname'] = 'SQL Judge';
$string['status'] = 'Status';
$string['status_help'] = 'Status indicates the results given by the SQL Judge. The meanings are listed below:

- Pending: submission hasn\'t been or is being checked now
- Accepted: submission has been checked and answer is correct
- Wrong answer: submission has been checked and answer is not correct
- Banned or required words content: the teacher has set keywords/phrases that must or must not appear in the answer, and yours violates this rule
- Contains restricted functions: answer contains potentionally dangerous functions and cannot be checked
- Time limit exceeded: your script ran longer than the maximum time allowed
- Unknown error: any other error that doesn\'t fit into the ones mentioned above, please contact the teacher';
$string['testdb'] = 'Test database';
$string['testdb_help'] = 'The database to check answer scripts on';
$string['testedon'] = 'Tested on';
$string['timelimit'] = 'Time limit, ms';
$string['memorylimit'] = 'Memory limit, kb';
$string['status_bad_word'] = 'Contains bad words';
$string['status_admission_word'] = 'Does not contains admission words';
$string['status_correct_answer'] = 'Correct answer';
$string['status_wrong_answer'] = 'Wrong answer';
$string['status_time_limit_exceeded'] = 'Time limit exceeded';
$string['status_memory_limit_exceeded'] = 'Memory limit exceeded';
$string['status_unknown_error'] = 'Unknown error';
$string['status_not_checked'] = 'Checking scripts';
$string['status_checked'] = 'Checked';
$string['student_script'] = 'Student script: ';
$string['dbsettings_redirect'] = 'Add new database';
$string['checkscript_template'] = "To enter scripts for verification using this template:
1. script for verification;
2. script for verification;
3. script for verification;
4. script for verification;
5. script for verification;
....";
$string['bannedwords_template'] = "Forbidden words and phrases should be entered using this template, an exclamation mark means the end of the list:
1. Prohibition: word1, word2, phrase 1 ... ! | Admission: word1, word2, phrase 1 ... !;
2. Prohibition: word1, word2, phrase 1 ... ! | Admission: word1, word2, phrase 1 ... !;
3. Prohibition: word1, word2, phrase 1 ... ! | Admission: word1, word2, phrase 1 ... !;
4. Prohibition: word1, word2, phrase 1 ... ! | Admission: word1, word2, phrase 1 ... !;
5. Prohibition: word1, word2, phrase 1 ... ! | Admission: word1, word2, phrase 1 ... !;
....";
$string["total_grade"] = "Grade:";