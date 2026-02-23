<?php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
Use App\Events\UserActivityEvent;
use App\Models\ActivityLog;

class SaveUserActivityListener
{
    /**
     * Create the event listener.
     */

     use InteractsWithQueue;
    public function __construct()
    {
        
    }

    /**
     * Handle the event.
     */
    public function handle(UserActivityEvent $event): void
    {
        ActivityLog::create([
            'user_id' => $event->user?->id,
            'action' => $event->action,
            'model' => $event->model,
            'model_id' => $event->modelId,
            'description' => $event->description,
            'properties' => $event->properties,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
