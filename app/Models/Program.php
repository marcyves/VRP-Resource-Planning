<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Program extends Model
{
    use HasFactory;

    public $timestamps = false;

    public $fillable = ['name', 'short_description'];

    public function courses(): HasMany
    {
        return $this->HasMany(Course::class);
    }

    public static function labelFrom(?string $shortDescription, ?string $name): string
    {
        $short = trim((string) $shortDescription);

        return $short !== '' ? $short : (string) $name;
    }

    public function listLabel(): string
    {
        return self::labelFrom($this->short_description, $this->name);
    }
}
