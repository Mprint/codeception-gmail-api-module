<?php
namespace Codeception\Module;
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
        $email = $this->getLastMessage($filters);
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
        $email = $this->getLastMessage($filters);
        $this->dontSeeInEmail($email, $unexpected);
    }


    /**
     * See In Last Email From
     *
     * Look for a string in the most recent email sent to $address
     *
     * @param $address string
     * @param $expected string
     * @return void
     **/
    public function seeInLastEmailFrom($address, $expected) {
        $email = $this->getLastMessageFrom($address);
        $this->seeInEmail($email, $expected);

    }


    /**
     * Don't See In Last Email From
     *
     * Look for the absence of a string in the most recent email sent to $address
     *
     * @param $address string
     * @param $unexpected string
     * @return void
     **/
    public function dontSeeInLastEmailFrom($address, $unexpected) {
        $email = $this->getLastMessageFrom($address);
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
        $email = $this->getLastMessage($filters);
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
        $email = $this->getLastMessage($filters);
        $this->dontSeeInEmailSubject($email, $expected);
    }


    /**
     * See In Last Email Subject From
     *
     * Look for a string in the most recent email subject sent to $address
     *
     * @param $address string
     * @param $expected string
     * @return void
     **/
    public function seeInLastEmailSubjectFrom($address, $expected) {
        $email = $this->getLastMessageFrom($address);
        $this->seeInEmailSubject($email, $expected);

    }


    /**
     * Don't See In Last Email Subject From
     *
     * Look for the absence of a string in the most recent email subject sent to $address
     *
     * @param $address string
     * @param $unexpected string
     * @return void
     **/
    public function dontSeeInLastEmailSubjectFrom($address, $unexpected) {
        $email = $this->getLastMessageFrom($address);
        $this->dontSeeInEmailSubject($email, $unexpected);
    }


    /**
     * Grab From Last Email
     *
     * Look for a regex in the email source and return it
     *
     * @param $regex string
     * @param $filters array
     * @return string
     **/
    public function grabFromLastEmail($regex, $filters = array()) {
        $matches = $this->grabMatchesFromLastEmail($regex, $filters);
        return $matches[0];
    }


    /**
     * Grab From Last Email From
     *
     * Look for a regex in most recent email sent to $address email body and
     * return it
     *
     * @param $address string
     * @param $regex string
     * @return string
     **/
    public function grabFromLastEmailFrom($address, $regex) {
        $matches = $this->grabMatchesFromLastEmailFrom($address, $regex);
        return $matches[0];
    }


    /**
     * Grab Matches From Last Email
     *
     * Look for a regex in the email source and return it's matches
     *
     * @param $regex string
     * @param $filters array
     * @return array
     **/
    public function grabMatchesFromLastEmail($regex, $filters = array()) {
        $email = $this->getLastMessage($filters);
        $matches = $this->grabMatchesFromEmail($email, $regex);
        return $matches;
    }


    /**
     * Grab Matches From Last Email From
     *
     * Look for a regex in most recent email sent to $address email source and
     * return it's matches
     *
     * @param $address string
     * @param $regex string
     * @return array
     **/
    public function grabMatchesFromLastEmailFrom($address, $regex) {
        $email = $this->getLastMessageFrom($address);
        $matches = $this->grabMatchesFromEmail($email, $regex);
        return $matches;
    }


    /**
     * Waits for email from $address to be received or for $timeout seconds to pass.
     *
     * @param $address
     * @param int $timeout
     * @throws \Codeception\Exception\TimeOut
     */
    public function waitForEmailFrom($address, $timeout = 10) {
        $this->remoteMail->wait($timeout)->until(GMailExpectedCondition::emailFrom($address));
    }

    public function waitForEmail($filters = array(), $timeout = 10) {
        $this->remoteMail->wait($timeout)->until(GMailExpectedCondition::emails($filters));
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

    /********************
     *  Helper function
     ********************/


    /**
     * Last Message From
     *
     * Get the most recent email sent to $address
     *
     * @param $address string
     * @return \Google_Service_Gmail_Message
     **/
    protected function getLastMessageFrom($address) {
        return $this->getLastMessage(array('from' => $address), "No messages sent to {$address}");
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
    protected function getLastMessage($filters = array(), $message = "No messages received") {
        $lastEmail = $this->remoteMail->getLastEmail($filters);

        if (is_null($lastEmail)) {
            $this->fail($message);
        }

        return $lastEmail;
    }
}