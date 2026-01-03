<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CalendarSource extends Model
{

    protected $fillable = [
        'school_id',
        'filename',
        'storage_path',
        'url'
    ];

    /**
     * Relation vers l'École (Une source appartient à une école)
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Optionnel : Si vous voulez lier les lignes de planning directement à leur source
     * Cela permet de supprimer toutes les sessions d'un fichier précis si on s'est trompé.
     */
    public function plannings(): HasMany
    {
        return $this->hasMany(Planning::class);
    }
}
