<?php

namespace App\Http\Controllers;

abstract class Controller
{
    public function __construct()
    {
        // Removed third-party license coupling
    }

    public static function middleware()
    {
        return [];
    }

}
