<?php

namespace Tests\Feature;

use App\Repositories\StatisticRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Единая методика «выручки» (задача 9.1).
 *
 * До правки `StatisticRepository` считал выручку тремя разными формулами
 * (`SUM(services.price)`, `SUM(quantity * sale_price)`, `SUM(orders.total_amount)`,
 * причём последняя — без фильтров `paid`/`status`), поэтому цифры на одном экране
 * не сходились. Здесь проверяется, что теперь все отчёты используют одну методику:
 *
 *   • учтённый заказ — `status = 'done'`, `paid = true`, `deleted_at IS NULL`;
 *   • выручка = позиции: работы + товары + ручные материалы;
 *   • цена позиции (`sale_price`) важнее каталожной `services.price`;
 *   • число заказов считается по заказам, а значит заказ без позиций входит
 *     в средний чек как нулевой;
 *   • период — по `orders.updated_at`.
 *
 * Контрольные цифры (набор данных из `setUp`) совпадают с клиентским тестом
 * `test/analytics-repo.test.js` — это и есть критерий «цифры на странице
 * аналитики сходятся с серверными отчётами».
 *
 * Тест идёт на **отдельной** тестовой БД (см. `phpunit.xml`): `RefreshDatabase`
 * сносит таблицы, поэтому на «не тестовой» БД тест пропускается.
 */
class StatisticRepositoryTest extends TestCase
{
    use RefreshDatabase;

    /** Выручка основного набора: 2×300 (работа) + 1×1000 (товар) + 2×50 (материал). */
    private const EXPECTED_REVENUE = 1700;

    /** Себестоимость основного набора: 1×700 (товар) + 2×20 (материал) — задачи 9.5/9.6. */
    private const EXPECTED_COST = 740;

    /** Маржа основного набора: 1700 − 740; у работ себестоимости нет. */
    private const EXPECTED_MARGIN = 960;

    private StatisticRepository $statistics;

    private int $specializationId;
    private int $clientId;
    private int $serviceId;
    private int $productId;

    protected function setUp(): void
    {
        $database = $_ENV['DB_DATABASE'] ?? $_SERVER['DB_DATABASE'] ?? getenv('DB_DATABASE');
        $database = $database === false ? '' : (string) $database;

        if ($database === '' || ! str_contains($database, 'test')) {
            $this->markTestSkipped(
                "Тесты статистики запускаются только на отдельной тестовой БД ".
                "(в имени должно быть 'test'); текущая БД: '{$database}'. См. phpunit.xml."
            );
        }

        parent::setUp();

        $this->statistics = new StatisticRepository();
        $this->seedWorkshop();
    }

    /**
     * Мастерская: специализация, клиент, работа, товар + набор заказов
     * (учтённые, неоплаченные, незавершённые, удалённый).
     */
    private function seedWorkshop(): void
    {
        $now = Carbon::now();

        $this->specializationId = DB::table('specializations')->insertGetId([
            'specializationName' => 'Ремонт',
            'popularCounter'     => 0,
            'created_at'         => $now,
            'updated_at'         => $now,
        ]);

        $this->clientId = DB::table('clients')->insertGetId([
            'name'       => 'Иван',
            'phone'      => '123',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $categoryId = DB::table('categories')->insertGetId([
            'category_name'     => 'Двигатель',
            'specialization_id' => $this->specializationId,
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);

        // Каталожная цена работы (500) намеренно отличается от цены в позиции (300):
        // методика обязана считать по `order_service.sale_price` (в заказе была скидка).
        $this->serviceId = DB::table('services')->insertGetId([
            'service'     => 'Замена масла',
            'price'       => 500,
            'category_id' => $categoryId,
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);

        $productCategoryId = DB::table('product_categories')->insertGetId([
            'name'              => 'Фильтры',
            'specialization_id' => $this->specializationId,
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);

        $this->productId = DB::table('products')->insertGetId([
            'name'                => 'Фильтр',
            'product_category_id' => $productCategoryId,
            'base_sale_price'     => 1200,
            'created_at'          => $now,
            'updated_at'          => $now,
        ]);

        // Учтённый заказ: работы + товар + ручной материал.
        // Закупка (себестоимость) — задачи 9.5/9.6: 1×700 (товар) + 2×20 (материал) = 740.
        $this->createOrder('done', true, [
            ['service', 2, 300],
            ['product', 1, 1000, 700],
            ['material', 2, 50, 20],
        ]);

        // Учтённый заказ без позиций: нужен, чтобы проверить средний чек.
        $this->createOrder('done', true, []);

        // Не завершён и не оплачен — в выручку не входит.
        $this->createOrder('waiting', false, [['service', 5, 1000]]);

        // Завершён, но не оплачен — тоже не выручка.
        $this->createOrder('done', false, [['service', 1, 777]]);

        // Оплаченный и завершённый, но удалённый (soft-delete) — не выручка.
        $this->createOrder('done', true, [['service', 1, 999]], deleted: true);
    }

    public function test_all_reports_use_the_same_revenue(): void
    {
        $dwmy = $this->statistics->getProfitDWMY($this->specializationId)[0];

        $this->assertSame(self::EXPECTED_REVENUE, (int) $dwmy->total_day);
        $this->assertSame(self::EXPECTED_REVENUE, (int) $dwmy->total_week);
        $this->assertSame(self::EXPECTED_REVENUE, (int) $dwmy->total_month);
        $this->assertSame(self::EXPECTED_REVENUE, (int) $dwmy->total_year);

        // Те же цифры у остальных отчётов — в этом и смысл «одной методики».
        $thisMonth = Carbon::now()->format('Y-m');

        $byPeriod = $this->statistics->getStatsByPeriod($this->specializationId, 'month');
        $this->assertSame(
            (float) self::EXPECTED_REVENUE,
            $byPeriod->firstWhere('period', $thisMonth)['total']
        );

        $byMonth = collect($this->statistics->getIncomeByMonth($this->specializationId))
            ->firstWhere('period', $thisMonth);
        $this->assertSame((float) self::EXPECTED_REVENUE, $byMonth['total']);

        $byYear = collect($this->statistics->getIncomeByYear($this->specializationId))
            ->firstWhere('month_number', Carbon::now()->month);
        $this->assertSame((float) self::EXPECTED_REVENUE, (float) $byYear->total);

        $byDay = collect($this->statistics->getIncomeByDay($this->specializationId))->last();
        $this->assertSame(Carbon::now()->format('Y-m-d'), $byDay['period']);
        $this->assertSame((float) self::EXPECTED_REVENUE, $byDay['total']);
    }

    public function test_unpaid_unfinished_and_deleted_orders_are_not_revenue(): void
    {
        $topServices = collect($this->statistics->getTopServicesBySpecialization($this->specializationId));

        // Кроме учтённой работы (2×300) в наборе есть 5×1000 и 1×777 по неоплаченным
        // заказам и 1×999 по удалённому — в топ они попадать не должны.
        $this->assertCount(1, $topServices);
        $this->assertSame('Замена масла', $topServices[0]->service);
        $this->assertSame(2, (int) $topServices[0]->service_count);
        $this->assertSame(600, (int) $topServices[0]->total);
        // Каталожная цена (500) в расчёте не участвует — только `sale_price` позиции.
        $this->assertSame(500, (int) $topServices[0]->price);
    }

    public function test_average_check_counts_orders_without_positions(): void
    {
        $today = $this->statistics
            ->getStatsByPeriod($this->specializationId, 'day')
            ->firstWhere('period', Carbon::now()->format('Y-m-d'));

        $this->assertSame(2, $today['count']);                        // два учтённых заказа
        $this->assertSame((float) self::EXPECTED_REVENUE, $today['total']);
        $this->assertSame(850, $today['avg']);                        // 1700 / 2, а не 1700 / 1
    }

    public function test_top_lists_use_order_prices_and_unified_filters(): void
    {
        $products = $this->statistics->getTopProductsBySpecialization($this->specializationId);
        $this->assertSame('Фильтр', $products[0]->name);
        $this->assertSame(1, (int) $products[0]->product_count);
        $this->assertSame(1000, (int) $products[0]->total);

        $materials = $this->statistics->getTopMaterialsBySpecialization($this->specializationId);
        $this->assertSame('Герметик', $materials[0]->name);
        $this->assertSame(2, (int) $materials[0]->material_count);
        $this->assertSame(100, (int) $materials[0]->total);

        $clients = $this->statistics->getTopProfitClients($this->specializationId);
        $this->assertSame('Иван', $clients[0]->name);
        $this->assertSame(2, (int) $clients[0]->num_ord);
        $this->assertSame(self::EXPECTED_REVENUE, (int) $clients[0]->total_amount);
    }

    /**
     * Задачи 9.5/9.6: маржа = выручка − себестоимость позиций.
     *
     * Себестоимость берётся из позиций заказа (`order_product.buy_price` / `materials.buy_price`),
     * у работ её нет — это труд мастера, поэтому их маржа равна выручке. Набор данных:
     * 1700 ₽ выручки и 740 ₽ закупки → маржа 960 ₽ (наценка 130 %).
     */
    public function test_margin_is_revenue_minus_purchase_cost(): void
    {
        $dwmy = $this->statistics->getProfitDWMY($this->specializationId)[0];

        $this->assertSame(self::EXPECTED_MARGIN, (int) $dwmy->margin_day);
        $this->assertSame(self::EXPECTED_MARGIN, (int) $dwmy->margin_week);
        $this->assertSame(self::EXPECTED_MARGIN, (int) $dwmy->margin_month);
        $this->assertSame(self::EXPECTED_MARGIN, (int) $dwmy->margin_year);

        $month = $this->statistics
            ->getStatsByPeriod($this->specializationId, 'month')
            ->firstWhere('period', Carbon::now()->format('Y-m'));

        $this->assertSame((float) self::EXPECTED_COST, $month['cost']);
        $this->assertSame((float) self::EXPECTED_MARGIN, $month['margin']);
        // 960 / 740 * 100 = 129.7… → 130 %
        $this->assertSame(130, $month['margin_percent']);

        // Топы считают маржу по строкам: товар 1×(1000−700), материал 2×(50−20),
        // работы — целиком маржа (600 ₽ = 2×300), у клиента — сумма по заказам.
        $products = $this->statistics->getTopProductsBySpecialization($this->specializationId);
        $this->assertSame(300, (int) $products[0]->margin);

        $materials = $this->statistics->getTopMaterialsBySpecialization($this->specializationId);
        $this->assertSame(60, (int) $materials[0]->margin);

        $services = $this->statistics->getTopServicesBySpecialization($this->specializationId);
        $this->assertSame(600, (int) $services[0]->total, 'у работы себестоимости нет');

        $clients = $this->statistics->getTopProfitClients($this->specializationId);
        $this->assertSame(self::EXPECTED_MARGIN, (int) $clients[0]->margin);
    }

    /**
     * Задачи 9.5/9.6: без закупки в позициях маржа равна выручке, а наценка —
     * `null` (делить не на что), а не 0 % — «прибыль» просто неизвестна.
     */
    public function test_period_without_purchase_costs_has_no_markup_percent(): void
    {
        $now = Carbon::now();

        $otherSpecializationId = DB::table('specializations')->insertGetId([
            'specializationName' => 'Без закупок',
            'popularCounter'     => 0,
            'created_at'         => $now,
            'updated_at'         => $now,
        ]);

        $clientId = DB::table('clients')->insertGetId([
            'name'       => 'Пётр',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $orderId = DB::table('orders')->insertGetId([
            'specialization_id' => $otherSpecializationId,
            'client_id'         => $clientId,
            'status'            => 'done',
            'paid'              => true,
            'total_amount'      => 0,
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);

        // Позиция без закупки (мастер не указал себестоимость).
        DB::table('materials')->insert([
            'order_id'   => $orderId,
            'name'       => 'Изолента',
            'price'      => 100,
            'amount'     => 1,
            'buy_price'  => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $period = $this->statistics
            ->getStatsByPeriod($otherSpecializationId, 'month')
            ->firstWhere('period', $now->format('Y-m'));

        $this->assertSame(100.0, $period['total']);
        $this->assertSame(0.0, $period['cost']);
        $this->assertSame(100.0, $period['margin'], 'без закупки маржа равна выручке');
        $this->assertNull($period['margin_percent'], 'наценка без себестоимости не определена');
    }

    public function test_status_distribution_counts_current_orders(): void
    {
        $statuses = collect($this->statistics->getStatusDistribution($this->specializationId))
            ->pluck('count', 'status')
            ->map(fn ($count) => (int) $count)
            ->all();

        // Удалённый заказ не считаем: остаются три завершённых и один в ожидании.
        ksort($statuses);
        $this->assertSame(['done' => 3, 'waiting' => 1], $statuses);
    }

    public function test_statistics_are_isolated_by_specialization(): void
    {
        $now = Carbon::now();

        $otherSpecializationId = DB::table('specializations')->insertGetId([
            'specializationName' => 'Аквариумы',
            'popularCounter'     => 0,
            'created_at'         => $now,
            'updated_at'         => $now,
        ]);

        $otherClientId = DB::table('clients')->insertGetId([
            'name'       => 'Пётр',
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('orders')->insert([
            'specialization_id' => $otherSpecializationId,
            'client_id'         => $otherClientId,
            'status'            => 'done',
            'paid'              => true,
            'total_amount'      => 0,
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);

        $other = $this->statistics->getProfitDWMY($otherSpecializationId)[0];
        $this->assertSame(0, (int) $other->total_month);

        // Свои цифры не поехали.
        $own = $this->statistics->getProfitDWMY($this->specializationId)[0];
        $this->assertSame(self::EXPECTED_REVENUE, (int) $own->total_month);
    }


    /**
     * Создаёт заказ со строками позиций.
     *
     * @param array<int, array{0: string, 1: int, 2: int, 3?: int|null}> $lines [вид, количество, цена, закупка]
     */
    private function createOrder(string $status, bool $paid, array $lines, bool $deleted = false): int
    {
        $now = Carbon::now();

        $orderId = DB::table('orders')->insertGetId([
            'specialization_id' => $this->specializationId,
            'client_id'         => $this->clientId,
            'status'            => $status,
            'paid'              => $paid,
            'total_amount'      => 0,
            'created_at'        => $now,
            'updated_at'        => $now,
            'deleted_at'        => $deleted ? $now : null,
        ]);

        foreach ($lines as $line) {
            [$kind, $quantity, $price] = $line;
            // Закупка (себестоимость) — задачи 9.5/9.6; без неё маржа по строке не считается.
            $buyPrice = $line[3] ?? null;

            if ($kind === 'service') {
                DB::table('order_service')->insert([
                    'order_id'   => $orderId,
                    'service_id' => $this->serviceId,
                    'sale_price' => $price,
                    'quantity'   => $quantity,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            if ($kind === 'product') {
                DB::table('order_product')->insert([
                    'order_id'   => $orderId,
                    'product_id' => $this->productId,
                    'sale_price' => $price,
                    'quantity'   => $quantity,
                    'buy_price'  => $buyPrice,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            if ($kind === 'material') {
                DB::table('materials')->insert([
                    'order_id'   => $orderId,
                    'name'       => 'Герметик',
                    'price'      => $price,
                    'amount'     => $quantity,
                    'buy_price'  => $buyPrice,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        return $orderId;
    }
}
