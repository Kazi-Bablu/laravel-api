<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CustomerService;
use Illuminate\Http\Request;

class CustomerController extends Controller
{

    private $service;

    public function __construct(CustomerService $service)
    {
        $this->middleware('auth:api');

        $this->service = $service;
    }

    public function index()
    {
        return $this->service->list();
    }

    public function store(Request $request)
    {

        $data = $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'phone' => 'required'
        ]);

        return $this->service->store($data);

    }

    public function show($id)
    {
        return $this->service->show($id);
    }

    public function update(Request $request, $id)
    {

        return $this->service->update($id, $request->all());

    }

    public function destroy($id)
    {
        return $this->service->delete($id);
    }

}
