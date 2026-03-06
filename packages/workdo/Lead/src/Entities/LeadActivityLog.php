<?php

namespace Workdo\Lead\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LeadActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'lead_id',
        'log_type',
        'remark'
    ];

    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'user_id');
    }


    public function getLeadRemark()
    {
        $remark = json_decode($this->remark, true);
        if ($remark) {
            $user = $this->user;

            if ($user) {
                $user_name = $user->name;
            } else {
                $user_name = '';
            }

            if ($this->log_type == 'Upload File') {
                return $user_name . ' ' . __('Upload new file') . ' <b>' . $remark['file_name'] . '</b>';
            } elseif ($this->log_type == 'Add Product') {
                return $user_name . ' ' . __('Add new Products') . " <b>" . $remark['title'] . "</b>";
            } elseif ($this->log_type == 'Update Sources') {
                return $user_name . ' ' . __('Update Sources');
            } elseif ($this->log_type == 'Create Lead Call') {
                return $user_name . ' ' . __('Create new Lead Call');
            } elseif ($this->log_type == 'Create Lead Email') {
                return $user_name . ' ' . __('Create new Lead Email');
            } elseif ($this->log_type == 'Move') {
                $res = $user_name . " " . __('moved') . " <b>" . $remark['title'] . "</b> " .
                    '<span class="badge rounded-pill bg-light text-dark border mx-1">' . __(ucwords($remark['old_status'])) . '</span>' .
                    '<i class="ti ti-arrow-narrow-right text-muted mx-1"></i>' .
                    '<span class="badge rounded-pill bg-success-subtle text-success border border-success border-opacity-25 mx-1">' . __(ucwords($remark['new_status'])) . '</span>';

                if (isset($remark['transfer_msg'])) {
                    $res .= " <br><small class='text-muted'><i class='ti ti-user-check me-1'></i>" . $remark['transfer_msg'] . "</small>";
                }
                return $res;
            } elseif ($this->log_type == 'Create Task') {
                return $user_name . ' ' . __('Create new Task') . " <b>" . $remark['title'] . "</b>";
            } elseif ($this->log_type == 'Create Reminder') {
                return $user_name . ' ' . __('Create new Reminder') . " <b>" . $remark['title'] . "</b>";
            } elseif ($this->log_type == 'Lead Created' || $this->log_type == 'Lead Transferred') {
                return $remark['message'];
            } elseif ($this->log_type == 'Lead Updated') {
                return isset($remark['message']) ? $remark['message'] : $user_name . ' ' . __('updated lead details');
            } elseif ($this->log_type == 'Lead Imported') {
                return isset($remark['message']) ? $remark['message'] : $user_name . ' ' . __('imported this lead via CSV');
            } elseif ($this->log_type == 'Discussion' || $this->log_type == 'Note Updated') {
                return isset($remark['message']) ? $remark['message'] : $user_name . ' ' . __('performed an action');
            }
        } else {
            return $this->remark;
        }
    }

    public function logIcon()
    {
        $type = $this->log_type;
        $icon = '';

        if (!empty($type)) {
            if ($type == 'Move') {
                $icon = 'fa-arrows-alt';
            } elseif ($type == 'Add Product') {
                $icon = 'fa-dolly';
            } elseif ($type == 'Upload File') {
                $icon = 'fa-file-alt';
            } elseif ($type == 'Update Sources') {
                $icon = 'fa-pen';
            } elseif ($type == 'Create Lead Call') {
                $icon = 'fa-phone';
            } elseif ($type == 'Create Lead Email') {
                $icon = 'fa-envelope';
            } elseif ($type == 'Create Task') {
                $icon = 'fa-tasks';
            } elseif ($type == 'Create Reminder') {
                $icon = 'fa-clock';
            } elseif ($type == 'Lead Created') {
                $icon = 'fa-plus';
            } elseif ($type == 'Lead Transferred') {
                $icon = 'fa-exchange-alt';
            } elseif ($type == 'Lead Updated') {
                $icon = 'fa-edit';
            } elseif ($this->log_type == 'Lead Imported') {
                $icon = 'fa-file-import';
            } elseif ($this->log_type == 'Discussion') {
                $icon = 'fa-comments';
            } elseif ($this->log_type == 'Note Updated') {
                $icon = 'fa-sticky-note';
            }
        }

        return $icon;
    }
}
