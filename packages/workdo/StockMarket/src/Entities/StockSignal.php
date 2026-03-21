<?php

namespace Workdo\StockMarket\Entities;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;
use Workdo\StockMarket\Entities\StockActivityLog;

class StockSignal extends Model
{
    use HasFactory;

    protected $table = 'stock_signals';

    protected $fillable = [
        'title',
        'symbol',
        'exchange',
        'category_id',
        'type',
        'legs',
        'buy_price_min',
        'buy_price_max',
        'target',
        'stoploss',
        'quantity',
        'min_amount',
        'hold_duration',
        'description',
        'date',
        'status',
        'exit_price',
        'exit_at',
        'expiry_date',
        'close_reason',
        'workspace',
        'created_by',
    ];

    protected $casts = [
        'legs' => 'array',
        'date' => 'date',
        'exit_at' => 'datetime',
        'expiry_date' => 'date',
    ];

    // ─── Relations ───────────────────────────────────────────────

    public function category()
    {
        return $this->belongsTo(StockCategory::class, 'category_id');
    }

    public function adjustments()
    {
        return $this->hasMany(StockAdjustment::class, 'signal_id')->orderBy('created_at', 'desc');
    }

    public function notifications()
    {
        return $this->hasMany(StockNotification::class, 'signal_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ─── Helpers ─────────────────────────────────────────────────

    public function getEntryRangeAttribute()
    {
        if ($this->buy_price_min && $this->buy_price_max && $this->buy_price_min != $this->buy_price_max) {
            return '₹' . number_format($this->buy_price_min, 2) . ' - ₹' . number_format($this->buy_price_max, 2);
        }
        return '₹' . number_format($this->buy_price_min ?? $this->buy_price_max, 2);
    }

    public function getEstReturnsAttribute()
    {
        if ($this->buy_price_min > 0 && $this->target > 0) {
            $entry = ($this->buy_price_min + ($this->buy_price_max ?? $this->buy_price_min)) / 2;
            return round((($this->target - $entry) / $entry) * 100, 2);
        }
        return 0;
    }

    // Progress position of current price between SL and Target (0–100)
    public function pricePosition($current_price)
    {
        if (!$this->stoploss || !$this->target || $this->stoploss == $this->target)
            return 50;
        $pos = (($current_price - $this->stoploss) / ($this->target - $this->stoploss)) * 100;
        return max(0, min(100, $pos));
    }

    // ─── Default Data ────────────────────────────────────────────

    public static function defaultdata($company_id = null, $workspace_id = null)
    {
        $defaultCategories = [
            ['name' => 'Equity', 'type' => 'equity'],
            ['name' => 'F&O (Futures & Options)', 'type' => 'fo'],
        ];

        foreach ($defaultCategories as $cat) {
            StockCategory::firstOrCreate([
                'name' => $cat['name'],
                'workspace' => $workspace_id,
                'created_by' => $company_id,
            ], [
                'type' => $cat['type'],
            ]);
        }
    }

    // ─── Permissions ─────────────────────────────────────────────

    public static function GivePermissionToRoles($role_id, $rolename)
    {
        $permissions = self::stockPermissions();

        foreach ($permissions as $permission_name) {
            $permission = Permission::where('name', $permission_name)->first();
            if (!$permission) {
                $permission = Permission::create(['name' => $permission_name, 'guard_name' => 'web']);
            }

            $role = Role::find($role_id);
            if ($role && !$role->hasPermission($permission_name)) {
                $role->givePermission($permission);
            }
        }
    }

    public static function autoCloseIntradaySignals()
    {
        $signals = self::where('status', 'live')
            ->where('hold_duration', 'Intraday')
            ->get();

        foreach ($signals as $signal) {
            $signal->update([
                'status' => 'closed',
                'exit_price' => $signal->buy_price_min,
                'exit_at' => now(),
                'close_reason' => 'intraday_square_off'
            ]);

            StockActivityLog::create([
                'signal_id' => $signal->id,
                'user_id' => Auth::id() ?? $signal->created_by,
                'action' => 'Auto-Closed',
                'details' => json_encode(['exit_price' => $signal->buy_price_min, 'close_reason' => 'intraday_square_off']),
                'workspace_id' => $signal->workspace,
            ]);

            \Workdo\StockMarket\Entities\StockNotification::notifyWorkspaceUsers($signal->id, $signal->workspace, 'closed');
        }

        return count($signals);
    }

    public static function stockPermissions()
    {
        return [
            'stockmarket manage',
            'stockmarket dashboard manage',
            'signal manage',
            'signal create',
            'signal edit',
            'signal delete',
            'signal show',
            'adjustment manage',
            'adjustment create',
            'adjustment edit',
            'adjustment delete',
            'stock category manage',
            'stock category create',
            'stock category edit',
            'stock category delete',
            'stock setting manage',
            'stock notification manage',
        ];
    }
}
