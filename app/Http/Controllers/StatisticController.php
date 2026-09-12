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

    /**
     * Топ товаров со склада в заказах (задача 9.1) — зеркало клиентского блока
     * «топ товаров» на странице аналитики, чтобы цифры сходились.
     */
    public function getTopProductsBySpecialization(Request $request, $specializationId)
    {
        return response()->json($this->statisticRepository->getTopProductsBySpecialization($specializationId));
    }

    /** Топ ручных позиций заказа (задача 9.1). */
    public function getTopMaterialsBySpecialization(Request $request, $specializationId)
    {
        return response()->json($this->statisticRepository->getTopMaterialsBySpecialization($specializationId));
    }

    /** Распределение заказов по текущему статусу (задача 9.1). */
    public function getStatusDistribution(Request $request, $specializationId)
    {
        return response()->json($this->statisticRepository->getStatusDistribution($specializationId));
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

    public function getStatsByPeriod(Request $r, $id)
    {
        $period = $r->get('period', 'month');
        $data   = $this->statisticRepository->getStatsByPeriod($id, $period);
        return response()->json($data);
    }

}
