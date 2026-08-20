<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacebookConnection extends Model
{
    protected $fillable = ['page_id', 'page_name', 'access_token', 'connected_by'];

    protected $hidden = ['access_token'];

    protected function casts(): array
    {
        return [
            'access_token' => 'encrypted',
        ];
    }
}
