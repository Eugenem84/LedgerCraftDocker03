<?php

namespace App\Repositories;

use App\Http\Controllers\Controller;
use App\Models\Client;

class ClientRepository extends Controller
{
    public function getAll()
    {
        return Client::all();
    }

    public function getBySpecialization($specializationId)
    {
        return Client::where('specialization_id', $specializationId)->get();
    }

    public function getName($id)
    {
        $client = Client::withTrashed()->find($id);
        if ($client){
            $clientName = $client->name;
            if ($clientName){
                return $clientName;
            }
        }
        return 'клиент неизвестен';
    }

    public function getPhone($id)
    {
        $client = Client::withTrashed()->find($id);
        if ($client){
            $clintPhone = $client->phone;
            if ($clintPhone){
                return $clintPhone;
            }
        }
        return 'нет телефона';
    }

    public function addNew($name, $phone, $specializationId )
    {
        $client = new Client();
        $client->name = $name;
        $client->phone = $phone;
        $client->specialization_id = $specializationId;
        $client->save();
        return $client->fresh();
    }

    public function delete($id)
    {
        $client = Client::find($id);
        if ($client){
            $client->delete();
            return true;
        } else {
            return false;
        }
    }

    public function edit($id, $newName, $newPhone)
    {
        $client = Client::find($id);
        if ($client){
            $client->name = $newName;
            $client->phone = $newPhone;
            $client->save();
            return true;
        } else {
            return false;
        }
    }
}
