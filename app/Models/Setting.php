<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
 // اسم الجدول (اختياري لأن الاسم سيكون تلقائيًا 'settings' بما أن الموديل بالـ plural)
    protected $table = 'settings';

    // الأعمدة القابلة للتعديل (التي يمكن ملؤها عبر الإدخال)
    protected $fillable = ['admin_id', 'key', 'value'];

    // علاقة مع الـ Admin
    public function admin() {
        return $this->belongsTo(Admin::class);  // كل إعداد ينتمي إلى مسؤول واحد
    }
}
