<?php
namespace Codeception\Util;

interface MailInterface {

    /**
     * See In Last Email
     *
     * Look for a string in the most recent email
     *
     * @param $expected string
     * @return void
     **/
    public function seeInLastEmail($expected);

    /**
     * Don't See In Last Email
     *
     * Look for the absence of a string in the most recent email
     *
     * @param $unexpected string
     * @return void
     **/
    public function dontSeeInLastEmail($unexpected);

    /**
     * See In Last Email From
     *
     * Look for a string in the most recent email sent to $address
     *
     * @param $address string
     * @param $expected string
     * @return void
     **/
    public function seeInLastEmailFrom($address, $expected);

    /**
     * Don't See In Last Email From
     *
     * Look for the absence of a string in the most recent email sent to $address
     *
     * @param $address string
     * @param $unexpected string
     * @return void
     **/
    public function dontSeeInLastEmailFrom($address, $unexpected);

    /**
     * See In Last Email subject
     *
     * Look for a string in the most recent email subject
     *
     * @param $expected string
     * @return void
     **/
    public function seeInLastEmailSubject($expected);

    /**
     * Don't See In Last Email subject
     *
     * Look for the absence of a string in the most recent email subject
     *
     * @param $expected string
     * @return void
     **/
    public function dontSeeInLastEmailSubject($expected);

    /**
     * See In Last Email Subject From
     *
     * Look for a string in the most recent email subject sent to $address
     *
     * @param $address string
     * @param $expected string
     * @return void
     **/
    public function seeInLastEmailSubjectFrom($address, $expected);

    /**
     * Don't See In Last Email Subject From
     *
     * Look for the absence of a string in the most recent email subject sent to $address
     *
     * @param $address string
     * @param $unexpected string
     * @return void
     **/
    public function dontSeeInLastEmailSubjectFrom($address, $unexpected);

    /**
     * Grab From Last Email
     *
     * Look for a regex in the email source and return it
     *
     * @param $regex string
     * @return string
     **/
    public function grabFromLastEmail($regex);

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
    public function grabFromLastEmailFrom($address, $regex);

    /**
     * Grab Matches From Last Email
     *
     * Look for a regex in the email source and return it's matches
     *
     * @param $regex string
     * @return array
     **/
    public function grabMatchesFromLastEmail($regex);

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
    public function grabMatchesFromLastEmailFrom($address, $regex);
}