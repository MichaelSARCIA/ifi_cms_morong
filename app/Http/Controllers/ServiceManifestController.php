<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ServiceManifestController extends Controller
{
    public function index()
    {
        return view('modules.service_manifest.index');
    }
}
