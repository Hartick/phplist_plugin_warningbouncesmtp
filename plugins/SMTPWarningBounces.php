<?php

class SMTPWarningBounces extends phplistPlugin {

    public $coderoot = PLUGIN_ROOTDIR . "/SMTPWarningBounces/";

    const VERSION_FILE = 'version.txt';
    const PLUGIN = 'SMTPWarningBounces';

    public $name = 'SMTP Warning Bounces';
    public $enabled = true;
    public $authors = 'Giuseppe Scarfò';
    public $description = 'Allows to incercept failed sended mail and to bouncing it.';
    public $priority = 100;

    function __construct()
    {
        $this->coderoot = dirname(__FILE__) . '/' . __CLASS__ . '/';
        parent::__construct();
        $this->version = file_get_contents($this->coderoot . self::VERSION_FILE);
    }

    public function logEvent($msg = "") {
        $completeString = "Error sending email to %s";
        $translatedString = s($completeString);
        $pattern = '/[a-z0-9_\-\+\.]+@[a-z0-9\-]+\.([a-z]{2,4})(?:\.[a-z]{2})?/i';
        preg_match_all($pattern, $msg, $matches);
        if(empty($matches)) {
            return false;
        }
        $mail = $matches[0];
        $message_copy = str_replace($mail, '', $msg);
        $translatedString = substr($translatedString, 0, strlen($translatedString) - 2);
        $completeString = substr($completeString, 0, strlen($completeString) - 2);
        if(strpos($message_copy,$translatedString) !== FALSE || strpos($message_copy,$completeString) !== FALSE)  {
            global $tables;
            $bouncerules = loadBounceRules();
            $ruletoApply = matchBounceRules($message_copy, $bouncerules);
            $table_user = $tables['user'];
            $query = "select * from $table_user where email = '".$mail[0]."'";
            $userdata = Sql_Fetch_Array_Query($query);
            $user_id = $userdata['id'];
            if (isset($ruletoApply['action']) && ($ruletoApply['action'] == "deleteuser" || $ruletoApply['action'] == "deleteuserandbounce" )) {
                deleteUser($user_id);
            }
            return true;
        }
        return false;
    }
}
?>