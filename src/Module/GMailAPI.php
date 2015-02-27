<?php
namespace Codeception\Module;
use Codeception\Exception\TimeOut;
use Codeception\Module;
use Codeception\Util\GMailExpectedCondition;
use Codeception\Util\MailInterface;
use Codeception\Util\GMailRemote;

class GMailAPI extends Module implements MailInterface
{
    protected $requiredFields = array('client_id', 'client_secret', 'refresh_token');

    /** @var \Google_Service_Gmail */
    protected $service;

    /** @var GMailRemote */
    protected $remoteMail;

    public function _initialize() {
        $this->remoteMail = GMailRemote::createByParams($this->config['client_id'], $this->config['client_secret'], $this->config['refresh_token']);
        $this->remoteMail->setBaseFilter($this->config['base_filter']);
    }

    /**
     * Ensure that the active token has not expired
     *
     * TODO: take an other look on the implementation of wait*()
     *
     * @param \Codeception\Step $step
     */
    public function _beforeStep(\Codeception\Step $step) {
        $this->remoteMail->refreshToken();
    }

    /**
     * @return \Google_Client
     */
    public function _getClient() {
        return $this->remoteMail->getClient();
    }

    /**
     * @return \Google_Service_Gmail
     */
    public function _getService() {
        return $this->remoteMail->getService();
    }

    /**
     * @return GMailRemote
     */
    public function _getRemoteMail() {
        return $this->remoteMail;
    }

    /**
     * See In Last Email
     *
     * Look for a string in the most recent email
     *
     * @param $expected string
     * @param $filters array|string
     * @return void
     **/
    public function seeInLastEmail($expected, $filters = array()) {
        $email = $this->getLastEmail($filters);
        $this->seeInEmail($email, $expected);
    }


    /**
     * Don't See In Last Email
     *
     * Look for the absence of a string in the most recent email
     *
     * @param $unexpected string
     * @param $filters array|string
     * @return void
     **/
    public function dontSeeInLastEmail($unexpected, $filters = array()) {
        $email = $this->getLastEmail($filters);
        $this->dontSeeInEmail($email, $unexpected);
    }


    /**
     * See In Last Email subject
     *
     * Look for a string in the most recent email subject
     *
     * @param $expected string
     * @param $filters array|string
     * @return void
     **/
    public function seeInLastEmailSubject($expected, $filters = array()) {
        $email = $this->getLastEmail($filters);
        $this->seeInEmailSubject($email, $expected);
    }


    /**
     * Don't See In Last Email subject
     *
     * Look for the absence of a string in the most recent email subject
     *
     * @param $expected string
     * @param $filters array|string
     * @return void
     **/
    public function dontSeeInLastEmailSubject($expected, $filters = array()) {
        $email = $this->getLastEmail($filters);
        $this->dontSeeInEmailSubject($email, $expected);
    }


    /**
     * Grab From Last Email
     *
     * Look for a regex in the email source and return it
     *
     * @param $regex string
     * @param $filters array|string
     * @return string
     **/
    public function grabFromLastEmail($regex, $filters = array()) {
        $matches = $this->grabMatchesFromLastEmail($regex, $filters);
        //TODO:: try to print the grab text to console. @see writeln()
        return $matches[0];
    }


    /**
     * Grab Matches From Last Email
     *
     * Look for a regex in the email source and return it's matches
     *
     * @param $regex string
     * @param $filters array|string
     * @return array
     **/
    public function grabMatchesFromLastEmail($regex, $filters = array()) {
        $email = $this->getLastEmail($filters);
        $matches = $this->grabMatchesFromEmail($email, $regex);

        //TODO:: try to print the grab text to console. @see writeln()
        return $matches;
    }

    /**
     * Waits for an email to be received or $timeout seconds to pass.
     *
     * @param $filters array|string
     * @param $timeout int
     * @param $message string
     * @return void
     */
    public function waitForEmail($filters = array(), $timeout = 10, $message = '') {
        try{
            $this->remoteMail->wait($timeout)->until(GMailExpectedCondition::emails($filters));
        } catch (TimeOut $e) {
            $this->fail($message);
        }
    }


    /**
     * See In Email
     *
     * Look for a string in an email
     *
     * @param $email /Google_Service_Gmail_Message
     * @param $expected string
     * @return void
     **/
    protected function seeInEmail($email, $expected) {
        $this->assertContains($expected, $this->remoteMail->getEmailContent($email), "Email Contains");
    }

    /**
     * Don't See In Email
     *
     * Look for the absence of a string in an email
     *
     * @param $email /Google_Service_Gmail_Message
     * @param $unexpected string
     * @return void
     **/
    protected function dontSeeInEmail($email, $unexpected) {
        $this->assertNotContains($unexpected, $this->remoteMail->getEmailContent($email), "Email Does Not Contain");
    }

    /**
     * See In Subject
     *
     * Look for a string in an email subject
     *
     *
     * @param $email /Google_Service_Gmail_Message
     * @param $expected string
     * @return void
     **/
    protected function seeInEmailSubject($email, $expected) {
        $this->assertContains($expected, $this->remoteMail->getEmailHeader($email, 'Subject'), "Email Subject Contains");
    }

    /**
     * Don't See In Subject
     *
     * Look for the absence of a string in an email subject
     *
     * @param $email /Google_Service_Gmail_Message
     * @param $unexpected string
     * @return void
     **/
    protected function dontSeeInEmailSubject($email, $unexpected) {
        $this->assertNotContains($unexpected, $this->remoteMail->getEmailHeader($email, 'Subject'), "Email Subject Does Not Contain");
    }

    /**
     * Grab From Email
     *
     * Return the matches of a regex against the raw email
     *
     * @param $email \Google_Service_Gmail_Message
     * @param $regex string
     * @return array
     **/
    protected function grabMatchesFromEmail($email, $regex)
    {
        preg_match($regex, $this->remoteMail->getEmailContent($email), $matches);
        $this->assertNotEmpty($matches, "No matches found for $regex");
        return $matches;
    }

    /**
     * Last Message
     *
     * Get the most recent email
     *
     * @param $filters array|string
     * @param $message string
     * @return \Google_Service_Gmail_Message
     **/
    protected function getLastEmail($filters = array(), $message = "No messages received") {
        $lastEmail = $this->remoteMail->getLastEmail($filters);

        if (is_null($lastEmail)) {
            $this->fail($message);
        }

        return $lastEmail;
    }
}