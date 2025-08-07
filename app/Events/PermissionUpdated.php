<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PermissionUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $eventType;
    public $resourceId;
    public $affectedIds;
    public $timestamp;

    /**
     * Create a new event instance.
     *
     * @param string $eventType
     * @param int|string $resourceId
     * @param array $affectedIds
     * @return void
     */
    public function __construct(string $eventType, $resourceId, $affectedIds = [])
    {
        $this->eventType = $eventType;
        $this->resourceId = $resourceId;
        $this->affectedIds = is_array($affectedIds) ? $affectedIds : [$affectedIds];
        $this->timestamp = now()->toDateTimeString();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        $channels = [
            new PrivateChannel('permission-updates')
        ];

        // Add user-specific channels for affected users
        if ($this->eventType === 'course_assignment') {
            foreach ($this->affectedIds as $userId) {
                $channels[] = new PrivateChannel("user.{$userId}.permissions");
            }
        }

        return $channels;
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'permission.updated';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'event' => $this->eventType,
            'resource_id' => $this->resourceId,
            'affected_ids' => $this->affectedIds,
            'timestamp' => $this->timestamp,
            'message' => $this->getEventMessage()
        ];
    }

    /**
     * Get a human-readable message for the event
     *
     * @return string
     */
    protected function getEventMessage(): string
    {
        switch ($this->eventType) {
            case 'course_assignment':
                return count($this->affectedIds) > 1 
                    ? 'Course access has been updated for multiple users.'
                    : 'Course access has been updated for user.';
            case 'group_created':
                return 'A new user group has been created.';
            case 'course_access_updated':
                return 'Course access rules have been updated.';
            default:
                return 'Permission settings have been updated.';
        }
    }
}
