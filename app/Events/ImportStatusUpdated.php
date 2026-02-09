<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class ImportStatusUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $id;
    public $file;
    public $status;
    public $rows;
    public $message;

    public function __construct($import)
    {
        $this->id = $import->id;
        $this->file = $import->file_name;
        $this->status = $import->status;
        $this->rows = $import->rows_inserted;
        $this->message = $import->message;
    }

    public function broadcastOn()
    {
        return new Channel('imports');
    }

    public function broadcastAs()
    {
        return 'ImportStatusUpdated';
    }

    public function broadcastWith()
    {
        return [
            'id' => $this->id,
            'file' => $this->file,
            'status' => $this->status,
            'rows' => $this->rows,
            'message' => $this->message,
        ];
    }
}
