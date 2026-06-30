<?php

namespace App\Http\Controllers;

use App\Models\Sponsor;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class SponsorLogoController extends Controller
{
    public function show(Sponsor $sponsor): Response
    {
        $path = trim((string) ($sponsor->logo ?? ''));

        abort_if($path === '', 404);

        $path = str_replace('\\', '/', ltrim($path, '/'));
        $lower = strtolower($path);

        if (str_starts_with($lower, 'public/')) {
            $path = substr($path, 7);
            $lower = strtolower($path);
        }

        if (str_starts_with($lower, 'assets/')) {
            $fullPath = public_path($path);
        } else {
            if (str_starts_with($lower, 'storage/')) {
                $path = substr($path, 8);
            }

            $fullPath = Storage::disk('public')->path($path);
        }

        abort_unless(is_file($fullPath), 404);

        return response()->file($fullPath, [
            'Cache-Control' => 'public, max-age=604800',
        ]);
    }
}
