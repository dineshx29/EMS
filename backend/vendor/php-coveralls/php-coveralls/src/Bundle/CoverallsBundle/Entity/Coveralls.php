<?php

namespace PhpCoveralls\Bundle\CoverallsBundle\Entity;

/**
 * Data for Coveralls API.
 *
 * @author Kitamura Satoshi <with.no.parachute@gmail.com>
 */
abstract class Coveralls implements ArrayConvertable
{
    /**
     * String expression (convert to json).
     *
     * @return string
     */
    public function __toString()
    {
        $result = json_encode($this->toArray());

        if (JSON_ERROR_NONE !== json_last_error()) {
            $this->onJsonError();
        }

        return (string) $result;
    }

    protected function onJsonError()
    {
        throw new \UnexpectedValueException(\sprintf(
            'Can not encode to JSON, error: "%s". If you have non-UTF8 chars, consider migration to UTF8.',
            json_last_error_msg()
        ));
    }
}
