<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Waiter\OrderRequest;
use App\Http\Requests\Api\Waiter\PositionOrderRequest;
use App\Http\Requests\Api\Waiter\StatusOrderRequest;
use App\Models\Order;
use App\Models\OrderMenu;
use App\Models\ShiftWorker;
use App\Models\StatusOrder;
use App\Models\Table;
use App\Models\WorkShift;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WaiterController extends Controller
{

    public function order(OrderRequest $request)
    {
        $user = Auth::user();
        $workShiftId = $request->work_shift_id;
        $tableId = $request->table_id;
        $numberOfPerson = $request->number_of_person;

        $workShift = WorkShift::findOrFail($workShiftId);
        if (!$workShift->active) {
            return response()->json([
                'error' => [
                    'code' => 403,
                    'message' => 'Forbidden. The shift must be active!'
                ]
            ], 403);
        }

        $shiftWorker = ShiftWorker::where('work_shift_id', $workShiftId)
            ->where('user_id', $user->id)
            ->first();

        if (!$shiftWorker) {
            return response()->json([
                'error' => [
                    'code' => 403,
                    'message' => "Forbidden. You don't work this shift!"
                ]
            ], 403);
        }

        $order = Order::create([
            'table_id' => $tableId,
            'shift_worker_id' => $shiftWorker->id,
            'status_order_id' => 1, // Принят
            'number_of_person' => $numberOfPerson,
        ]);

        $table = Table::find($order->table_id);
        $status = StatusOrder::find($order->status_order_id);
        $workerUser = $shiftWorker->user;

        return response()->json([
            'data' => [
                'id' => $order->id,
                'table' => $table ? $table->name : 'Unknown',
                'shift_workers' => $workerUser ? $workerUser->name : 'Unknown',
                'create_at' => $order->created_at ? $order->created_at->toIso8601String() : now()->toIso8601String(),
                'status' => $status ? $status->name : 'Unknown',
                'price' => 0
            ]
        ], 200);
    }

    public function orderShow( $id)
    {
        $user = Auth::user();

        $order = Order::findOrFail($id);

        $shiftWorker = ShiftWorker::where('user_id', $user->id)
            ->where('id', $order->shift_worker_id)
            ->first();

        if (!$shiftWorker) {
            return response()->json([
                'error' => [
                    'code' => 403,
                    'message' => 'Forbidden. You did not accept this order!'
                ]
            ], 403);
        }

        $table = Table::find($order->table_id);
        $status = StatusOrder::find($order->status_order_id);
        $workerUser = $shiftWorker->user;

        $positions = OrderMenu::where('order_id', $order->id)
            ->join('menus', 'order_menus.menu_id', '=', 'menus.id')
            ->select('order_menus.id', 'order_menus.count', 'menus.name as position', 'menus.price')
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->id,
                    'count' => $item->count,
                    'position' => $item->position,
                    'price' => round($item->price * $item->count, 2)
                ];
            });

        $priceAll = $positions->sum('price');

        return response()->json([
            'data' => [
                'id' => $order->id,
                'table' => $table ? $table->name : 'Unknown',
                'shift_workers' => $workerUser ? $workerUser->name : 'Unknown',
                'create_at' => $order->created_at ? $order->created_at->toIso8601String() : null,
                'status' => $status ? $status->name : 'Unknown',
                'positions' => $positions,
                'price_all' => round($priceAll, 2)
            ]
        ], 200);
    }

    public function orderByShift($id)
    {
        $user = Auth::user();

        $shiftWorker = ShiftWorker::where('work_shift_id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$shiftWorker) {
            return response()->json([
                'error' => [
                    'code' => 403,
                    'message' => 'Forbidden. You did not accept this order!'
                ]
            ], 403);
        }

        $workShift = WorkShift::findOrFail($id);

        $shiftWorkerIds = ShiftWorker::where('work_shift_id', $id)->pluck('id');

        $orders = Order::whereIn('shift_worker_id', $shiftWorkerIds)
            ->with(['table', 'statusOrder', 'shiftWorker.user'])
            ->get();

        $ordersData = $orders->map(function($order) {
            // Получаем позиции заказа
            $positions = OrderMenu::where('order_id', $order->id)
                ->join('menus', 'order_menus.menu_id', '=', 'menus.id')
                ->select('order_menus.id', 'order_menus.count', 'menus.name as position', 'menus.price')
                ->get()
                ->map(function($item) {
                    return [
                        'id' => $item->id,
                        'count' => $item->count,
                        'position' => $item->position,
                        'price' => round($item->price * $item->count, 2)
                    ];
                });

            $priceAll = $positions->sum('price');

            $workerName = '';
            if ($order->shiftWorker && $order->shiftWorker->user) {
                $workerName = $order->shiftWorker->user->name;
            }

            return [
                'id' => $order->id,
                'table' => $order->table ? $order->table->name : 'Unknown',
                'shift_workers' => $workerName,
                'create_at' => $order->created_at ? $order->created_at->toIso8601String() : null,
                'status' => $order->statusOrder ? $order->statusOrder->name : 'Unknown',
                'positions' => $positions,
                'price_all' => round($priceAll, 2)
            ];
        });

        $amountForAll = $ordersData->sum('price_all');

        return response()->json([
            'data' => [
                'id' => $workShift->id,
                'start' => $workShift->start,
                'end' => $workShift->end,
                'active' => (int)$workShift->active,
                'orders' => $ordersData,
                'amount_for_all' => round($amountForAll, 2)
            ]
        ], 200);
    }

    public function changeStatus(StatusOrderRequest $request ,$id){
        $user = Auth::user();
        $order = Order::findOrFail($id);
        if ($order->shiftWorker->user_id != $user->id) {
            return response()->json([
                'error' => [
                    'code' => 403,
                    'message' => 'Forbidden! You did not accept this order!'
                ]
            ], 403);
        }
        if (!$order->shiftWorker->workShift->active) {
            return response()->json([
                'error' => [
                    'code' => 403,
                    'message' => 'You cannot change the order status of a closed shift!'
                ]
            ], 403);
        }
        $allowed = [
            1 => [5],
            3 => [4],
        ];

        $currentStatus = $order->status_order_id;
        $newStatus = StatusOrder::where('code', $request->status)->first()->id;

        if (!isset($allowed[$currentStatus]) || !in_array($newStatus, $allowed[$currentStatus])) {
            return response()->json([
                'error' => [
                    'code' => 403,
                    'message' => "Forbidden! Can't change existing order status"
                ]
            ], 403);
        }

        $order->status_order_id = $newStatus;
        $order->save();

        return response()->json([
            'data' => [
                'id' => $order->id,
                'status' => $request->status
            ]
        ], 200);
    }

    public function orderPosition(PositionOrderRequest $request , $id)
    {
        $menuId = $request -> menu_id;
        $count = $request -> count;

        $order = OrderMenu::create([
            'menu_id' => $menuId,
            'order_id' => $id,
            'count' => $count
        ]);
        return response()->json([
            'data' => [
                'id' => $order->id,
                'count' => $count,
            ]
        ]);

    }

    public function destroyOrder( $order,  $position)
    {
        $user = Auth::user();

        // 1. Получаем позицию заказа
        $orderMenu = OrderMenu::findOrFail($position);

        // 2. Проверяем, что позиция принадлежит указанному заказу
        if ($orderMenu->order_id != $order) {
            return response()->json([
                'error' => [
                    'code' => 400,
                    'message' => 'Position does not belong to this order!'
                ]
            ], 400);
        }

        // 3. Получаем заказ
        $order = Order::findOrFail($order);

        // 4. Проверяем: это заказ официанта?
        if ($order->shiftWorker->user_id != $user->id) {
            return response()->json([
                'error' => [
                    'code' => 403,
                    'message' => 'Forbidden! You did not accept this order!'
                ]
            ], 403);
        }

        // 5. Проверяем: смена активна?
        if (!$order->shiftWorker->workShift->active) {
            return response()->json([
                'error' => [
                    'code' => 403,
                    'message' => 'You cannot change the order of a closed shift!'
                ]
            ], 403);
        }

        // 6. Проверяем статус заказа (можно удалять только из "Принят" или "Готовится")
        $allowedStatuses = [1, 2]; // taken, preparing
        if (!in_array($order->status_order_id, $allowedStatuses)) {
            return response()->json([
                'error' => [
                    'code' => 403,
                    'message' => 'Cannot delete position from this order status!'
                ]
            ], 403);
        }

        // 7. Получаем данные позиции для ответа
        $menu = $orderMenu->menu;
        $responseData = [
            'id' => $orderMenu->id,
            'count' => $orderMenu->count,
            'position' => $menu ? $menu->name : 'Unknown',
            'price' => $menu ? round($menu->price * $orderMenu->count, 2) : 0
        ];

        // 8. Удаляем позицию
        $orderMenu->delete();

        // 9. Считаем новую общую сумму заказа
        $totalPrice = OrderMenu::where('order_id', $order)
            ->join('menus', 'order_menus.menu_id', '=', 'menus.id')
            ->sum(DB::raw('menus.price * order_menus.count'));

        return response()->json([
            'data' => [
                'deleted_position' => $responseData,
                'order_id' => $order,
                'price_all' => round($totalPrice, 2)
            ]
        ], 200);
    }
}
