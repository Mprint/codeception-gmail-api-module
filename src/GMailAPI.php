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
     * See In Last Email
     *
     * Look for a string in the most recent email
     *
     * @param $expected string
     * @return void
     **/
    public function seeInLastEmail($expected)
    {
        $email = $this->lastMessage();
        $this->seeInEmail($email, $expected);
    }


    /**
     * Don't See In Last Email
     *
     * Look for the absence of a string in the most recent email
     *
     * @param $unexpected string
     * @return void
     **/
    public function dontSeeInLastEmail($unexpected)
    {
        $email = $this->lastMessage();
        $this->dontSeeInEmail($email, $unexpected);
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
     * @return \Google_Service_Gmail_Message
     **/
    protected function lastMessage() {
        $messages = $this->messages(array('maxResults' => 1));
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
     * @param $params array
     * @return array
     **/
    protected function messages($params = array()) {
        $defaultParam = array('maxResults' => 100);
        $params = array_merge($defaultParam, $params);
        /** @var /Google_Service_Gmail_ListMessagesResponse $list */
        $list = $this->service->users_messages->listUsersMessages('me', $params);
        return $list->getMessages();
    }

    /**
     * Email from ID
     *
     * Given a GMail id, returns the email's object
     *
     * @param $id string
     * @return \Google_Service_Gmail_Message
     **/
    protected function emailFromId($id) {
        return $this->service->users_messages->get('me', $id, array('format' => 'full'));
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
    protected function seeInEmail($email, $expected)
    {
        $this->assertContains($expected, $this->getEmailContent($email), "Email Contains");
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
    protected function dontSeeInEmail($email, $unexpected)
    {
        $this->assertNotContains($unexpected, $this->getEmailContent($email), "Email Does Not Contain");
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

    /********************
     *  Helper function
     ********************/

    /**
     * @param $email /Google_Service_Gmail_Message
     * @param $headerName string
     * @return string
     */
    protected function getEmailHeader($email, $headerName) {
        return $this->getHeaderValue($email->getPayload()->getHeaders(), $headerName);
    }

    /**
     * @param $headers array
     * @param $headerName string
     * @return string
     */
    protected function getHeaderValue($headers, $headerName) {
        foreach ($headers as $header) {
            if (!isset($header['name']) || !isset($header['value'])) continue;
            if ($header['name'] == $headerName) return $header['value'];
        }
        return '';
    }

    /**
     * @param $email /Google_Service_Gmail_Message
     * @param $type string 'html' | 'plain'
     * @return string
     */
    protected function getEmailContent($email, $type = 'plain') {
        if(!in_array($type, array('html', 'plain'))) {
            $type = 'plain';
        }

        foreach($email->getPayload()->getParts() as $emailPart) {
            if ($emailPart->mimeType != 'text/'.$type) continue;
            return $this->base64url_decode($emailPart['body']['data']);
        }
        return '';
    }

    /**
     * Custom base64 encode function required by GMail API
     *
     * @param $data
     * @return string
     */
    protected function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Custom base64 decode function required by GMail API
     *
     * @param $data
     * @return string
     */
    protected function base64url_decode($data) {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }

    public function _refreshToken() {
        $this->client->refreshToken($this->config['refresh_token']);
    }
}