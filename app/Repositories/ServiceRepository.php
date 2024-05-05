<?php

namespace App\Repositories;

use App\Http\Controllers\Controller;
use App\Models\Service;
use function Symfony\Component\String\s;

class ServiceRepository extends Controller
{
    public function getByCategory($categoryId)
    {
        return Service::where('category_id', $categoryId)->get();
    }

    public function getService($id)
    {
        return Service::find($id);
    }

    public function addNew($name, $price, $categoryId)
    {

        $service = new Service;
        $service->service = $name;
        $service->price = $price;
        $service->category_id = $categoryId;
        $service->save();
    }

    public function delete($id)
    {
        $service = Service::find($id);
        if ($service){
            $service->delete();
            return true;
        }
        return false;
    }

    public function edit($id, $newName, $newPrice)
    {
        $service = Service::find($id);
        if ($service){
            $service->service = $newName;
            $service->price = $newPrice;
            $service->save();
            return true;
        } else {
            return false;
        }
    }
}
