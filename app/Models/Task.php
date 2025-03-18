<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'content',
        'completed'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'completed' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * スコープ：完了していないタスクを取得
     */
    public function scopeIncomplete($query)
    {
        return $query->where('completed', false);
    }

    /**
     * スコープ：完了したタスクを取得
     */
    public function scopeCompleted($query)
    {
        return $query->where('completed', true);
    }

    /**
     * タスクを完了に設定
     */
    public function markAsCompleted()
    {
        $this->update(['completed' => true]);
    }

    /**
     * タスクを未完了に設定
     */
    public function markAsIncomplete()
    {
        $this->update(['completed' => false]);
    }

    /**
     * タスクの完了状態を切り替え
     */
    public function toggleCompleted()
    {
        $this->update(['completed' => !$this->completed]);
    }
}
