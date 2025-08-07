<?php

namespace App\Broadcasting;

use App\Models\User;

class ProgressChannel
{
    /**
     * Create a new channel instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Authenticate the user's access to the channel.
     *
     * @param  \App\Models\User  $user
     * @param  int  $userId
     * @return array|bool
     */
    public function join(User $user, int $userId)
    {
        // Users can only listen to their own progress channel
        // Admins can listen to any channel
        return $user->id === $userId || $user->is_admin;
    }
}
