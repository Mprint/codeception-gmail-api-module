<?php
namespace Codeception\Util;

class GMailExpectedCondition implements ExpectedConditionInterface{

    /**
     * A closure function to be executed by RemoteWait. It should return
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
     * @return GMailExpectedCondition
     */
    public static function emailFrom($address) {
        return self::emails(array('from' => $address), 1);
    }

    /**
     * An expectation for an email where the subject contains $subject
     *
     * @param $subject string
     * @return GMailExpectedCondition
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
     * @return GMailExpectedCondition
     */
    public static function emails($filters, $limit = 100) {
        return new GMailExpectedCondition(
            function (GMailRemote $remote) use ($filters, $limit) {
                return $remote->getEmails($filters, $limit);
            }
        );
    }


}