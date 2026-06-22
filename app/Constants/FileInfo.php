<?php

namespace App\Constants;

class FileInfo
{
    /**
     * Get file information for all file types in the system
     * 
     * @return array
     */
    public function fileInfo()
    {
        return [
            'logoIcon' => [
                'path' => 'assets/images/logo_icon',
            ],
            'withdraw' => [
                'path' => 'assets/images/withdraw/method',
            ],
            'ticket' => [
                'path' => 'assets/support',
            ],
            'language' => [
                'path' => 'assets/images/language',
            ],
            'extensions' => [
                'path' => 'assets/images/extensions',
            ],
            'push' => [
                'path' => 'assets/images/push',
                'size' => '800x800',
            ],
            'pushConfig' => [
                'path' => 'storage/app/push',
            ],
            'seo' => [
                'path' => 'assets/images/seo',
            ],
            'userProfile' => [
                'path' => 'assets/images/user/profile',
                'size' => '400x400',
            ],
            'adminProfile' => [
                'path' => 'assets/admin/images/profile',
                'size' => '400x400',
            ],
            'deposit' => [
                'path' => 'assets/images/deposit',
            ],
            'verify' => [
                'path' => 'assets/verify',
            ],
            'gateway' => [
                'path' => 'assets/images/gateway',
                'size' => '800x800',
            ],
            'league' => [
                'path' => 'assets/images/league',
                'size' => '800x800',
            ],
            'team' => [
                'path' => 'assets/images/team',
                'size' => '800x800',
            ],
            'maintenance' => [
                'path' => 'assets/images/maintenance',
                'size' => '1200x800',
            ],
            'rodeio' => [
                'path' => 'storage/rodeios',
                'size' => '800x600',
            ],
            'competitor' => [
                'path' => 'storage/competitors',
                'size' => '400x400',
            ],
            'modalidade' => [
                'path' => 'storage/modalidades',
                'size' => '600x400',
            ],
        ];
    }
}
