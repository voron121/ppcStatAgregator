<?php


namespace Models;

use PPCSoft\Registry;

class BaseModel
{
    protected $db = null;
    protected $filter = null;

    public function __construct()
    {
        $this->db = Registry::get("db");
    }
}