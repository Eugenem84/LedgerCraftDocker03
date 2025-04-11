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

    public function getTopProfitClients(Request $request, $specializationId)
    {
        return response()->json($this->statisticRepository->getTopProfitClients($specializationId));
    }

    public function getIncomeByYear(Request $request, $specializationId)
    {
        $data = $this->statisticRepository->getIncomeByYear($specializationId);
        return response()->json($data);
    }

    public function getIncomesByPeriod(Request $request, $specializationId)
    {
        $period = $request->get('period') ?? $request->query('period');// Получаем выбранный период из запроса, по умолчанию месяц

        if ($period === 'day') {
            $data = $this->statisticRepository->getIncomeByDay($specializationId);
            return response()->json($data);
        }

        if ($period === 'week') {
            $data = $this->statisticRepository->getIncomeByWeek($specializationId);
            return response()->json($data);
        }

        if ($period === 'month') {
            $data = $this->statisticRepository->getIncomeByMonth($specializationId);
            return response()->json($data);
        }

        $data = $this->statisticRepository->getIncomeByTimePeriod($specializationId, $period);

        return response()->json($data);
    }

    public function getIncomesByDat(Request $request, $specializationId)
    {

    }

}
