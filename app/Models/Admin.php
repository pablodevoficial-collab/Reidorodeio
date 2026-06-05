<?php

namespace App\Models;


use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class Admin extends Authenticatable
{
    private const ALWAYS_ALLOWED_ROUTE_PREFIXES = [
        'admin.dashboard',
        'admin.profile',
        'admin.password',
        'admin.notifications',
        'admin.notification.',
        'admin.request.report',
    ];

    private const ROUTE_PERMISSION_PREFIX_MAP = [
        'admin.users.bots.' => 'users_bots',
        'admin.profits.' => 'profits',
        'admin.app_control.' => 'app_control',
        'admin.modalidade_odds.' => 'modalidade_odds',
        'admin.rodeios.' => 'rodeios',
        'admin.sponsors.' => 'rodeios',
        'admin.category.' => 'rodeios',
        'admin.modalidade.' => 'modalidades',
        'admin.modalidades.' => 'modalidades',
        'admin.competitors.' => 'competitors',
        'admin.live_transmission.' => 'live_transmission',
        'admin.x1.' => 'x1',
        'admin.competitor_stats.' => 'competitor_stats',
        'admin.dynamic_selection.' => 'dynamic_selection',
        'admin.quick_scoring.' => 'quick_scoring',
        'admin.queues.' => 'queues',
        'admin.ads.' => 'ads',
        'admin.affiliates.' => 'affiliates',
        'admin.fantasy_prizes.' => 'fantasy_leagues',
        'admin.fantasy_leagues.' => 'fantasy_leagues',
        'admin.users.' => 'users',
        'admin.subscriber.' => 'subscriber',
        'admin.report.' => 'reports',
        'admin.ticket.' => 'tickets',
        'admin.language.' => 'language',
        'admin.kyc.' => 'settings',
        'admin.setting.' => 'settings',
        'admin.extensions.' => 'settings',
        'admin.system.' => 'system',
        'admin.cron.' => 'system',
        'admin.frontend.' => 'frontend',
        'admin.seo' => 'frontend',
    ];

    private static ?bool $permissionsColumnExists = null;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public static function supportsGranularPermissions(): bool
    {
        if (self::$permissionsColumnExists !== null) {
            return self::$permissionsColumnExists;
        }

        try {
            self::$permissionsColumnExists =
                Schema::hasTable((new static())->getTable())
                && Schema::hasColumn((new static())->getTable(), 'permissions');
        } catch (\Throwable $e) {
            self::$permissionsColumnExists = false;
        }

        return self::$permissionsColumnExists;
    }

    public function adminPermissions(): array
    {
        if (!self::supportsGranularPermissions()) {
            return ['*'];
        }

        $raw = trim((string) ($this->getAttribute('permissions') ?? ''));
        if ($raw === '') {
            return ['*'];
        }

        if ($raw === '*' || strcasecmp($raw, 'all') === 0) {
            return ['*'];
        }

        $decoded = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return collect($decoded)
                ->flatten()
                ->map(fn ($value) => $this->normalizePermissionValue($value))
                ->filter()
                ->values()
                ->all();
        }

        return collect(preg_split('/[\s,;|]+/', $raw) ?: [])
            ->map(fn ($value) => $this->normalizePermissionValue($value))
            ->filter()
            ->values()
            ->all();
    }

    public function isSuperAdmin(): bool
    {
        return in_array('*', $this->adminPermissions(), true);
    }

    public function hasAdminPermission(string $permission): bool
    {
        if ($permission === '') {
            return true;
        }

        return $this->isSuperAdmin() || in_array($permission, $this->adminPermissions(), true);
    }

    public static function permissionKeyForRoute(?string $routeName): string|bool|null
    {
        if (!$routeName) {
            return false;
        }

        foreach (self::ALWAYS_ALLOWED_ROUTE_PREFIXES as $prefix) {
            if (Str::startsWith($routeName, $prefix)) {
                return null;
            }
        }

        foreach (self::ROUTE_PERMISSION_PREFIX_MAP as $prefix => $permission) {
            if (Str::startsWith($routeName, $prefix)) {
                return $permission;
            }
        }

        return false;
    }

    public function canAccessAdminRoute(?string $routeName): bool
    {
        $permission = self::permissionKeyForRoute($routeName);

        if ($permission === null) {
            return true;
        }

        if ($permission === false) {
            return $this->isSuperAdmin();
        }

        return $this->hasAdminPermission($permission);
    }

    private function normalizePermissionValue(mixed $value): string
    {
        $normalized = trim((string) $value);

        if ($normalized === '') {
            return '';
        }

        return strcasecmp($normalized, 'all') === 0 ? '*' : $normalized;
    }
}
