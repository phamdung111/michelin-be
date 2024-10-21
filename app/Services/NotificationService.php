<?php
namespace App\Services;
use App\Models\Notification;
use App\Models\NotificationActor;
use App\Models\NotificationObject;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class NotificationService {

  function store ($notificationEntityTypeId, $entityId, $actor_entity_table, $actor_id, $receivers) {
  try{
      $notificationObject = new NotificationObject();
      $notificationObject->entity_type_id = $notificationEntityTypeId;
      $notificationObject->entity_id = $entityId;
      $notificationObject->save();

      $notificationActor = new NotificationActor();
      $notificationActor->actor_id = $actor_id;
      $notificationActor->notification_object_id = $notificationObject->id;
      $notificationActor->actor_entity_table = $actor_entity_table;
      $notificationActor->save();

      $notification = new Notification();
      for($i = 0; $i < count($receivers); $i++){
          if($receivers[$i] !== null){
            $notification->receiver_id = $receivers[$i];
            $notification->notification_object_id = $notificationObject->id;
            $notification->status = false;
            $notification->save();
          }
      }

      $actorName = DB::table($notificationActor->actor_entity_table)->where('id',$notificationActor->actor_id)->first()->name;
      $actorAvatar = '';
      !str_starts_with(auth()->user()->avatar,'https') ? $actorAvatar = Storage::url(auth()->user()->avatar) : $actorAvatar = auth()->user()->avatar;
      $notificationType = DB::table('notification_entity_types')->where('id',$notificationEntityTypeId)->first();
      return [
        'actorId' => $notificationActor->actor_id,
        'actorName' => $actorName,
        'actorAvatar' => $actorAvatar,
        'actorType' => $notificationActor->actor_entity_table,
        'notificationType' => $notificationType->entity_table . ' ' . $notificationType->notification_type,
        'description' => $notificationType->description,
        'notificationId' => $notification->id,
        'time' => $notification->created_at
      ];
  }catch (\Exception $e){
      return response()->json(['errors'=> $e->getMessage()],400);
    }
  }

  function list () {
 
  }

  function update (){}

  function destroy( $notificationId ) {}
}
