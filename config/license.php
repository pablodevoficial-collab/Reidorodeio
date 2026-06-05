<?php

return [
    // Ative quando quiser exigir a chave em produção
    'required' => env('APP_LICENSE_REQUIRED', false),

    // Opcional: pode ser definido no .env; se não houver, usaremos o arquivo em storage
    'key' => env('APP_LICENSE_KEY'),
];
