<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

class CsrfController extends Controller
{
    public function csrf_token() {
    	return csrf_token();
    }
}
