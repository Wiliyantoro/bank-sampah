<?php
namespace Modules\BankSampah\Http\Controllers\BackEnd;

use Illuminate\Support\Facades\Response;

class TestController
{
    public function index()
    {
        return Response::json([
            'module' => 'BankSampah',
            'status' => 'loaded',
            'version' => '2608.0.0'
        ]);
    }
}
