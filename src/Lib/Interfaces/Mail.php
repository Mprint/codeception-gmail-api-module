<?php
namespace Codeception\Lib\Interfaces;

interface Mail
{

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
    public function seeInLastEmail($expected, $filters = array(), $message = '');

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
    public function dontSeeInLastEmail($unexpected, $filters = array(), $message = '');


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
    public function seeInLastEmailSubject($expected, $filters = array(), $message = '');

    /**
     * Don't See In Last Email subject
     *
     * Look for the absence of a string in the most recent email subject
     *
     * @param string        $expected
     * @param array|string  $filters
     * @param string        $message
     * @return void
     **/
    public function dontSeeInLastEmailSubject($expected, $filters = array(), $message = '');

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
    public function grabFromLastEmail($regex, $filters = array(), $message = '');

    /**
     * Grab Matches From Last Email
     *
     * Look for a regex in the email source and return it's matches
     *
     * @param string        $regex
     * @param array|string  $filters
     * @param string        $message
     * @return array
     **/
    public function grabMatchesFromLastEmail($regex, $filters = array(), $message = '');

    /**
     * Test email count equals expected value
     *
     * @param int           $expected
     * @param array|string  $filters
     * @param string        $message
     */
    public function seeEmailCountEquals($expected, $filters = array(), $message = '');

    /**
     * Test email count greater then expected value
     *
     * @param int           $expected
     * @param array|string  $filters
     * @param string        $message
     */
    public function seeEmailCountGreaterThan($expected, $filters = array(), $message = '');

    /**
     * Test email count greater then or equals expected value
     *
     * @param int           $expected
     * @param array|string  $filters
     * @param string        $message
     */
    public function seeEmailCountGreaterThanOrEqual($expected, $filters = array(), $message = '');

    /**
     * Test email count less then expected value
     *
     * @param int           $expected
     * @param array|string  $filters
     * @param string        $message
     */
    public function seeEmailCountLessThan($expected, $filters = array(), $message = '');

    /**
     * Test email count less then or equals expected value
     *
     * @param int           $expected
     * @param array|string  $filters
     * @param string        $message
     */
    public function seeEmailCountLessThanOrEqual($expected, $filters = array(), $message = '');
}