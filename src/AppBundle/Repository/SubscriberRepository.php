<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Subscriber;

class SubscriberRepository
{
    protected $path;
    protected $data = [];

    public function __construct($path)
    {
        $this->loadData($path);
    }

    public function sort($field, $direction)
    {
        uasort($this->data, function ($a, $b) use ($field, $direction) {
            $a = $a->toArray();
            $b = $b->toArray();
            if ($a[$field] == $b[$field]) {
                return 0;
            }
            if ($direction == 'desc') {
                return $a[$field] > $b[$field] ? -1 : 1;
            } else {
                return $a[$field] < $b[$field] ? -1 : 1;
            }
        });

        return $this;
    }


    public function findAll()
    {
        return $this->data;
    }

    public function find($id)
    {
        return isset($this->data[$id]) ? $this->data[$id] : false;
    }

    public function add(Subscriber $subscriber)
    {
        $subscriber->setId(md5(microtime(true)));
        $this->data[$subscriber->getId()] = $subscriber;
    }

    public function update($id, Subscriber $subscriber)
    {
        if ($this->find($id)) {
            $this->data[$id] = $subscriber;
            return true;
        } else {
            return false;
        }
    }

    public function delete($id)
    {
        unset($this->data[$id]);
    }

    public function save()
    {
        $data = [];
        array_walk($this->data, function ($item) use (&$data) {
            $data[] = $item->toArray();
        });
        file_put_contents($this->path, json_encode($data));
    }

    protected function loadData($path)
    {
        $this->path = $path;
        $file = file_get_contents($this->path);
        $jsonData = json_decode($file, true);

        if ($jsonData) {
            array_walk($jsonData, function ($row) {
                $entity = new Subscriber();
                $entity->setId($row['id']);
                $entity->setName($row['name']);
                $entity->setEmail($row['email']);
                $entity->setCategories($row['categories']);
                $entity->setCreatedAt($row['created_at']);
                ;
                $this->data[$entity->getId()] = $entity;
            });
        }
    }
}
