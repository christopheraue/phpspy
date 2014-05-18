<?php

class VIP
{
    private $_secret;
    private $_source;

    public function learnSecret($secret, $source = null)
    {
        $this->_secret = $secret;
        $this->_source = $source;
    }
}