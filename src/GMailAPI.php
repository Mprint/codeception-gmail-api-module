<?php
namespace Codeception\Module;
use Codeception\Module;
class GMailAPI extends Module
{
    protected $requiredFields = array('client_id', 'client_secret', 'refresh_token');

    /** @var \Google_Client */
    protected $client;

    /** @var \Google_Service_Gmail */
    protected $service;

    public function _initialize() {
        $this->client = new \Google_Client();
        $this->client->setClientId($this->config['client_id']);
        $this->client->setClientSecret($this->config['client_secret']);
        $this->client->addScope('https://mail.google.com/');

        $this->service = new \Google_Service_Gmail($this->client);
    }

    public function _before(\Codeception\TestCase $test) {
        $this->_refreshToken();
    }

    /**
     * See In Last Email subject
     *
     * Look for a string in the most recent email subject
     *
     * @param $expected string
     * @return void
     **/
    public function seeInLastEmailSubject($expected)
    {
        $email = $this->lastMessage();
        $this->seeInEmailSubject($email, $expected);
    }

    /**
     * Don't See In Last Email subject
     *
     * Look for the absence of a string in the most recent email subject
     *
     * @param $expected string
     * @return void
     **/
    public function dontSeeInLastEmailSubject($expected)
    {
        $email = $this->lastMessage();
        $this->dontSeeInEmailSubject($email, $expected);
    }


    /**
     * Last Message
     *
     * Get the most recent email
     *
     * @return /Google_Service_Gmail_Message
     **/
    protected function lastMessage() {
        $messages = $this->messages(1);
        if (empty($messages)) {
            $this->fail("No messages received");
        }

        /** @var /Google_Service_Gmail_Message $last */
        $last = array_shift($messages);

        return $this->emailFromId($last->id);
    }

    /**
     * Messages
     *
     * Get an array of all the message objects
     *
     * @param $maxResults int
     * @return array
     **/
    protected function messages($maxResults = 100) {
        /** @var /Google_Service_Gmail_ListMessagesResponse $list */
        $list = $this->service->users_messages->listUsersMessages('me',['maxResults' => $maxResults]);
        return $list->getMessages();
    }

    /**
     * Email from ID
     *
     * Given a GMail id, returns the email's object
     *
     * @return /Google_Service_Gmail_Message
     **/
    protected function emailFromId($id) {
        return $this->service->users_messages->get('me',$id,['format' => 'full']);
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
        $this->assertContains($expected, $this->getEmailHeader($email, 'Subject'), "Email Subject Contains");
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
        $this->assertNotContains($unexpected, $this->getEmailHeader($email, 'Subject'), "Email Subject Does Not Contain");
    }

    /**
     * @param $email /Google_Service_Gmail_Message
     * @param $headerName string
     * @return string
     */
    protected function getEmailHeader($email, $headerName) {
        foreach ($email->getPayload()->getHeaders() as $header) {
            if ($header['name'] == $headerName) return $header['value'];
        }
        return '';
    }

    public function _refreshToken() {
        $this->client->refreshToken($this->config['refresh_token']);
    }
}