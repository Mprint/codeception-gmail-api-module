<?php
namespace Codeception\Module;
use Codeception\Module;
use Codeception\Lib\Interfaces\Mail;
use Codeception\Util\Driver\GMail;
use Codeception\Exception\TimeOut;

class GMailAPI extends Module implements Mail
{
    protected $requiredFields = array('client_id', 'client_secret', 'refresh_token');

    /** @var GMail */
    protected $driver;

    public function _initialize() {
        $this->driver = GMail::createByParams($this->config['client_id'], $this->config['client_secret'], $this->config['refresh_token']);
        $this->driver->setBaseFilter($this->config['base_filter']);
    }

    /**
     * Ensure that the active token has not expired
     *
     * TODO: take an other look on the implementation of wait*()
     *
     * @param \Codeception\Step $step
     */
    public function _beforeStep(\Codeception\Step $step) {
        $this->driver->refreshToken();
    }

    /**
     * @return \Google_Client
     */
    public function _getClient() {
        return $this->driver->getClient();
    }

    /**
     * @return \Google_Service_Gmail
     */
    public function _getService() {
        return $this->driver->getService();
    }

    /**
     * @return GMail
     */
    public function _getDriver() {
        return $this->driver;
    }

    /**
     * See In Last Email
     *
     * Look for a string in the most recent email
     *
     * @param string        $expected
     * @param array|string  $filters
     * @param string        $message
     * @return void
     **/
    public function seeInLastEmail($expected, $filters = array(), $message = '') {
        $email = $this->getLastEmail($filters);
        $this->seeInEmail($email, $expected);
    }


    /**
     * Don't See In Last Email
     *
     * Look for the absence of a string in the most recent email
     *
     * @param string        $unexpected
     * @param array|string  $filters
     * @param string        $message
     * @return void
     **/
    public function dontSeeInLastEmail($unexpected, $filters = array(), $message = '') {
        $email = $this->getLastEmail($filters);
        $this->dontSeeInEmail($email, $unexpected);
    }


    /**
     * See In Last Email subject
     *
     * Look for a string in the most recent email subject
     *
     * @param string        $expected
     * @param array|string  $filters
     * @param string        $message
     * @return void
     **/
    public function seeInLastEmailSubject($expected, $filters = array(), $message = 'Email Subject dose not contains') {
        $email = $this->getLastEmail($filters);
        $this->seeInEmailHeader($email, $expected, 'Subject', $message);
    }


    /**
     * Don't See In Last Email subject
     *
     * Look for the absence of a string in the most recent email subject
     *
     * @param string        $unexpected
     * @param array|string  $filters
     * @param string        $message
     * @return void
     **/
    public function dontSeeInLastEmailSubject($unexpected, $filters = array(), $message = 'Email Subject contain') {
        $email = $this->getLastEmail($filters);
        $this->dontSeeInEmailHeader($email, $unexpected, 'Subject', $message);
    }


    /**
     * Grab From Last Email
     *
     * Look for a regex in the email source and return the first occurrence
     *
     * @param string        $regex
     * @param array|string  $filters
     * @param string        $message
     * @return string
     **/
    public function grabFromLastEmail($regex, $filters = array(), $message = 'No match fount in email') {
        $matches = $this->grabMatchesFromLastEmail($regex, $filters);
        //TODO:: try to print the grab text to console. @see writeln()
        if(empty($matches)) {
            $this->fail($message);
        }
        return $matches[0];
    }


    /**
     * Grab Matches From Last Email
     *
     * Look for a regex in the email source and return it's matches
     *
     * @param string $regex
     * @param array|string $filters
     * @param string $message
     * @return array
     **/
    public function grabMatchesFromLastEmail($regex, $filters = array(), $message = 'No matches found in email') {
        $email = $this->getLastEmail($filters);
        $matches = $this->grabMatchesFromEmail($email, $regex);
        //TODO:: try to print the grab text to console. @see writeln()
        if(empty($matches)) {
            $this->fail($message);
        }
        return $matches;
    }

    /**
     * Waits for an email to be received or $timeout seconds to pass.
     *
     * @param array|string  $filters
     * @param int           $timeout
     * @param string        $message
     * @return void
     */
    public function waitForEmail($filters = array(), $timeout = 10, $message = 'No email received') {
        try{
            $this->driver->wait($timeout)->until(\Codeception\Lib\Expectation\GMail::emails($filters));
        } catch (TimeOut $e) {
            $this->fail($message);
        }
    }

    /**
     * Test email count equals expected value
     *
     * @param int $expected
     * @param array|string $filters
     * @param string $message
     */
    public function seeEmailCountEquals($expected, $filters = array(), $message = '') {
        $this->assertEquals($expected, $this->driver->getEmailCount($filters), $message);
    }

    /**
     * Test email count greater then expected value
     *
     * @param int $expected
     * @param array|string $filters
     * @param string $message
     */
    public function seeEmailCountGreaterThan($expected, $filters = array(), $message = '') {
        $this->assertGreaterThan($expected, $this->driver->getEmailCount($filters), $message);
    }

    /**
     * Test email count greater then or equals expected value
     *
     * @param int $expected
     * @param array|string $filters
     * @param string $message
     */
    public function seeEmailCountGreaterThanOrEqual($expected, $filters = array(), $message = '') {
        $this->assertGreaterThanOrEqual($expected, $this->driver->getEmailCount($filters), $message);
    }

    /**
     * Test email count less then expected value
     *
     * @param int $expected
     * @param array|string $filters
     * @param string $message
     */
    public function seeEmailCountLessThan($expected, $filters = array(), $message = '') {
        $this->assertLessThan($expected, $this->driver->getEmailCount($filters), $message);
    }

    /**
     * Test email count less then or equals expected value
     *
     * @param int $expected
     * @param array|string $filters
     * @param string $message
     */
    public function seeEmailCountLessThanOrEqual($expected, $filters = array(), $message = '') {
        $this->assertLessThanOrEqual($expected, $this->driver->getEmailCount($filters), $message);
    }


    /**
     * See In Email
     *
     * Look for a string in an email
     *
     * @param  /Google_Service_Gmail_Message $email
     * @param  string                        $expected
     * @param  string                        $message
     * @return void
     **/
    protected function seeInEmail($email, $expected, $message = '') {
        $this->assertContains($expected, $this->driver->getEmailContent($email), $message);
    }

    /**
     * Don't See In Email
     *
     * Look for the absence of a string in an email
     *
     * @param  /Google_Service_Gmail_Message $email
     * @param  string                        $unexpected
     * @param  string                        $message
     * @return void
     **/
    protected function dontSeeInEmail($email, $unexpected, $message = '') {
        $this->assertNotContains($unexpected, $this->driver->getEmailContent($email), $message);
    }

    /**
     * See In Subject
     *
     * Look for a string in an email subject
     *
     * @param  /Google_Service_Gmail_Message $email
     * @param  string                        $expected
     * @param  string                        $headerName
     * @param  string                        $message
     * @return void
     **/
    protected function seeInEmailHeader($email, $expected, $headerName = 'Subject', $message = '') {
        $this->assertContains($expected, $this->driver->getEmailHeader($email, $headerName), $message);
    }

    /**
     * Don't See In Subject
     *
     * Look for the absence of a string in an email subject
     *
     * @param  /Google_Service_Gmail_Message $email
     * @param  string                        $unexpected
     * @param  string                        $headerName
     * @param  string                        $message
     * @return void
     **/
    protected function dontSeeInEmailHeader($email, $unexpected, $headerName = 'Subject', $message = '') {
        $this->assertNotContains($unexpected, $this->driver->getEmailHeader($email, $headerName), $message);
    }

    /**
     * Grab From Email
     *
     * Return the matches of a regex against the raw email
     *
     * @param \Google_Service_Gmail_Message $email
     * @param string                        $regex
     * @return array
     **/
    protected function grabMatchesFromEmail($email, $regex)
    {
        preg_match($regex, $this->driver->getEmailContent($email), $matches);
        $this->assertNotEmpty($matches, "No matches found for $regex");
        return $matches;
    }

    /**
     * Last Message
     *
     * Get the most recent email
     *
     * @param array|string  $filters
     * @param string        $message
     * @return \Google_Service_Gmail_Message
     **/
    protected function getLastEmail($filters = array(), $message = "No email received") {
        $lastEmail = $this->driver->getLastEmail($filters);

        if (is_null($lastEmail)) {
            $this->fail($message);
        }

        return $lastEmail;
    }
}