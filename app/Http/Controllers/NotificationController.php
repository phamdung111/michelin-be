<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\NotificationActor;
use App\Models\NotificationEntityType;
use App\Models\NotificationObject;
use stdClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        try{
            $offset = $request->input('offset');
            $limit = $request->input('limit');
            $notifications = Notification::where('receiver_id',auth()->user()->id)
            ->orderByDesc('created_at')
            ->offset($offset)
            ->limit($limit)
            ->get();
            
            $notificationsMapper = [];
            foreach ($notifications as $notification) {
                $notificationObject = NotificationObject::where('id',$notification->notification_object_id)
                ->first();
                $notificationEntityType = NotificationEntityType::where('id',$notificationObject->entity_type_id)->first();
                $notificationActor = NotificationActor::where('notification_object_id',$notificationObject->id)
                ->first();

                $actor = DB::table($notificationActor->actor_entity_table)->where('id',$notificationActor->actor_id)->first();
                $notificationResponse = new stdClass();
                $notificationResponse->actorId = $notificationActor->id;
                $notificationResponse->actorName = $actor->name;

                $actorAvatar = '';
                !str_starts_with($actor->avatar,'https') ? $actorAvatar = Storage::url($actor->avatar) : $actorAvatar = $actor->avatar;

                $notificationResponse->actorAvatar = $actorAvatar;
                $notificationResponse->actorType = $notificationActor->actor_entity_table;
                $notificationResponse->notificationType = $notificationEntityType->entity_table . ' ' . $notificationEntityType->notification_type;
                $notificationResponse->description = $notificationEntityType->description;
                $notificationResponse->notificationId = $notification->id;
                $notificationResponse->time = date_format($notification->created_at,'Y-m-d H:i');
                $notificationsMapper[] = $notificationResponse;
            }

            $notificationUnread = Notification::where('receiver_id',auth()->user()->id)
            ->where('status', '0')
            ->get();
            foreach($notificationUnread as $notification) {
                $notification->status = true;
                $notification->save();
            }
            $total = Notification::where('receiver_id',auth()->user()->id)->count();
            return response()->json([
                'notifications' => $notificationsMapper,
                'total' => $total
            ],200);
        }catch(\Exception $e) {
            return response()->json(['errors'=>$e->getMessage()],400);
        }        
    }

    public function countUnread(){
        try{
            $number = DB::table('notifications')
            ->where('receiver_id','=',auth()->user()->id)
            ->where('status','=', false)
            ->count();
            return response()->json($number,200);
        }catch(\Exception $e){
            return response()->json(['errors'=>$e->getMessage()],400);  
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
