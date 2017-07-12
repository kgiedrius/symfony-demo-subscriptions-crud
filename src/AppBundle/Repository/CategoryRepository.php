<?php

namespace AppBundle\Repository;

class CategoryRepository
{
    protected $data;

    public function __construct($path)
    {
        $file = file_get_contents($path);
        $jsonData = json_decode($file, true);
        $this->data = !$jsonData ? [] : $jsonData;
    }

    public function findAll()
    {
        return $this->data;
    }
}
