<?php

namespace Cleantalk\CF7_Integration;

use Exception;

class TransportException extends Exception
{
    /**
     * @param string $url
     * @return self
     */
    public static function fromUrlHostError($url_host)
    {
        return new self("Couldn't resolve host name for \"$url_host\".\nCheck your network connectivity.");
    }
}
