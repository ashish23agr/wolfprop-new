<?php

namespace App\Models;

use Corcel\Model\User as CorcelUser;

/**
 * WordPress-backed auth user (via Corcel).
 *
 * Adds a `role` accessor so `Auth::user()->role` returns the value stored
 * in usermeta where meta_key = 'role' (e.g. 'admin' or 'member'), matching
 * how the old app stored roles.
 */
class WpUser extends CorcelUser
{
    /**
     * Read the custom `role` usermeta value.
     */
    public function getRoleAttribute()
    {
        return $this->getMeta('role');
    }
}