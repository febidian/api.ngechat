<?php

namespace App\Http\Controllers;

use App\Http\Resources\AddFriendNotifResource;
use App\Http\Resources\NotificationsResource;
use App\Jobs\DeleteOldNotifications;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NotificationsController extends Controller
{
    public function index()
    {
        try {
            $user = User::where('id', Auth::user()->id)->first();
            $data = $user->notifications()->paginate(7);

            return response()->json([
                'notif' => NotificationsResource::collection($data)->response()->getData(),
                'status' => 'success'
            ], Response::HTTP_OK);
        } catch (QueryException $th) {
            return response()->json([
                'status' => 'failed',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function NotficationsRead(Request $request)
    {
        try {
            $idList = $request->input('idList');
            DB::table('notifications')->whereIn('id', $idList)->update(['read_at' => now()]);

            return response()->json([
                'status' => 'success'
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'failed',
                'message' => $th->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
