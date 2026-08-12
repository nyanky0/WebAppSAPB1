<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemLog extends Model
{
    protected $fillable = [
        'category',
        'action',
        'details',
        'user_id',
        'ip_address',
        'pc_name',
        'instant_sync',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'uid7');
    }

    public static function logAction($category, $action, $details = null, $instantSync = false)
    {
        self::create([
            'category' => $category,
            'action' => $action,
            'details' => is_array($details) ? json_encode($details) : $details,
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
            'pc_name' => gethostname() ?: 'Unknown',
            'instant_sync' => $instantSync,
        ]);
    }
}
