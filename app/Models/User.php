<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Enums\DealStatusEnum;
use App\Enums\RoleEnum;
use App\Traits\Searchable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    use Searchable;

    public $searchable = ['name', 'email'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function assignedUsers() : BelongsToMany
    {
        return $this->belongsToMany(User::class, 'manager_user', 'owner_id', 'user_id');
    }

    public function managers() : BelongsToMany
    {
        return $this->belongsToMany(User::class, 'manager_user', 'user_id', 'owner_id');
    }

    public function branches() : BelongsToMany
    {
        return $this->belongsToMany(Branch::class, 'branch_user', 'user_id', 'branch_id');
    }

    public function tasks() : HasMany
    {
        return $this->hasMany(Task::class, 'owner_id');
    }

    public function lastIncompletedTasks() : HasMany
    {
        return $this->tasks()
            ->where('done', false)
            ->has('taskable')
            ->orderBy('due_at', 'ASC')
            ->limit(20);

    }

    public function leads() : HasMany
    {
        return $this->hasMany(Lead::class, 'owner_id');
    }

    public function customers() : HasMany
    {
        return $this->hasMany(Customer::class, 'owner_id');
    }

    public function deals() : HasMany
    {
        return $this->hasMany(Deal::class, 'owner_id');
    }

    public function wonDeals() : HasMany
    {
        return $this->deals()->where('status', DealStatusEnum::Won);
    }

    public function lostDeals() : HasMany
    {
        return $this->deals()->where('status', DealStatusEnum::Lost);
    }

    public function activeDeals() : HasMany
    {
        return $this->deals()
            ->whereIn('status', [DealStatusEnum::InProgress->value, DealStatusEnum::New->value]);
    }

    public function activeDealsFromPublicity() : HasMany
    {
        return $this->deals()
            ->where('status', DealStatusEnum::InProgress)
            ->where('source_id', 2);
    }

    public function warningDeals() : HasMany
    {
        return $this->deals()
            ->whereIn('status', [DealStatusEnum::InProgress->value, DealStatusEnum::New->value])
            ->where(function($query) {
                $query->doesntHave('lastActivetask')
                    ->orWhereHas('lastActivetask', function($query) {
                        $query->where('due_at', '<', now()->format('Y-m-d'));
                    });
            });
    }

    public function warningDealsFromPublicity() : HasMany
    {
        return $this->deals()
            ->where('status', DealStatusEnum::InProgress)
            ->where('source_id', 2)
            ->where(function($query) {
                $query->doesntHave('lastActivetask')
                    ->orWhereHas('lastActivetask', function($query) {
                        $query->where('due_at', '<', now()->format('Y-m-d'));
                    });
            });

    }

    public function opportunities() : HasMany
    {
        return $this->hasMany(Deal::class, 'owner_id')->where('type', 'oportunidad');
    }

    public function quotations() : HasMany
    {
        return $this->hasMany(Deal::class, 'owner_id')->where('type', 'cotizado');
    }

    public function opportunitiesEstimatedSizeSum(): int
    {
        return $this->opportunities()->sum('value');
    }

    public function opportunitiesScore(): int
    {
        return floor($this->opportunitiesEstimatedSizeSum() / 1_000_000);
    }

    public function quotationsEstimatedSizeSum(): int
    {
        return $this->quotations()->sum('value');
    }

    public function quotationsScore(): int
    {
        return (floor($this->quotationsEstimatedSizeSum() / 1_000_000) * 2);
    }

    protected function totalScore(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $this->quotationsScore() + $this->opportunitiesScore(),
        );
    }

    public function scopeBranch($query, $branch)
    {
        return $query->where('branch', $branch);
    }

    public function hasRole(string $role): bool
    {
        return $this->role == $role;
    }

    public function getAssignedUsersIdByRole(): ?array
    {
        if ($this->isSuperAdminOrDirector() || $this->role == RoleEnum::LeadQualifier->value) {
            return null;
        }

        if ($this->role == RoleEnum::Admin->value) {
            $branches = $this->branches()->get()->pluck('name')->toArray();

            if (empty($branches)) {
                $branches = [$this->branch];
            }

            return $this->whereIn('branch', $branches)
                ->get()
                ->pluck('id')
                ->toArray();
        }

        if ($this->role == RoleEnum::Advisor->value) {
            return [$this->id];
        }

        if ($this->role == RoleEnum::TeamLeader->value) {
            return $this->assignedUsers()->get()->pluck('id')->push($this->id)->toArray();
        }
    }

    public function isSuperAdminOrDirector(): bool
    {
        if (
            $this->hasRole(RoleEnum::Superadmin->value) ||
            $this->hasRole(RoleEnum::Director->value)
        ) {
            return true;
        }

        return false;
    }

}
