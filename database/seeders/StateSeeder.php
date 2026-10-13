<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\State;

class StateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $estados = [
            ['name' => 'Aguascalientes', 'abbreviation' => 'AGS'],
            ['name' => 'Baja California', 'abbreviation' => 'BC'],
            ['name' => 'Baja California Sur', 'abbreviation' => 'BCS'],
            ['name' => 'Campeche', 'abbreviation' => 'CAM'],
            ['name' => 'Chiapas', 'abbreviation' => 'CHIS'],
            ['name' => 'Chihuahua', 'abbreviation' => 'CHIH'],
            ['name' => 'Ciudad de México', 'abbreviation' => 'CDMX'],
            ['name' => 'Coahuila', 'abbreviation' => 'COAH'],
            ['name' => 'Colima', 'abbreviation' => 'COL'],
            ['name' => 'Durango', 'abbreviation' => 'DGO'],
            ['name' => 'Guanajuato', 'abbreviation' => 'GTO'],
            ['name' => 'Guerrero', 'abbreviation' => 'GRO'],
            ['name' => 'Hidalgo', 'abbreviation' => 'HGO'],
            ['name' => 'Jalisco', 'abbreviation' => 'JAL'],
            ['name' => 'México', 'abbreviation' => 'MEX'],
            ['name' => 'Michoacán', 'abbreviation' => 'MICH'],
            ['name' => 'Morelos', 'abbreviation' => 'MOR'],
            ['name' => 'Nayarit', 'abbreviation' => 'NAY'],
            ['name' => 'Nuevo León', 'abbreviation' => 'NL'],
            ['name' => 'Oaxaca', 'abbreviation' => 'OAX'],
            ['name' => 'Puebla', 'abbreviation' => 'PUE'],
            ['name' => 'Querétaro', 'abbreviation' => 'QRO'],
            ['name' => 'Quintana Roo', 'abbreviation' => 'QROO'],
            ['name' => 'San Luis Potosí', 'abbreviation' => 'SLP'],
            ['name' => 'Sinaloa', 'abbreviation' => 'SIN'],
            ['name' => 'Sonora', 'abbreviation' => 'SON'],
            ['name' => 'Tabasco', 'abbreviation' => 'TAB'],
            ['name' => 'Tamaulipas', 'abbreviation' => 'TAM'],
            ['name' => 'Tlaxcala', 'abbreviation' => 'TLAX'],
            ['name' => 'Veracruz', 'abbreviation' => 'VER'],
            ['name' => 'Yucatán', 'abbreviation' => 'YUC'],
            ['name' => 'Zacatecas', 'abbreviation' => 'ZAC'],
        ];

        foreach ($estados as $estado) {
            State::firstOrCreate(['name' => $estado['name']], $estado);
        }
    }
}
