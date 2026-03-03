<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Cook\CookRequest;
use App\Models\Order;
use App\Models\OrderMenu;
use App\Models\ShiftWorker;
use App\Models\StatusOrder;
use App\Models\WorkShift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CookController extends Controller
{
    //
    public function changeStatus(CookRequest $request ,$id)
    {
        $user = Auth::user();
        $order = Order::findOrFail($id);

        // 2. Проверка: работает ли повар в этой смене?
        $isAssigned = $order->shiftWorker->workShift->shiftWorkers
            ->contains('user_id', $user->id);

        if (!$isAssigned) {
            return response()->json([
                'error' => [
                    'code' => 403,
                    'message' => 'Forbidden! You are not assigned to this shift!'
                ]
            ], 403);
        }

        // 3. Проверка: смена активна?
        if (!$order->shiftWorker->workShift->active) {
            return response()->json([
                'error' => [
                    'code' => 403,
                    'message' => 'You cannot change the order status of a closed shift!'
                ]
            ], 403);
        }

        // 4. Разрешённые переходы для повара
        $allowed = [
            1 => [2],  // Принят → Готовится
            2 => [3],  // Готовится → Готов
        ];

        $currentStatus = $order->status_order_id;
        $newStatus = StatusOrder::where('code', $request->status)->first()?->id;

        if (!$newStatus) {
            return response()->json([
                'error' => [
                    'code' => 403,
                    'message' => 'Forbidden! Can\'t change existing order status554544544'
                ]
            ], 403);
        }

        // 6. Обновляем статус
        $order->status_order_id = $newStatus;
        $order->save();

        return response()->json([
            'data' => [
                'id' => $order->id,
                'status' => $request->status
            ]
        ], 200);

    }

    public function showOrder()
    {
        $user = auth()->user();

        // Активная смена
        $shift = DB::table('work_shifts')->where('active', 1)->first();
        if (!$shift) return response()->json(['error' => ['code' => 404, 'message' => 'No active shift']], 404);

        // Работает ли пользователь в этой смене
        $check = DB::table('shift_workers')->where('work_shift_id', $shift->id)->where('user_id', $user->id)->first();
        if (!$check) return response()->json(['error' => ['code' => 403, 'message' => 'Forbidden']], 403);

        // IDs всех сотрудников смены
        $workerIds = DB::table('shift_workers')->where('work_shift_id', $shift->id)->pluck('id');

        // Заказы со статусом 1 или 2
        $orders = DB::table('orders')
            ->whereIn('shift_worker_id', $workerIds)
            ->whereIn('status_order_id', [1, 2])
            ->orderBy('created_at')
            ->get();

        $result = [];
        foreach ($orders as $o) {
            // Столик
            $table = DB::table('tables')->where('id', $o->table_id)->value('name');
            // Сотрудник
            $worker = DB::table('shift_workers')->join('users', 'users.id', '=', 'shift_workers.user_id')
                ->where('shift_workers.id', $o->shift_worker_id)->value('users.name');
            // Статус
            $status = DB::table('status_orders')->where('id', $o->status_order_id)->value('name');
            // Цена
            $price = DB::table('order_menus')->join('menus', 'menus.id', '=', 'order_menus.menu_id')
                ->where('order_id', $o->id)->sum(DB::raw('menus.price * order_menus.count'));

            $result[] = [
                'id' => $o->id,
                'table' => $table,
                'shift_workers' => $worker,
                'create_at' => $o->created_at,
                'status' => $status,
                'price' => round($price, 2)
            ];
        }

        return response()->json(['data' => $result], 200);
    }
}
