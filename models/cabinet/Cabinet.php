<?php


namespace Models\cabinet;

use PPCSoft\Registry;
use PPCSoft\Logger\Log;
use Exception;

class Cabinet
{
    protected $db = null;

    public function __construct()
    {
        $this->db = Registry::get("db");
    }
}