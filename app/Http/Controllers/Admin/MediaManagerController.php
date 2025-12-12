<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MediaManagerController extends Controller
{
    /**
     * Display the media manager page.
     */
    public function index(): View
    {
        return view('admin.media.manager');
    }
}
