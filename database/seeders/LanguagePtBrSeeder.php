<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Constants\Status;

class LanguagePtBrSeeder extends Seeder
{
    public function run(): void
    {
        if (!DB::getSchemaBuilder()->hasTable('languages')) {
            return; // table not present; skip
        }

        // Upsert pt_BR language with Brazil flag stored in public/assets/images/language
    $exists = DB::table('languages')->where('code', 'pt_br')->first();
        $data = [
            'name'       => 'Português (Brasil)',
            'code'       => 'pt_br',
            'image'      => 'brasil.jpg',
        ];

        if ($exists) {
            DB::table('languages')->where('id', $exists->id)->update($data);
        } else {
            $data['is_default'] = 0;
            DB::table('languages')->insert($data);
        }

        // Definir pt_br como idioma padrão e unset dos demais
        DB::table('languages')->update(['is_default' => 0]);
        DB::table('languages')->where('code', 'pt_br')->update(['is_default' => 1]);
    }
}
