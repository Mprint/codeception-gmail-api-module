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

    public function _beforeStep(\Codeception\Step $step) {
        if($this->client->isAccessTokenExpired()) {
            $this->_refreshToken();
        }
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
        $email = $this->getLastMessage();
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
        $email = $this->getLastMessage();
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
    public function seeInLastEmailFrom($address, $expected)
    {
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
    public function dontSeeInLastEmailFrom($address, $unexpected)
    {
        $email = $this->getLastMessageFrom($address);
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
        $email = $this->getLastMessage();
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
        $email = $this->getLastMessage();
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
    public function seeInLastEmailSubjectFrom($address, $expected)
    {
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
    public function dontSeeInLastEmailSubjectFrom($address, $unexpected)
    {
        $email = $this->getLastMessageFrom($address);
        $this->dontSeeInEmailSubject($email, $unexpected);
    }


    /**
     * Grab From Last Email
     *
     * Look for a regex in the email source and return it
     *
     * @param $regex string
     * @return string
     **/
    public function grabFromLastEmail($regex)
    {
        $matches = $this->grabMatchesFromLastEmail($regex);
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
    public function grabFromLastEmailFrom($address, $regex)
    {
        $matches = $this->grabMatchesFromLastEmailFrom($address, $regex);
        return $matches[0];
    }


    /**
     * Grab Matches From Last Email
     *
     * Look for a regex in the email source and return it's matches
     *
     * @param $regex string
     * @return array
     **/
    public function grabMatchesFromLastEmail($regex)
    {
        $email = $this->getLastMessage();
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
    public function grabMatchesFromLastEmailFrom($address, $regex)
    {
        $email = $this->getLastMessageFrom($address);
        $matches = $this->grabMatchesFromEmail($email, $regex);
        return $matches;
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
        preg_match($regex, $this->getEmailContent($email), $matches);
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
    protected function getLastMessageFrom($address)
    {
        $messages = $this->getEmails(array(
            'maxResults' => 1,
            'q' => "from:{$address}",
        ));
        if (empty($messages)) {
            $this->fail("No messages sent to {$address}");
        }

        /** @var /Google_Service_Gmail_Message $last */
        $last = array_shift($messages);

        return $this->getEmailById($last->id);
    }

    /**
     * Last Message
     *
     * Get the most recent email
     *
     * @return \Google_Service_Gmail_Message
     **/
    protected function getLastMessage() {
        $messages = $this->getEmails(array(
            'maxResults' => 1,
        ));

        if (empty($messages)) {
            $this->fail("No messages received");
        }

        /** @var /Google_Service_Gmail_Message $last */
        $last = array_shift($messages);

        return $this->getEmailById($last->id);
    }

    /**
     * Messages
     *
     * Get an array of all the message objects
     *
     * @param $params array
     * @return array
     **/
    protected function getEmails($params = array()) {
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
    protected function getEmailById($id) {
        return $this->service->users_messages->get('me', $id, array('format' => 'full'));
    }

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
            if ($emailPart->mimeType != "text/{$type}") continue;
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