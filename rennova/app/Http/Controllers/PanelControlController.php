<?php

namespace App\Http\Controllers;

use App\Services\PanelControlService;

class PanelControlController extends Controller
{
    public function index(PanelControlService $panelControl)
    {
        return view('dashboard', $panelControl->obtenerMetricas());
    }
}
