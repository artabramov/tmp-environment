<?php

namespace artabramov\Echidna\Echidna;

/**
 * All posts should be inside one of the hubs. 
 * The Hub class is responsible for working with hubs.
 */
class Hub extends \artabramov\Echidna\Echidna
{
    /**
     * Insert a new hub.
     * @param int $user_id
     * @param string $hub status
     * @param string $hub_name
     * @return bool
     */
    public function set( int $user_id, string $hub_status, string $hub_name ) : bool {}

    public function put() : bool {}

    public function unset() : bool {}

    public function get() : bool {}

    public function some() : bool {}

}
