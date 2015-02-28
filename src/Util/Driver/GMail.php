<?php
namespace Codeception\Util\Driver;

use Codeception\Lib\Wait;

class GMail {

    /** @var \Google_Service_Gmail */
    public $service;

    protected $baseFilter = '';

    /**
     * Constructor for current class
     *
     * @param string $clientId
     * @param string $clientSecret
     * @param string $refreshToken
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

    /**
     * @return string
     */
    public function getBaseFilter() {
        return $this->baseFilter;
    }

    /**
     * @param string $baseFilter
     */
    public function setBaseFilter($baseFilter) {
        $this->baseFilter = $baseFilter;
    }

    /**
     * Ensure that the current token has not expired
     *
     * @return void
     */
    public function ensureActiveToken() {
        if($this->service->getClient()->isAccessTokenExpired()) {
            $this->service->getClient()->refreshToken($this->service->getClient()->getRefreshToken());
        }
    }

    /**
     * List of Emails
     *
     * Get an array of all the emails objects
     *
     * @param array $filters
     * @param int   $limit
     * @return array
     **/
    public function getEmails($filters, $limit = 100) {
        $params = array(
            'maxResults' => $limit,
            'q' => $this->parseFilters($filters)
        );

        $this->ensureActiveToken();

        /** @var /Google_Service_Gmail_ListMessagesResponse $list */
        $list = $this->service->users_messages->listUsersMessages('me', $params);
        return $list->getMessages();
    }

    /**
     * @param array|string $filters
     * @return int
     */
    public function getEmailCount($filters) {
        $params = array(
            'maxResults' => 1,
            'q' => $this->parseFilters($filters)
        );

        $this->ensureActiveToken();

        /** @var /Google_Service_Gmail_ListMessagesResponse $list */
        $list = $this->service->users_messages->listUsersMessages('me', $params);
        return (int) $list->getResultSizeEstimate();
    }

    /**
     * @param array|string $filters
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
     * @param string $id
     * @return \Google_Service_Gmail_Message
     **/
    public function getEmailById($id) {
        $this->ensureActiveToken();
        return $this->service->users_messages->get('me', $id, array('format' => 'full'));
    }

    /**
     * Email Body
     *
     * @param /Google_Service_Gmail_Message $email
     * @param string {'html'|'plain'}       $type
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
     * @param /Google_Service_Gmail_Message $email
     * @param string                        $headerName
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
     * @param int   $timeout_in_second
     * @param int   $interval_in_millisecond
     * @return Wait
     */
    public function wait($timeout_in_second = 30, $interval_in_millisecond = 1000) {
        return new Wait(
            $this, $timeout_in_second, $interval_in_millisecond
        );
    }



    /**
     * @param array|string $filters
     * @return string
     */
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
     * @param array  $headers
     * @param string $headerName
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
     * @param string    $data
     * @return string
     */
    protected function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Custom base64 decode function required by GMail API
     *
     * @param string    $data
     * @return string
     */
    protected function base64url_decode($data) {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }
}