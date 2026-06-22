<?php

use App\Constants\Status;
use App\Lib\Captcha;
use App\Lib\ClientInfo;
use App\Lib\CurlRequest;
use App\Lib\FileManager;
use App\Lib\GoogleAuthenticator;
use App\Models\Extension;
use App\Models\Frontend;
use App\Models\GeneralSetting;
use App\Models\Language;
use App\Notify\Notify;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

function systemDetails() {
    $system['name']          = 'rei-central';
    $system['version']       = '4.0';
    $system['build_version'] = '5.1.6';
    return $system;
}

function slug($string) {
    return Str::slug($string);
}

function getExtensionScript($key) {
    $extension = Extension::where('act', $key)->where('status', Status::ENABLE)->first();
    return $extension ? $extension->generateScript() : '';
}

function loadCustomCaptcha($width = '100%', $height = 46, $bgColor = '#003') {
    try {
        $captcha = Captcha::customCaptcha($width, $height, $bgColor);
        return $captcha ?: null;
    } catch (\Throwable $e) {
        return null;
    }
}

function loadReCaptcha() {
    try {
        return Captcha::reCaptcha();
    } catch (\Throwable $e) {
        return null;
    }
}

function verifyCaptcha() {
    try {
        return Captcha::verify();
    } catch (\Throwable $e) {
        return true;
    }
}

function getTrx($length = 12) {
    $characters       = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ123456789';
    $charactersLength = strlen($characters);
    $randomString     = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function getAmount($amount, $length = 2) {
    $amount = round($amount ?? 0, $length);
    return $amount + 0;
}

function showAmount($amount, $decimal = 2, $separate = true, $exceptZeros = false, $currencyFormat = true) {
    $separator = '';
    if ($separate) {
        $separator = ',';
    }
    $printAmount = number_format($amount, $decimal, '.', $separator);
    if ($exceptZeros) {
        $exp = explode('.', $printAmount);
        if ($exp[1] * 1 == 0) {
            $printAmount = $exp[0];
        } else {
            $printAmount = rtrim($printAmount, '0');
        }
    }
    if ($currencyFormat) {
        if (gs('currency_format') == Status::CUR_BOTH) {
            return gs('cur_sym') . $printAmount . ' ' . __(gs('cur_text'));
        } else if (gs('currency_format') == Status::CUR_TEXT) {
            return $printAmount . ' ' . __(gs('cur_text'));
        } else {
            return gs('cur_sym') . $printAmount;
        }
    }
    return $printAmount;
}

function removeElement($array, $value) {
    return array_diff($array, (is_array($value) ? $value : array($value)));
}

function keyToTitle($text) {
    return ucfirst(preg_replace("/[^A-Za-z0-9 ]/", ' ', $text));
}

function titleToKey($text) {
    return strtolower(str_replace(' ', '_', $text));
}

function strLimit($title = null, $length = 10) {
    return Str::limit($title, $length);
}

function getIpInfo() {
    $ipInfo = ClientInfo::ipInfo();
    return $ipInfo;
}

function osBrowser() {
    $osBrowser = ClientInfo::osBrowser();
    return $osBrowser;
}

function getTemplates() {
    // Disabled remote template fetch (license package removed)
    return null;
}

function activeTemplate($asset = false) {
    $template = null;

    try {
        $template = session()->get('template');
    } catch (\Throwable $e) {
        $template = null;
    }

    if (!$template && function_exists('gs')) {
        $template = gs('active_template');
    }

    $template = $template ?: 'frontend';
    $template = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $template);

    $templatesDir = resource_path('views/templates/' . $template);
    $directDir    = resource_path('views/' . $template);

    if ($asset) {
        if (is_dir($templatesDir)) {
            return public_path('assets/templates/' . $template . '/');
        }

        if (is_dir($directDir) && is_dir(public_path('assets/' . $template))) {
            return public_path('assets/' . $template . '/');
        }

        return public_path('assets/');
    }

    if (is_dir($templatesDir)) {
        return 'templates.' . $template . '.';
    }

    if (is_dir($directDir)) {
        return $template . '.';
    }

    return 'frontend.';
}

function getPageSections($arr = false) {
    $jsonUrl  = resource_path('views/') . str_replace('.', '/', activeTemplate()) . 'sections.json';
    if (!file_exists($jsonUrl)) {
        return $arr ? [] : (object) [];
    }

    $sections = json_decode(file_get_contents($jsonUrl));
    if ($arr) {
        $sections = json_decode(file_get_contents($jsonUrl), true);
        ksort($sections);
    }
    return $sections;
}

function normalizePublicAssetPath($path) {
    $value = trim((string) ($path ?? ''));
    if ($value === '') {
        return '';
    }

    $value = str_replace('\\', '/', $value);

    if (preg_match('/^https?:\/\//i', $value)) {
        return $value;
    }

    $value = ltrim($value, '/');

    if (stripos($value, 'public/') === 0) {
        $value = substr($value, strlen('public/'));
    }

    return ltrim($value, '/');
}

function publicStorageBaseUrl(): string {
    static $cached = null;

    if ($cached !== null) {
        return $cached;
    }

    $requestBase = '';

    try {
        if (!app()->runningInConsole()) {
            $request = request();
            if ($request) {
                $requestBase = rtrim((string) $request->getSchemeAndHttpHost(), '/');
            }
        }
    } catch (\Throwable $e) {
        $requestBase = '';
    }

    if ($requestBase === '') {
        $requestBase = rtrim((string) config('app.url', ''), '/');
    }

    $normalizePath = static function (string $path): string {
        $real = realpath($path);
        $value = $real !== false ? $real : $path;
        return rtrim(str_replace('\\', '/', $value), '/');
    };

    $documentRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
    if (is_string($documentRoot) && trim($documentRoot) !== '' && $requestBase !== '') {
        $documentRoot = $normalizePath($documentRoot);
        $basePath = $normalizePath(base_path());
        $publicPath = $normalizePath(public_path());

        if ($documentRoot === $basePath) {
            return $cached = $requestBase . '/public/storage';
        }

        if ($documentRoot === $publicPath) {
            return $cached = $requestBase . '/storage';
        }
    }

    $configured = trim((string) config('filesystems.disks.public.url', ''));
    if ($configured !== '') {
        if (preg_match('/^https?:\/\//i', $configured)) {
            return $cached = rtrim($configured, '/');
        }

        if ($requestBase !== '') {
            return $cached = $requestBase . '/' . trim($configured, '/');
        }
    }

    if ($requestBase !== '') {
        return $cached = $requestBase . '/storage';
    }

    return $cached = rtrim(asset('storage'), '/');
}

function publicStorageUrl($path): string {
    $value = trim((string) ($path ?? ''));
    if ($value === '') {
        return '';
    }

    if (preg_match('/^https?:\/\//i', $value)) {
        return $value;
    }

    $value = normalizePublicAssetPath($value);
    if ($value === '') {
        return '';
    }

    $lower = strtolower($value);

    if (str_starts_with($lower, 'assets/')) {
        return asset($value);
    }

    if (str_starts_with($lower, 'storage/')) {
        $value = substr($value, strlen('storage/'));
    }

    return rtrim(publicStorageBaseUrl(), '/') . '/' . ltrim($value, '/');
}

function getImage($image, $size = null) {
    $image = trim((string) ($image ?? ''));

    if ($image !== '' && preg_match('/^https?:\/\//i', $image)) {
        return $image;
    }

    $relativePath = normalizePublicAssetPath($image);
    if ($relativePath !== '' && is_file(public_path($relativePath))) {
        return asset($relativePath);
    }

    if ($size) {
        return route('placeholder.image', $size);
    }

    return asset('assets/images/default.png');
}

function siteFavicon() {
    $path = public_path('assets/images/logo_icon/favicon.png');
    if (file_exists($path)) {
        return versionedAsset('assets/images/logo_icon/favicon.png');
    }

    return versionedAsset('favicon.ico');
}

function siteLogo() {
    $path = public_path('assets/images/logo_icon/logo.png');
    if (file_exists($path)) {
        return versionedAsset('assets/images/logo_icon/logo.png');
    }

    $fallback = public_path('assets/images/logo_icon/logo1.png');
    if (file_exists($fallback)) {
        return versionedAsset('assets/images/logo_icon/logo1.png');
    }

    return versionedAsset('assets/images/default.png');
}

function versionedAsset(string $path, ?string $version = null): string {
    $url = asset(ltrim($path, '/'));
    $resolvedVersion = $version ?: date('Ymd');
    $joiner = str_contains($url, '?') ? '&' : '?';

    return $url . $joiner . 'v=' . $resolvedVersion;
}

function notify($user, $templateName, $shortCodes = null, $sendVia = null, $createLog = true, $pushImage = null) {
    $globalShortCodes = [
        'site_name'       => gs('site_name'),
        'site_currency'   => gs('cur_text'),
        'currency_symbol' => gs('cur_sym'),
    ];

    if (gettype($user) == 'array') {
        $user = (object) $user;
    }

    $shortCodes = array_merge($shortCodes ?? [], $globalShortCodes);

    $notify               = new Notify($sendVia);
    $notify->templateName = $templateName;
    $notify->shortCodes   = $shortCodes;
    $notify->user         = $user;
    $notify->createLog    = $createLog;
    $notify->pushImage    = $pushImage;
    $notify->userColumn   = isset($user->id) ? $user->getForeignKey() : 'user_id';
    $notify->send();
}

function getPaginate($paginate = null) {
    if (!$paginate) {
        $paginate = gs('paginate_number');
    }
    return $paginate;
}

function paginateLinks($data, $view = null) {
    return $data->appends(request()->all())->links($view);
}

function menuActive($routeName, $type = null, $param = null) {
    if ($type == 3) {
        $class = 'side-menu--open';
    } else if ($type == 2) {
        $class = 'sidebar-submenu__open';
    } else {
        $class = 'active';
    }

    if (is_array($routeName)) {
        foreach ($routeName as $key => $value) {
            if (request()->routeIs($value)) {
                return $class;
            }
        }
    } else if (request()->routeIs($routeName)) {
        if ($param) {
            $routeParam = array_values(@request()->route()->parameters ?? []);
            if (strtolower(@$routeParam[0]) == strtolower($param)) {
                return $class;
            } else {
                return;
            }
        }
        return $class;
    }
}

function fileUploader($file, $location, $size = null, $old = null, $thumb = null, $filename = null) {
    $fileManager           = new FileManager($file);
    $fileManager->path     = $location;
    $fileManager->size     = $size;
    $fileManager->old      = $old;
    $fileManager->thumb    = $thumb;
    $fileManager->filename = $filename;
    $fileManager->upload();
    return $fileManager->filename;
}

function fileManager() {
    return new FileManager();
}

function getFilePath($key) {
    try {
        $info = fileManager()->$key();
        return $info->path ?? '';
    } catch (\Throwable $e) {
        return '';
    }
}

function getFileSize($key) {
    try {
        $info = fileManager()->$key();
        return $info->size ?? false;
    } catch (\Throwable $e) {
        return false;
    }
}

function getFileExt($key) {
    try {
        $info = fileManager()->$key();
        return $info->extensions ?? null;
    } catch (\Throwable $e) {
        return null;
    }
}

function diffForHumans($date) {
    $lang = session()->get('lang');
    if (!$lang) {
        $lang = getDefaultLang();
    }
    Carbon::setlocale($lang);
    return Carbon::parse($date)->diffForHumans();
}

function showDateTime($date, $format = 'Y-m-d h:i A') {
    if (!$date) {
        return '-';
    }
    $lang = session()->get('lang');
    if (!$lang) {
        $lang = getDefaultLang();
    }
    Carbon::setlocale($lang);
    return Carbon::parse($date)->translatedFormat($format);
}

function getDefaultLang() {
    return Language::where('is_default', Status::YES)->first()->code ?? 'en';
}

function getContent($dataKeys, $singleQuery = false, $limit = null, $orderById = false) {

    $templateName = activeTemplateName();
    if ($singleQuery) {
        $content = Frontend::where('tempname', $templateName)->where('data_keys', $dataKeys)->orderBy('id', 'desc')->first();
    } else {
        $article = Frontend::where('tempname', $templateName);
        $article->when($limit != null, function ($q) use ($limit) {
            return $q->limit($limit);
        });
        if ($orderById) {
            $content = $article->where('data_keys', $dataKeys)->orderBy('id')->get();
        } else {
            $content = $article->where('data_keys', $dataKeys)->orderBy('id', 'desc')->get();
        }
    }
    return $content;
}

function verifyG2fa($user, $code, $secret = null) {
    $authenticator = new GoogleAuthenticator();
    if (!$secret) {
        $secret = $user->tsc;
    }
    $oneCode  = $authenticator->getCode($secret);
    $userCode = $code;
    if ($oneCode == $userCode) {
        $user->tv = Status::YES;
        $user->save();
        return true;
    } else {
        return false;
    }
}

function urlPath($routeName, $routeParam = null) {
    if ($routeParam == null) {
        $url = route($routeName);
    } else {
        $url = route($routeName, $routeParam);
    }
    $basePath = route('home');
    $path     = str_replace($basePath, '', $url);
    return $path;
}

function showMobileNumber($number) {
    $length = strlen($number);
    return substr_replace($number, '***', 2, $length - 4);
}

function showEmailAddress($email) {
    $endPosition = strpos($email, '@') - 1;
    return substr_replace($email, '***', 1, $endPosition);
}

function getRealIP() {
    $ip = $_SERVER["REMOTE_ADDR"];
    //Deep detect ip
    if (filter_var(@$_SERVER['HTTP_FORWARDED'], FILTER_VALIDATE_IP)) {
        $ip = $_SERVER['HTTP_FORWARDED'];
    }
    if (filter_var(@$_SERVER['HTTP_FORWARDED_FOR'], FILTER_VALIDATE_IP)) {
        $ip = $_SERVER['HTTP_FORWARDED_FOR'];
    }
    if (filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP)) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    if (filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP)) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    }
    if (filter_var(@$_SERVER['HTTP_X_REAL_IP'], FILTER_VALIDATE_IP)) {
        $ip = $_SERVER['HTTP_X_REAL_IP'];
    }
    if (filter_var(@$_SERVER['HTTP_CF_CONNECTING_IP'], FILTER_VALIDATE_IP)) {
        $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
    }
    if ($ip == '::1') {
        $ip = '127.0.0.1';
    }

    return $ip;
}

function appendQuery($key, $value) {
    return request()->fullUrlWithQuery([$key => $value]);
}

function dateSort($a, $b) {
    return strtotime($a) - strtotime($b);
}

function dateSorting($arr) {
    usort($arr, "dateSort");
    return $arr;
}

function gs($key = null) {
    $cacheAvailable = false;
    $general = null;

    try {
        $cacheAvailable = Schema::hasTable('cache');
        $general = $cacheAvailable ? Cache::get('GeneralSetting') : null;

        if (!$general && Schema::hasTable('general_settings')) {
            $general = GeneralSetting::first();
            if ($cacheAvailable && $general) {
                Cache::put('GeneralSetting', $general);
            }
        }
    } catch (\Throwable $e) {
        // Durante install/deploy o banco pode não estar acessível ainda.
        // Nesses casos, retorna fallback seguro abaixo.
        $general = null;
    }

    if (!$general) {
        $general              = new GeneralSetting();
        $general->site_name   = config('app.name') ?: (systemDetails()['name'] ?? 'rei-central');
        $general->cur_text    = 'BRL';
        $general->cur_sym     = 'R$';
        $general->base_color  = '0d6efd';
        $general->active_template = 'frontend';
    }
    if ($key) {
        $value = $general->{$key} ?? null;

        // Many admin templates expect stdClass-style access (->prop) for JSON configs.
        // When Eloquent casts return arrays, convert them to objects recursively.
        if (is_array($value)) {
            $value = json_decode(json_encode($value));
        }

        // Provide safe defaults for commonly expected config blobs.
        if ($value === null) {
            if ($key === 'mail_config') {
                $value = (object) [
                    'name' => 'php',
                    'host' => null,
                    'port' => null,
                    'enc' => null,
                    'username' => null,
                    'password' => null,
                    'appkey' => null,
                    'public_key' => null,
                    'secret_key' => null,
                ];
            } elseif ($key === 'sms_config') {
                $value = json_decode(json_encode([
                    'name' => 'custom',
                    'clickatell' => ['api_key' => null],
                    'infobip' => ['username' => null, 'password' => null],
                    'message_bird' => ['api_key' => null],
                    'nexmo' => ['api_key' => null, 'api_secret' => null],
                    'sms_broadcast' => ['username' => null, 'password' => null],
                    'twilio' => ['account_sid' => null, 'auth_token' => null, 'from' => null],
                    'text_magic' => ['username' => null, 'apiv2_key' => null],
                    'custom' => [
                        'method' => 'get',
                        'url' => '',
                        'headers' => ['name' => [], 'value' => []],
                        'body' => ['name' => [], 'value' => []],
                    ],
                ]));
            } elseif ($key === 'firebase_config') {
                $value = (object) [
                    'apiKey' => null,
                    'authDomain' => null,
                    'projectId' => null,
                    'storageBucket' => null,
                    'messagingSenderId' => null,
                    'appId' => null,
                    'measurementId' => null,
                ];
            } elseif ($key === 'socialite_credentials') {
                $value = [];
            } elseif ($key === 'global_shortcodes') {
                $value = [];
            }
        }

        return $value;
    }

    return $general;
}
function isImage($string) {
    $allowedExtensions = array('jpg', 'jpeg', 'png', 'gif');
    $fileExtension     = pathinfo($string, PATHINFO_EXTENSION);
    if (in_array($fileExtension, $allowedExtensions)) {
        return true;
    } else {
        return false;
    }
}

function isHtml($string) {
    if (preg_match('/<.*?>/', $string)) {
        return true;
    } else {
        return false;
    }
}

function convertToReadableSize($size) {
    preg_match('/^(\d+)([KMG])$/', $size, $matches);
    $size = (int) $matches[1];
    $unit = $matches[2];

    if ($unit == 'G') {
        return $size . 'GB';
    }

    if ($unit == 'M') {
        return $size . 'MB';
    }

    if ($unit == 'K') {
        return $size . 'KB';
    }

    return $size . $unit;
}

function frontendImage($sectionName, $image, $size = null, $seo = false) {
    if ($seo) {
        return getImage('assets/images/frontend/' . $sectionName . '/seo/' . $image, $size);
    }
    return getImage('assets/images/frontend/' . $sectionName . '/' . $image, $size);
}

function carbonParse($time, $format = null) {
    return $format ? Carbon::parse($time)->format($format) : Carbon::parse($time);
}

function ordinal($number) {
    $ends = ['th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th'];
    if ((($number % 100) >= 11) && (($number % 100) <= 13)) {
        return $number . 'th';
    } else {
        return $number . $ends[$number % 10];
    }
}

function createUniqueSlug($name, $model, $id = 0) {
    $slug = slug($name);

    $originalSlug = $slug;

    $i = 1;

    $query = $model::where('slug', $slug);

    if ($id) {
        $query->where('id', '!=', $id);
    }

    while ($query->exists()) {
        $slug  = $originalSlug . '-' . $i++;
        $query = $model::where('slug', $slug);
    }

    return $slug;
}

function generateRandomColor() {
    $red   = rand(0, 250);
    $green = rand(0, 250);
    $blue  = rand(0, 255);
    return [$red, $green, $blue];
}

function buildResponse($remark, $status, $notify, $data = null) {
    $response = [
        'remark' => $remark,
        'status' => $status,
    ];
    $message = [];
    if ($notify instanceof \Illuminate\Support\MessageBag) {
        $message = collect($notify)->map(function ($item) {
            return $item[0];
        })->values()->toArray();
    } else {
        $message = [$status => collect($notify)->map(function ($item) {
            if (is_string($item)) {
                return $item;
            }
            if (count($item) > 1) {
                return $item[1];
            }
            return $item[0];
        })->toArray()];
    }
    $response['message'] = $message;
    if ($data) {
        $response['data'] = $data;
    }
    return response()->json($response);
}

function responseSuccess($remark, $notify, $data = null) {
    return buildResponse($remark, 'success', $notify, $data);
}
function responseError($remark, $notify, $data = null) {
    return buildResponse($remark, 'error', $notify, $data);
}

function verificationCode($length) {
    if ($length == 0) return 0;
    $min = pow(10, $length - 1);
    $max = pow(10, $length) - 1;
    return random_int($min, $max);
}
