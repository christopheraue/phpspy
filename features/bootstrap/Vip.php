<?php

class VIP
{
    private $_secret;
    private $_source;

    /**
     * Bury a secret deep in the private chest
     *
     * @param string      $secret Secret to store
     * @param string|null $source Who told the secret
     *
     * @return void
     */
    public function learnSecret($secret, $source = null)
    {
        $this->_secret = $secret;
        $this->_source = $source;
    }

    /**
     * Tell a secret you already know
     *
     * @param string $secret The secret to tell
     *
     * @return string
     */
    public function tellSecret($secret)
    {
        return $secret;
    }
}