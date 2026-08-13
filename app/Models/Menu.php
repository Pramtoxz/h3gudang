<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    protected $table = 'menus';

    protected $fillable = [
        'project_id',
        'nama_menu',
        'ikon',
        'route',
        'url',
        'parent_id',
        'urutan',
        'status_aktif',
        'khusus_it',
    ];

    protected function casts(): array
    {
        return [
            'status_aktif' => 'boolean',
            'khusus_it' => 'boolean',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Menu::class, 'parent_id')->orderBy('urutan');
    }

    public function akses(): HasMany
    {
        return $this->hasMany(MenuAkses::class);
    }

    /**
     * Menu dengan project_id kosong berlaku lintas project.
     */
    public function scopeUntukProject(Builder $query, ?int $projectId): Builder
    {
        return $query->where(fn (Builder $q) => $q
            ->whereNull('project_id')
            ->orWhere('project_id', $projectId));
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('status_aktif', true);
    }
}
