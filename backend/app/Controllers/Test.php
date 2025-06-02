<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Test extends Controller
{
    public function index()
    {
        return $this->response->setJSON([
            'status' => true,
            'message' => 'EMS API is working!',
            'timestamp' => date('Y-m-d H:i:s'),
            'version' => '1.0.0'
        ]);
    }

    public function health()
    {
        return $this->response->setJSON([
            'status' => 'healthy',
            'database' => 'connected',
            'api' => 'working'
        ]);
    }
}
