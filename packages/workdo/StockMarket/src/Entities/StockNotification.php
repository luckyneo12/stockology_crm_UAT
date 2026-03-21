<?php

namespace Workdo\StockMarket\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class StockNotification extends Model
{
    protected $table = 'stock_notifications';

    protected $fillable = [
        'signal_id',
        'user_id',
        'is_read',
        'type',
        'workspace',
    ];

    public function signal()
    {
        return $this->belongsTo(StockSignal::class, 'signal_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Create notifications for all workspace users
    public static function notifyWorkspaceUsers($signal_id, $workspace, $type = 'new_signal')
    {
        $users = User::where('workspace_id', $workspace)->pluck('id');
        foreach ($users as $user_id) {
            self::create([
                'signal_id' => $signal_id,
                'user_id' => $user_id,
                'type' => $type,
                'workspace' => $workspace,
                'is_read' => false,
            ]);
        }
    }
}
