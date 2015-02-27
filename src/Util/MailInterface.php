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
     * Grab From Last Email
     *
     * Look for a regex in the email source and return it
     *
     * @param $regex string
     * @return string
     **/
    public function grabFromLastEmail($regex);

    /**
     * Grab Matches From Last Email
     *
     * Look for a regex in the email source and return it's matches
     *
     * @param $regex string
     * @return array
     **/
    public function grabMatchesFromLastEmail($regex);

}