<?php

namespace App\Http\Controllers;

use App\Repositories\StatisticRepository;
use Illuminate\Http\Request;

class StatisticController extends Controller
{
    protected $statisticRepository;

    public function __construct(StatisticRepository $statisticRepository)
    {
        $this->statisticRepository = $statisticRepository;
    }

    public function getTotalDWMY(Request $request, $specializationId)
    {
        return response()->json($this->statisticRepository->getProfitDWMY($specializationId));
    }

    public function getTopServicesBySpecialization(Request $request, $specializationId)
    {
        return response()->json($this->statisticRepository->getTopServicesBySpecialization($specializationId));
    }
}
