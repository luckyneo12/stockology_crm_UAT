<?php

namespace Workdo\Hrm\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'parent_id',
        'manager_id',
        'levers_user_id',
        'name',
        'logo',
        'type',
        'workspace',
        'created_by'
    ];

    protected static function newFactory()
    {
        return \Workdo\Hrm\Database\factories\DepartmentFactory::new ();
    }

    public function branch()
    {
        return $this->hasOne(Branch::class , 'id', 'branch_id');
    }

    public function parent()
    {
        return $this->belongsTo(Department::class , 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Department::class , 'parent_id');
    }

    public function allChildIds($visited = [])
    {
        if (in_array($this->id, $visited)) {
            return [];
        }
        $visited[] = $this->id;

        $ids = [$this->id];
        foreach ($this->children as $child) {
            $ids = array_merge($ids, $child->allChildIds($visited));
        }
        return $ids;
    }

    public function manager()
    {
        return $this->hasOne(\Workdo\Hrm\Entities\Employee::class , 'id', 'manager_id')->with('user');
    }

    public function employees()
    {
        return $this->hasMany(\Workdo\Hrm\Entities\Employee::class , 'department_id')->with('user');
    }
}
