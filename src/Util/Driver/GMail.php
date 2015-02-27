<?php
namespace Codeception\Util\Driver;

use Codeception\Lib\Wait;

class GMail {

    /**
     * Constructor for current class
     *
     * @param $clientId
     * @param $clientSecret
     * @param $refreshToken
     * @return GMail
     */
    public static function createByParams($clientId, $clientSecret, $refreshToken) {

        $client = new \Google_Client();
        $client->setClientId($clientId);
        $client->setClientSecret($clientSecret);
        $client->addScope(\Google_Service_Gmail::MAIL_GOOGLE_COM);
        $client->refreshToken($refreshToken);

        return self::createByClient($client);
    }

    /**
     * Constructor for current class
     *
     * @param \Google_Client $client
     * @return GMail
     */
    public static function createByClient(\Google_Client $client) {
        $service = new \Google_Service_Gmail($client);

        return self::createByService($service);
    }

    /**
     * Constructor for current class
     *
     * @param \Google_Service_Gmail $service
     * @return GMail
     */
    public static function createByService(\Google_Service_Gmail $service) {
        return new GMail($service);
    }

    /** @var \Google_Service_Gmail */
    public $service;

    protected $baseFilter = '';

    public function __construct(\Google_Service_Gmail $service) {
        $this->service = $service;
    }

    /**
     * @return \Google_Client
     */
    public function getClient() {
        return $this->service->getClient();
    }

    /**
     * @return \Google_Service_Gmail
     */
    public function getService() {
        return $this->service;
    }

    public function getBaseFilter() {
        return $this->baseFilter;
    }

    public function setBaseFilter($baseFilter) {
        $this->baseFilter = $baseFilter;
    }

    /**
     * @return void
     */
    public function refreshToken() {
        if($this->service->getClient()->isAccessTokenExpired()) {
            $this->service->getClient()->refreshToken($this->service->getClient()->getRefreshToken());
        }
    }

    /**
     * List of Emails
     *
     * Get an array of all the emails objects
     *
     * @param $filters array
     * @param $limit int
     * @return array
     **/
    public function getEmails($filters, $limit = 100) {
        $params = array(
            'maxResults' => $limit,
            'q' => $this->parseFilters($filters)
        );
        /** @var /Google_Service_Gmail_ListMessagesResponse $list */
        $list = $this->service->users_messages->listUsersMessages('me', $params);
        return $list->getMessages();
    }

    public function getEmailCount($filters) {
        $params = array(
            'maxResults' => 1,
            'q' => $this->parseFilters($filters)
        );

        /** @var /Google_Service_Gmail_ListMessagesResponse $list */
        $list = $this->service->users_messages->listUsersMessages('me', $params);
        return $list->getResultSizeEstimate();
    }

    /**
     * @param $filters
     * @return \Google_Service_Gmail_Message|null
     */
    public function getLastEmail($filters) {
        $emails = $this->getEmails($filters, 1);

        if (empty($emails)) {
            return null;
        }

        /** @var /Google_Service_Gmail_Message $last */
        $last = array_shift($emails);

        return $this->getEmailById($last->id);
    }

    /**
     * Email from ID
     *
     * Given a GMail id, returns the email's object
     *
     * @param $id string
     * @return \Google_Service_Gmail_Message
     **/
    public function getEmailById($id) {
        return $this->service->users_messages->get('me', $id, array('format' => 'full'));
    }

    /**
     * Email Body
     *
     * @param $email /Google_Service_Gmail_Message
     * @param $type string 'html' | 'plain'
     * @return string
     */
    public function getEmailContent($email, $type = 'html') {
        if(!in_array($type, array('html', 'plain'))) {
            $type = 'html';
        }

        foreach($email->getPayload()->getParts() as $emailPart) {
            if ($emailPart->mimeType != "text/{$type}") continue;
            return $this->base64url_decode($emailPart['body']['data']);
        }
        return '';
    }

    /**
     * Email Header
     *
     * Return Email header if exist (subject | from | to ...)
     *
     * @param $email /Google_Service_Gmail_Message
     * @param $headerName string
     * @return string
     */
    public function getEmailHeader($email, $headerName) {
        return $this->getHeaderValue($email->getPayload()->getHeaders(), $headerName);
    }

    /**
     * Construct a new Wait by the current \Codeception\Util\Driver\GMail instance.
     * Sample usage:
     *
     *   $driver->wait(20, 1000)->until(
     *     GMailExpectedCondition::emailFrom('test@gmail.com')
     *   );
     *
     * @param $timeout_in_second int
     * @param $interval_in_millisecond int
     * @return Wait
     */
    public function wait($timeout_in_second = 30, $interval_in_millisecond = 1000) {
        return new Wait(
            $this, $timeout_in_second, $interval_in_millisecond
        );
    }


    protected function parseFilters($filters) {
        if(is_array($filters)) {
            $query = array();
            foreach($filters as $filter => $value) {
                $query []= "{$filter}:{$value}";
            }

            $filters = implode(' ', $query);
        }

        $baseFilter = $this->baseFilter;
        return "({$filters}) $baseFilter";

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
}