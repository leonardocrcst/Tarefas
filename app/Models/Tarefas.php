<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @method where(string $string, string $string1, array|string|null $header)
 * @method static find(int $id)
 * @property mixed $tarefa
 * @property mixed $concluida
 * @property array|mixed|string|null $user
 * @property mixed $id
 */
class Tarefas extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        "tarefa",
        "concluida"
    ];

    protected $hidden = [
        "created_at",
        "updated_at",
        "user",
        "deleted_at"
    ];
}
