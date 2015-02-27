<?php
namespace Codeception\Util;

class GMail implements ExpectedCondition{

    /**
     * A closure function to be executed by \Codeception\Util\Driver\GMail. It should return
     * a truthy value, mostly boolean, array or a /Google_Service_Gmail_Message, on success.
     */
    private $apply;

    /**
     * @return (function():T) a closure function to be executed
     */
    public function getApply() {
        return $this->apply;
    }

    protected function __construct($apply) {
        $this->apply = $apply;
    }

    /**
     * An expectation for checking that an email from a email address is present in inbox
     *
     * @param $address string
     * @return GMail
     */
    public static function emailFrom($address) {
        return self::emails(array('from' => $address), 1);
    }

    /**
     * An expectation for an email where the subject contains $subject
     *
     * @param $subject string
     * @return GMail
     */
    public static function emailWithSubject($subject) {
        return self::emails(array('subject' => $subject), 1);
    }

    /**
     * An expectation for checking that an email from a email address is present in inbox
     *
     * For a complete list of filters supported by google @see https://support.google.com/mail/answer/7190?hl=en
     *
     * @param $filters array
     * @param $limit int
     * @return GMail
     */
    public static function emails($filters, $limit = 100) {
        return new GMail(
            function (\Codeception\Util\Driver\GMail $remote) use ($filters, $limit) {
                return $remote->getEmails($filters, $limit);
            }
        );
    }


}