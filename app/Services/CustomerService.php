<?php

namespace App\Services;

use App\Repositories\CustomerRepositoryInterface;

class CustomerService
{

    private $repository;

    public function __construct(CustomerRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function list()
    {
        return $this->repository->all();
    }

    public function store($data)
    {
        return $this->repository->create($data);
    }

    public function show($id)
    {
        return $this->repository->find($id);
    }

    public function update($id, $data)
    {
        return $this->repository->update($id, $data);
    }

    public function delete($id)
    {
        return $this->repository->delete($id);
    }

}
