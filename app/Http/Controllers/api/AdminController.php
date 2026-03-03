<?php

namespace App\Http\Controllers\api;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AdminRequest;
use App\Http\Requests\Api\Work\OpenAndCloseRequest;
use App\Http\Requests\Api\Work\UserWorkRequest;
use App\Http\Requests\Api\Work\WorkShiftRequest;
use App\Models\Order;
use App\Models\StatusOrder;
use App\Models\OrderMenu;
use App\Models\Role;
use App\Models\ShiftWorker;
use App\Models\Table;
use App\Models\User;
use App\Models\WorkShift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function index() {
        $users = User::all();
        return response()->json($users, 200);
    }
    public function register(AdminRequest $request) {
        $data = [
            'name'       => $request->name,
            'surname'    => $request->surname,
            'patronymic' => $request->patronymic,
            'login'      => $request->login,
            'password'   => $request->password,
            'role_id'    => $request->role_id,
            'status'     => 'created',
            'photo_file' => null,
        ];

        // Если загружен файл фото
        if ($request->hasFile('photo_file')) {
            $path = $request->file('photo_file')->store('photos', 'public');
            $data['photo_file'] = $path;
        }

        $user = User::create($data);

        return response()->json([
            'data' => [
                'id'         => $user->id,
                'status'     => $user->status,
            ]
        ], 201);
    }
    public function storeWork(WorkShiftRequest $request) {
        $workShift = WorkShift::create([
            'start' => $request->start,
            'end'   => $request->end,
        ]);

        return response()->json([
            'id'    => $workShift->id,
            'start' => $workShift->start,
            'end'   => $workShift->end,
        ], 201);
    }
    public function openWork(OpenAndCloseRequest $request , $id)
    {
        if (WorkShift::where('active', true)->exists()) {
            return response()->json([
                'error' => [
                    'code' => 403,
                    'message' => 'Forbidden. There are open shifts!'
                ]
            ], 403);
        }

        $workShift = WorkShift::find($id);

        if (!$workShift) {
            return response()->json([
                'error' => [
                    'code' => 404,
                    'message' => 'Work shift not found'
                ]
            ], 404);
        }

        $workShift->update(['active' => true]);

        return response()->json([
            'data' => [
                'id'     => $workShift->id,
                'start'  => $workShift->start,
                'end'    => $workShift->end,
                'active' => true
            ]
        ]);
    }
    public function closeWork(OpenAndCloseRequest $request ,$id){

        $workShift = WorkShift::findOrFail($id);
        if (!$workShift->active) {
            return response()->json([
                'error' => [
                    'code' => 403,
                    'message' => 'Forbidden. The shift is already closed!'
                ]
            ], 403);
        }
        $workShift->update(['active' => false]);
        return response()->json([
            'data' => [
                'id'     => $workShift->id,
                'start'  => $workShift->start,
                'end'    => $workShift->end,
                'active' => false
            ]
        ]);
    }
    public function userWork(UserWorkRequest $request , $id){
        $workShift = WorkShift::findOrFail($id);
        $userId = $request->user_id;

        if (ShiftWorker::where('work_shift_id', $id)
            ->where('user_id', $userId)
            ->exists()) {
            return response()->json([
                'error' => [
                    'code' => 403,
                    'message' => 'Forbidden. The worker is already on shift!'
                ]
            ], 403);
        }

        // Добавляем связь
        ShiftWorker::create([
            'work_shift_id' => $id,
            'user_id'       => $userId,
        ]);

        return response()->json([
            'data' => [
                'id_user' => $userId,
                'status'  => 'added'
            ]
        ]);
    }

    public function show($id)
    {
        $user = User::findOrFail($id);
        return response()->json($user, 200);

    }

    public function toDismiss( $id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return response()->json(['message' => 'User delete'], 200);
    }

    public function workShift()
    {
        $workShift = WorkShift::all();
        return response()->json($workShift, 200);

    }

    public function destroyUserWork($work, $user)
    {
        $workShift = WorkShift::findOrFail($work);
        $user = User::findOrFail($user);

        $deleted = ShiftWorker::where('work_shift_id', $workShift->id)
            ->where('user_id', $user->id)
            ->delete();

        if ($deleted === 0) {
            return response()->json([
                'message' => 'User not found in this work shift'
            ], 404);
        }
        return response()->json([
            'message' => 'User successfully removed from work shift'
        ], 200);

    }

    public function orderWork(string $id)
    {
        $workShift = WorkShift::findOrFail($id);

        $shiftWorkerIds = ShiftWorker::where('work_shift_id', $id)->pluck('id');

        $orders = Order::whereIn('shift_worker_id', $shiftWorkerIds)->get();

        $ordersData = $orders->map(function($order) {
            $status = StatusOrder::find($order->status_order_id);

            $table = Table::find($order->table_id);

            $shiftWorker = ShiftWorker::with('user')->find($order->shift_worker_id);

            $price = OrderMenu::where('order_id', $order->id)
                ->join('menus', 'order_menus.menu_id', '=', 'menus.id')
                ->sum(\DB::raw('menus.price * order_menus.count'));

            return [
                'id' => $order->id,
                'table' => $table ? $table->name : 'Unknown',
                'shift_workers' => $shiftWorker && $shiftWorker->user ? $shiftWorker->user->name : 'Unknown',
                'create_at' => $order->created_at,
                'status' => $status ? $status->name : 'Unknown',
                'price' => round($price, 2)
            ];
        });

        $amountForAll = $ordersData->sum('price');

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
}
