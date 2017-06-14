<?php
// The following define statements are the grade type format constants found in lib/grade/constants.php
/**
 * GRADE_DISPLAY_TYPE_DEFAULT - Grade display type can be set at 3 levels: grade_item, course setting and site. Use the display type from the higher level.
 */
define('GRADE_DISPLAY_TYPE_DEFAULT', 0);

/**
 * GRADE_DISPLAY_TYPE_REAL - Display the grade as a decimal number.
 */
define('GRADE_DISPLAY_TYPE_REAL', 1);

/**
 * GRADE_DISPLAY_TYPE_PERCENTAGE - Display the grade as a percentage.
 */
define('GRADE_DISPLAY_TYPE_PERCENTAGE', 2);

/**
 * GRADE_DISPLAY_TYPE_LETTER - Display the grade as a letter grade. For example, A, B, C, D or F.
 */
define('GRADE_DISPLAY_TYPE_LETTER', 3);

/**
 * GRADE_DISPLAY_TYPE_REAL_PERCENTAGE - Display the grade as a decimal number and a percentage.
 */
define('GRADE_DISPLAY_TYPE_REAL_PERCENTAGE', 12);

/**
 * GRADE_DISPLAY_TYPE_REAL_LETTER - Display the grade as a decimal number and a letter grade.
 */
define('GRADE_DISPLAY_TYPE_REAL_LETTER', 13);

/**
 * GRADE_DISPLAY_TYPE_LETTER_REAL - Display the grade as a letter grade and a decimal number.
 */
define('GRADE_DISPLAY_TYPE_LETTER_REAL', 31);

/**
 * GRADE_DISPLAY_TYPE_LETTER_PERCENTAGE - Display the grade as a letter grade and a percentage.
 */
define('GRADE_DISPLAY_TYPE_LETTER_PERCENTAGE', 32);

/**
 * GRADE_DISPLAY_TYPE_PERCENTAGE_LETTER - Display the grade as a percentage and a letter grade.
 */
define('GRADE_DISPLAY_TYPE_PERCENTAGE_LETTER', 23);

/**
 * GRADE_DISPLAY_TYPE_PERCENTAGE_REAL - Display the grade as a percentage and a decimal number.
 */
define('GRADE_DISPLAY_TYPE_PERCENTAGE_REAL', 21);

$instanceMap = GradeSubmit::getInstanceMap($instanceID);            // Replace this with your own array with the Moodle URL and Web Service Token.

$url    = $instanceMap['base_url'] . 'webservice/rest/server.php';  // The path to your Moodle instance.
$token  = $instanceMap['ws_token'];                                 // The Web Service Token created in Moodle.
$data_defaults = [
    'wstoken' => $token,
    'moodlewsrestformat' => 'json',
];

$moodleWebSvcData   = $data_defaults + [
    'wsfunction'    => 'gradereport_overview_get_course_users_grade',
    'courseid'      => $courseID,
    'userid'        => '0',
    'gradeformat'   => GRADE_DISPLAY_TYPE_LETTER,
];

$moodleResults = GradeSubmit::getMoodleUsersGrades($url, $moodleWebSvcData);

Abstract Class GradeSubmit {

    public static function getMoodleUsersGrades($url, $data) {
        $use_fopen = (bool) ini_get('allow_url_fopen');
        if ($use_fopen) {
            $stream_options = [
                'http' => [
                    'method' => 'POST',
                    'header' => [
                        'Content-type: application/x-www-form-urlencoded',
                    ],
                    'content' => http_build_query($data),
                ],
                'ssl' => [
                    'verify_peer' => false,
                ],
            ];
            $context    = stream_context_create($stream_options);
            if ($result = file_get_contents($url, false, $context)) {
                return json_decode($result, true);
            }
            else {
                return false;
            }
        }
        elseif (extension_loaded('curl')) {
            $ch = curl_init();
            $options = [
                CURLOPT_URL => $url,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $data,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_SSL_VERIFYPEER => false,
            ];
            curl_setopt_array($ch, $options);
            $result = curl_exec($ch);
            curl_close($ch);
            return $result;
        }
        else {
            throw new Exception('Could not find a suitable way to communicate with the server! Please make sure that either allow_url_fopen is On (preferred) or that curl is installed.');
        }
    }
}
