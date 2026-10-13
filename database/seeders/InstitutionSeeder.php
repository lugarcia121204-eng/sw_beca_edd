<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Institution;
use App\Models\State;

class InstitutionSeeder extends Seeder
{
    public function run(): void
    {
        $institutions = [
            // Aguascalientes
            ['name' => 'Instituto Tecnológico de Aguascalientes', 'state' => 'Aguascalientes'],
            ['name' => 'Instituto Tecnológico de Pabellón de Arteaga', 'state' => 'Aguascalientes'],
            ['name' => 'Instituto Tecnológico de la Construcción, Campus Aguascalientes', 'state' => 'Aguascalientes'],
            ['name' => 'Instituto Tecnológico de El Llano de Aguascalientes', 'state' => 'Aguascalientes'],

            // Baja California
            ['name' => 'Instituto Tecnológico de Ensenada', 'state' => 'Baja California'],
            ['name' => 'Instituto Tecnológico de la Construcción, Campus Baja California', 'state' => 'Baja California'],
            ['name' => 'Instituto Tecnológico de Mexicali', 'state' => 'Baja California'],
            ['name' => 'Instituto Tecnológico de Tijuana', 'state' => 'Baja California'],
            ['name' => 'Instituto Tecnológico de la Construcción, Campus Tijuana', 'state' => 'Baja California'],

            // Baja California Sur
            ['name' => 'Instituto Tecnológico de Estudios Superiores de Los Cabos', 'state' => 'Baja California Sur'],
            ['name' => 'Instituto Tecnológico Superior de Cd. Constitución', 'state' => 'Baja California Sur'],
            ['name' => 'Instituto Tecnológico Superior de Mulegé', 'state' => 'Baja California Sur'],
            ['name' => 'Instituto Tecnológico de La Paz', 'state' => 'Baja California Sur'],

            // Campeche
            ['name' => 'Instituto Tecnológico Superior de Calkiní', 'state' => 'Campeche'],
            ['name' => 'Instituto Tecnológico Superior de Champotón', 'state' => 'Campeche'],
            ['name' => 'Instituto Tecnológico Superior de Escárcega', 'state' => 'Campeche'],
            ['name' => 'Instituto Tecnológico Superior de Hopelchén', 'state' => 'Campeche'],
            ['name' => 'Instituto Tecnológico de la Construcción, Campus Campeche', 'state' => 'Campeche'],
            ['name' => 'Instituto Tecnológico y de Estudios Superiores René Descartes, Plantel Campeche', 'state' => 'Campeche'],
            ['name' => 'Instituto Tecnológico de Chiná', 'state' => 'Campeche'],
            ['name' => 'Instituto Tecnológico de Lerma', 'state' => 'Campeche'],
            ['name' => 'Instituto Tecnológico de Campeche', 'state' => 'Campeche'],
            ['name' => 'Instituto Tecnológico de la Construcción, Campus Cd. del Carmen', 'state' => 'Campeche'],

            // Chiapas
            ['name' => 'Instituto Tecnológico Superior de Cintalapa', 'state' => 'Chiapas'],
            ['name' => 'Instituto Tecnológico de Comitán', 'state' => 'Chiapas'],
            ['name' => 'Instituto Tecnológico de Frontera Comalapa', 'state' => 'Chiapas'],
            ['name' => 'Instituto Tecnológico y de Estudios Superiores de Monterrey, Campus Chiapas', 'state' => 'Chiapas'],
            ['name' => 'Instituto Tecnológico de Tapachula', 'state' => 'Chiapas'],
            ['name' => 'Instituto Tecnológico de la Construcción, campus Chiapas', 'state' => 'Chiapas'],
            ['name' => 'Instituto Tecnológico de Tuxtla Gutiérrez', 'state' => 'Chiapas'],

            // Chihuahua
            ['name' => 'Instituto Tecnológico de la Construcción, A.C., Campus Chihuahua', 'state' => 'Chihuahua'],
            ['name' => 'Instituto Tecnológico Superior de Nuevo Casas Grandes', 'state' => 'Chihuahua'],
            ['name' => 'Instituto Tecnológico y de Estudios Superiores de Monterrey, Campus Chihuahua', 'state' => 'Chihuahua'],
            ['name' => 'Instituto Tecnológico de Chihuahua', 'state' => 'Chihuahua'],
            ['name' => 'Instituto Tecnológico de Chihuahua II', 'state' => 'Chihuahua'],
            ['name' => 'Instituto Tecnológico de Cd. Cuauhtémoc', 'state' => 'Chihuahua'],
            ['name' => 'Instituto Tecnológico de Cd. Jiménez', 'state' => 'Chihuahua'],
            ['name' => 'Instituto Tecnológico y de Estudios Superiores de Monterrey, Campus Cd. Juárez', 'state' => 'Chihuahua'],
            ['name' => 'Instituto Tecnológico de la Construcción, A.C., Campus Juárez', 'state' => 'Chihuahua'],
            ['name' => 'Instituto Tecnológico de Cd. Juárez', 'state' => 'Chihuahua'],
            ['name' => 'Instituto Tecnológico de Delicias', 'state' => 'Chihuahua'],
            ['name' => 'Instituto Tecnológico de Parral', 'state' => 'Chihuahua'],

            // Ciudad de México
            ['name' => 'Instituto Tecnológico de Álvaro Obregón', 'state' => 'Ciudad de México'],
            ['name' => 'Instituto Tecnológico de Gustavo A. Madero', 'state' => 'Ciudad de México'],
            ['name' => 'Instituto Tecnológico de Gustavo A. Madero II', 'state' => 'Ciudad de México'],
            ['name' => 'Instituto Tecnológico de Iztapalapa', 'state' => 'Ciudad de México'],
            ['name' => 'Instituto Tecnológico de Iztapalapa II', 'state' => 'Ciudad de México'],
            ['name' => 'Instituto Tecnológico Roosevelt', 'state' => 'Ciudad de México'],
            ['name' => 'Instituto Tecnológico de Iztapalapa III', 'state' => 'Ciudad de México'],
            ['name' => 'Instituto Tecnológico de Milpa Alta', 'state' => 'Ciudad de México'],
            ['name' => 'Instituto Tecnológico de Milpa Alta II', 'state' => 'Ciudad de México'],
            ['name' => 'Instituto Tecnológico de Tláhuac', 'state' => 'Ciudad de México'],
            ['name' => 'Instituto Tecnológico de Tláhuac II', 'state' => 'Ciudad de México'],
            ['name' => 'Instituto Tecnológico de Tláhuac III', 'state' => 'Ciudad de México'],
            ['name' => 'Instituto Tecnológico de Tlalpan', 'state' => 'Ciudad de México'],
            ['name' => 'Instituto Tecnológico de Xochimilco', 'state' => 'Ciudad de México'],
            ['name' => 'Instituto Tecnológico Autónomo de México (ITAM) Campus Santa Teresa', 'state' => 'Ciudad de México'],
            ['name' => 'Instituto Tecnológico de la Construcción', 'state' => 'Ciudad de México'],
            ['name' => 'Instituto Tecnológico y de Estudios Superiores de Monterrey, Campus Santa Fé', 'state' => 'Ciudad de México'],
            ['name' => 'Instituto Tecnológico Autónomo de México (ITAM)', 'state' => 'Ciudad de México'],
            ['name' => 'Instituto Tecnológico y de Estudios Superiores de Monterrey', 'state' => 'Ciudad de México'],
            ['name' => 'Instituto Tecnológico de Teléfonos de México', 'state' => 'Ciudad de México'],

            // Coahuila
            ['name' => 'Instituto Tecnológico Superior de Cd. Acuña', 'state' => 'Coahuila'],
            ['name' => 'Instituto Tecnológico Superior de Monclova', 'state' => 'Coahuila'],
            ['name' => 'Instituto Tecnológico Superior de Múzquiz', 'state' => 'Coahuila'],
            ['name' => 'Instituto Tecnológico Superior de San Pedro de las Colonias', 'state' => 'Coahuila'],
            ['name' => 'Instituto Tecnológico de Torreón', 'state' => 'Coahuila'],
            ['name' => 'Instituto Tecnológico Agropecuario Nº 10 de Torreón', 'state' => 'Coahuila'],
            ['name' => 'Instituto Tecnológico de La Laguna', 'state' => 'Coahuila'],
            ['name' => 'Instituto Tecnológico y de Estudios Superiores de Monterrey, Campus Laguna', 'state' => 'Coahuila'],
            ['name' => 'Instituto Tecnológico de Piedras Negras', 'state' => 'Coahuila'],
            ['name' => 'Instituto Tecnológico de Saltillo', 'state' => 'Coahuila'],
            ['name' => 'Instituto Tecnológico Superior de Ramos Arizpe', 'state' => 'Coahuila'],
            ['name' => 'Instituto Tecnológico y de Estudios Superiores de Monterrey, Campus Saltillo', 'state' => 'Coahuila'],

            // Colima
            ['name' => 'Instituto Tecnológico Superior de Tamazula', 'state' => 'Colima'],
            ['name' => 'Instituto Tecnológico de Colima', 'state' => 'Colima'],

            // Durango
            ['name' => 'Instituto Tecnológico Superior de La Región de los Llanos', 'state' => 'Durango'],
            ['name' => 'Instituto Tecnológico Superior de Lerdo', 'state' => 'Durango'],
            ['name' => 'Instituto Tecnológico Superior de Santa María del Oro', 'state' => 'Durango'],
            ['name' => 'Instituto Tecnológico Superior de Santiago Papasquiaro', 'state' => 'Durango'],
            ['name' => 'Instituto Tecnológico de El Salto', 'state' => 'Durango'],
            ['name' => 'Instituto Tecnológico de Durango', 'state' => 'Durango'],
            ['name' => 'Instituto Tecnológico del Valle del Guadiana', 'state' => 'Durango'],

            // México
            ['name' => 'Tecnológico de Estudios Superiores de Chalco', 'state' => 'México'],
            ['name' => 'Tecnológico de Estudios Superiores de Chicoloapan', 'state' => 'México'],
            ['name' => 'Tecnológico de Estudios Superiores de Chimalhuacán', 'state' => 'México'],
            ['name' => 'Tecnológico de Estudios Superiores de Coacalco', 'state' => 'México'],
            ['name' => 'Tecnológico de Estudios Superiores de Cuautitlán Izcalli', 'state' => 'México'],
            ['name' => 'Tecnológico de Estudios Superiores de Ecatepec', 'state' => 'México'],
            ['name' => 'Tecnológico de Estudios Superiores de Huixquilucan', 'state' => 'México'],
            ['name' => 'Tecnológico de Estudios Superiores de Ixtapaluca', 'state' => 'México'],
            ['name' => 'Tecnológico de Estudios Superiores de Jilotepec', 'state' => 'México'],
            ['name' => 'Tecnológico de Estudios Superiores de Jocotitlán', 'state' => 'México'],
            ['name' => 'Tecnológico de Estudios Superiores de Oriente del Estado de México', 'state' => 'México'],
            ['name' => 'Tecnológico de Estudios Superiores de San Felipe del Progreso', 'state' => 'México'],
            ['name' => 'Tecnológico de Estudios Superiores de Tianguistenco', 'state' => 'México'],
            ['name' => 'Tecnológico de Estudios Superiores de Valle de Bravo', 'state' => 'México'],
            ['name' => 'Tecnológico de Estudios Superiores de Villa Guerrero', 'state' => 'México'],
            ['name' => 'Instituto Tecnológico de Atlacomulco', 'state' => 'México'],
            ['name' => 'Instituto Tecnológico de Tlalnepantla', 'state' => 'México'],
            ['name' => 'Instituto Tecnológico de Toluca', 'state' => 'México'],

            // Guanajuato
            ['name' => 'Instituto Tecnológico Superior de Abasolo', 'state' => 'Guanajuato'],
            ['name' => 'Instituto Tecnológico Superior de Guanajuato', 'state' => 'Guanajuato'],
            ['name' => 'Instituto Tecnológico Nacional de Celaya, Campus I', 'state' => 'Guanajuato'],
            ['name' => 'Instituto Tecnológico Superior de Irapuato', 'state' => 'Guanajuato'],
            ['name' => 'Instituto Tecnológico Superior de Purísima Del Rincón', 'state' => 'Guanajuato'],
            ['name' => 'Instituto Tecnológico Superior de Salvatierra', 'state' => 'Guanajuato'],
            ['name' => 'Instituto Tecnológico Superior de Sur de Guanajuato', 'state' => 'Guanajuato'],
            ['name' => 'Instituto Tecnológico de Roque', 'state' => 'Guanajuato'],
            ['name' => 'Instituto Tecnológico de Celaya', 'state' => 'Guanajuato'],
            ['name' => 'Instituto Tecnológico de León', 'state' => 'Guanajuato'],
            ['name' => 'Instituto Tecnológico de Diseño de Modas', 'state' => 'Guanajuato'],

            // Guerrero
            ['name' => 'Instituto Tecnológico Superior de La Costa Chica', 'state' => 'Guerrero'],
            ['name' => 'Instituto Tecnológico Superior de La Montaña', 'state' => 'Guerrero'],
            ['name' => 'Instituto Tecnológico de Acapulco', 'state' => 'Guerrero'],
            ['name' => 'Instituto Tecnológico de Chilpancingo', 'state' => 'Guerrero'],
            ['name' => 'Instituto Tecnológico de Iguala', 'state' => 'Guerrero'],
            ['name' => 'Instituto Tecnológico de San Marcos', 'state' => 'Guerrero'],
            ['name' => 'Instituto Tecnológico de Ciudad Altamirano', 'state' => 'Guerrero'],
            ['name' => 'Instituto Tecnológico de la Costa Grande', 'state' => 'Guerrero'],

            // Hidalgo
            ['name' => 'Instituto Tecnológico Superior del Occidente del Estado de Hidalgo', 'state' => 'Hidalgo'],
            ['name' => 'Instituto Tecnológico Superior del Oriente del Estado de Hidalgo', 'state' => 'Hidalgo'],
            ['name' => 'Instituto Tecnológico Superior de Huichapan', 'state' => 'Hidalgo'],
            ['name' => 'Instituto Tecnológico de Huejutla', 'state' => 'Hidalgo'],
            ['name' => 'Instituto Tecnológico de Atitalaquia', 'state' => 'Hidalgo'],
            ['name' => 'Instituto Tecnológico de Pachuca', 'state' => 'Hidalgo'],
            ['name' => 'Instituto Tecnológico de Tula de Allende', 'state' => 'Hidalgo'],
            ['name' => 'Instituto Tecnológico Latinoamericano, Campus Central', 'state' => 'Hidalgo'],
            ['name' => 'Instituto Tecnológico de la Construcción (ITC), Campus Hidalgo', 'state' => 'Hidalgo'],
            ['name' => 'Instituto Tecnológico Latinoamericano, Campus Tula', 'state' => 'Hidalgo'],

            // Jalisco
            ['name' => 'Instituto Tecnológico Superior de Chapala', 'state' => 'Jalisco'],
            ['name' => 'Instituto Tecnológico Superior de Cocula', 'state' => 'Jalisco'],
            ['name' => 'Instituto Tecnológico Superior de Mascota', 'state' => 'Jalisco'],
            ['name' => 'Instituto Tecnológico Superior José Mario Molina Pasquel y Henríquez', 'state' => 'Jalisco'],
            ['name' => 'Instituto Tecnológico Superior de Arandas', 'state' => 'Jalisco'],
            ['name' => 'Instituto Tecnológico de Ciudad Guzmán', 'state' => 'Jalisco'],
            ['name' => 'Instituto Tecnológico Superior de la Huerta', 'state' => 'Jalisco'],
            ['name' => 'Instituto Tecnológico Superior de El Grullo', 'state' => 'Jalisco'],
            ['name' => 'Instituto Tecnológico de Tlajomulco', 'state' => 'Jalisco'],
            ['name' => 'Instituto Tecnológico y de Estudios Superiores de Occidente', 'state' => 'Jalisco'],
            ['name' => 'Instituto Tecnológico de Ocotlán', 'state' => 'Jalisco'],
            ['name' => 'Instituto Tecnológico Superior de Zapotlanejo', 'state' => 'Jalisco'],
            ['name' => 'Instituto Tecnológico Superior de Zapopan', 'state' => 'Jalisco'],
            ['name' => 'Instituto Tecnológico José Mario Molina Pasquel y Enríquez - Lagos de Moreno', 'state' => 'Jalisco'],
            ['name' => 'Instituto Tecnológico Superior de Puerto Vallarta', 'state' => 'Jalisco'],

            // Michoacán
            ['name' => 'Instituto Tecnológico de Estudios Superiores de Zamora', 'state' => 'Michoacán'],
            ['name' => 'Instituto Tecnológico Superior de Apatzingán', 'state' => 'Michoacán'],
            ['name' => 'Instituto Tecnológico Superior de Cd. Hidalgo', 'state' => 'Michoacán'],
            ['name' => 'Instituto Tecnológico Superior de Coalcomán', 'state' => 'Michoacán'],
            ['name' => 'Instituto Tecnológico Superior de Huetamo', 'state' => 'Michoacán'],
            ['name' => 'Instituto Tecnológico Superior de Los Reyes', 'state' => 'Michoacán'],
            ['name' => 'Instituto Tecnológico Superior de Pátzcuaro', 'state' => 'Michoacán'],
            ['name' => "Instituto Tecnológico Superior de P'urhépecha", 'state' => 'Michoacán'],
            ['name' => 'Instituto Tecnológico Superior de Puruándiro', 'state' => 'Michoacán'],
            ['name' => 'Instituto Tecnológico Superior de Tacámbaro', 'state' => 'Michoacán'],
            ['name' => 'Instituto Tecnológico Superior de Uruapan', 'state' => 'Michoacán'],
            ['name' => 'Instituto Tecnológico de Jiquilpan', 'state' => 'Michoacán'],
            ['name' => 'Instituto Tecnológico de La Piedad', 'state' => 'Michoacán'],
            ['name' => 'Instituto Tecnológico de Lázaro Cárdenas', 'state' => 'Michoacán'],
            ['name' => 'Instituto Tecnológico de Morelia', 'state' => 'Michoacán'],
            ['name' => 'Instituto Tecnológico y de Estudios Superiores de Monterrey, Campus Morelia', 'state' => 'Michoacán'],
            ['name' => 'Instituto Tecnológico de Zitácuaro', 'state' => 'Michoacán'],

            // Morelos
            ['name' => 'Centro Nacional de Investigación y Desarrollo Tecnológico', 'state' => 'Morelos'],
            ['name' => 'Instituto Tecnológico de Cuautla', 'state' => 'Morelos'],
            ['name' => 'Instituto Tecnológico de Zacatepec', 'state' => 'Morelos'],
            ['name' => 'Instituto Tecnológico del Valle de Oaxaca', 'state' => 'Morelos'],
            ['name' => 'Instituto Tecnológico de la Construcción (ITC), Campus Cuernavaca', 'state' => 'Morelos'],

            // Nayarit
            ['name' => 'Instituto Tecnológico de Bahía de Banderas', 'state' => 'Nayarit'],
            ['name' => 'Instituto Tecnológico de Estudios Superiores de Nayarit', 'state' => 'Nayarit'],
            ['name' => 'Instituto Tecnológico del Norte de Nayarit', 'state' => 'Nayarit'],
            ['name' => 'Instituto Tecnológico del Sur de Nayarit', 'state' => 'Nayarit'],
            ['name' => 'Instituto Tecnológico de Tepic', 'state' => 'Nayarit'],
            ['name' => 'Instituto Tecnológico de la Construcción (ITC), Plantel Tepic', 'state' => 'Nayarit'],

            // Nuevo León
            ['name' => 'Instituto Tecnológico Superior de Montemorelos', 'state' => 'Nuevo León'],
            ['name' => 'Instituto Tecnológico de Linares', 'state' => 'Nuevo León'],
            ['name' => 'Instituto Tecnológico de Nuevo León', 'state' => 'Nuevo León'],

            // Oaxaca
            ['name' => 'Instituto Tecnológico Superior de San Miguel el Grande', 'state' => 'Oaxaca'],
            ['name' => 'Instituto Tecnológico Superior de Teposcolula', 'state' => 'Oaxaca'],
            ['name' => 'Instituto Tecnológico Superior de Huatulco', 'state' => 'Oaxaca'],
            ['name' => 'Instituto Tecnológico del Valle de Etla', 'state' => 'Oaxaca'],
            ['name' => 'Instituto Tecnológico del Valle de Oaxaca', 'state' => 'Oaxaca'],
            ['name' => 'Instituto Tecnológico del Istmo', 'state' => 'Oaxaca'],
            ['name' => 'Instituto Tecnológico de la Construcción (ITC), Plantel Oaxaca', 'state' => 'Oaxaca'],
            ['name' => 'Instituto Tecnológico de La Cuenca del Papaloapan', 'state' => 'Oaxaca'],
            ['name' => 'Instituto Tecnológico de Salina Cruz', 'state' => 'Oaxaca'],
            ['name' => 'Instituto Tecnológico de Comitancillo', 'state' => 'Oaxaca'],
            ['name' => 'Instituto Tecnológico de Oaxaca', 'state' => 'Oaxaca'],
            ['name' => 'Instituto Tecnológico de Pinotepa', 'state' => 'Oaxaca'],
            ['name' => 'Instituto Tecnológico de Pochutla', 'state' => 'Oaxaca'],
            ['name' => 'Instituto Tecnológico de Tlaxiaco', 'state' => 'Oaxaca'],
            ['name' => 'Instituto Tecnológico Agropecuario no. 3 de Tuxtepec', 'state' => 'Oaxaca'],
            ['name' => 'Instituto Tecnológico de Tuxtepec', 'state' => 'Oaxaca'],

            // Puebla
            ['name' => 'Instituto Tecnológico Superior de Acatlán de Osorio', 'state' => 'Puebla'],
            ['name' => 'Instituto Tecnológico Superior de Atlixco', 'state' => 'Puebla'],
            ['name' => 'Instituto Tecnológico Superior de la Sierra Norte de Puebla', 'state' => 'Puebla'],
            ['name' => 'Instituto Tecnológico Superior de La Sierra Negra de Ajalpan', 'state' => 'Puebla'],
            ['name' => 'Instituto Tecnológico Superior de Cd. Serdán', 'state' => 'Puebla'],
            ['name' => 'Instituto Tecnológico Superior de Huauchinango', 'state' => 'Puebla'],
            ['name' => 'Instituto Tecnológico Superior de Libres', 'state' => 'Puebla'],
            ['name' => 'Instituto Tecnológico Superior de San Martín Texmelucan', 'state' => 'Puebla'],
            ['name' => 'Instituto Tecnológico Superior de Tepeaca', 'state' => 'Puebla'],
            ['name' => 'Instituto Tecnológico Superior de Tepexi de Rodríguez', 'state' => 'Puebla'],
            ['name' => 'Instituto Tecnológico Superior de Teziutlán', 'state' => 'Puebla'],
            ['name' => 'Instituto Tecnológico Superior de Tlatlauquitepec', 'state' => 'Puebla'],
            ['name' => 'Instituto Tecnológico Superior de Venustiano Carranza', 'state' => 'Puebla'],
            ['name' => 'Instituto Tecnológico Superior de Zacapoaxtla', 'state' => 'Puebla'],
            ['name' => 'Instituto Tecnológico Superior de Zacatlán', 'state' => 'Puebla'],
            ['name' => 'Instituto Tecnológico de Tecomatlán', 'state' => 'Puebla'],
            ['name' => 'Instituto Tecnológico de Izúcar de Matamoros', 'state' => 'Puebla'],
            ['name' => 'Instituto Tecnológico de Puebla', 'state' => 'Puebla'],
            ['name' => 'Instituto Tecnológico de Tehuacán', 'state' => 'Puebla'],

            // Querétaro
            ['name' => 'Centro Interdisciplinario de Investigación y Docencia en Educación Técnica', 'state' => 'Querétaro'],
            ['name' => 'Instituto Tecnológico de la Construcción, Campus Querétaro', 'state' => 'Querétaro'],
            ['name' => 'Instituto Tecnológico de la Construccion, Plantel Querétaro', 'state' => 'Querétaro'],
            ['name' => 'Instituto Tecnológico de Querétaro', 'state' => 'Querétaro'],
            ['name' => 'Instituto Tecnológico de San Juan del Río', 'state' => 'Querétaro'],

            // Quintana Roo
            ['name' => 'Instituto Tecnológico Superior de Tulum', 'state' => 'Quintana Roo'],
            ['name' => 'Instituto Tecnológico Superior de Felipe Carrillo Puerto', 'state' => 'Quintana Roo'],
            ['name' => 'Instituto Tecnológico de La Zona Maya', 'state' => 'Quintana Roo'],
            ['name' => 'Instituto Tecnológico de Cancún', 'state' => 'Quintana Roo'],
            ['name' => 'Instituto Tecnológico de Chetumal', 'state' => 'Quintana Roo'],

            // San Luis Potosí
            ['name' => 'Instituto Tecnológico Superior de Ébano', 'state' => 'San Luis Potosí'],
            ['name' => 'Instituto Tecnológico Superior de Rioverde', 'state' => 'San Luis Potosí'],
            ['name' => 'Instituto Tecnológico Superior de Tamazunchale', 'state' => 'San Luis Potosí'],
            ['name' => 'Instituto Tecnológico de Matehuala', 'state' => 'San Luis Potosí'],
            ['name' => 'Instituto Tecnológico y de Estudios Superiores de Monterrey (ITESM). Campus San Luis Potosí', 'state' => 'San Luis Potosí'],
            ['name' => 'Instituto Tecnológico Superior de San Luis Potosí Capital', 'state' => 'San Luis Potosí'],
            ['name' => 'Instituto Tecnológico de San Luis Potosí', 'state' => 'San Luis Potosí'],
            ['name' => 'Instituto Tecnológico de Ciudad Valles', 'state' => 'San Luis Potosí'],

            // Sinaloa
            ['name' => 'Instituto Tecnológico Superior de Guasave', 'state' => 'Sinaloa'],
            ['name' => 'Instituto Tecnológico de Mazatlán', 'state' => 'Sinaloa'],
            ['name' => 'Instituto Tecnológico de Culiacán', 'state' => 'Sinaloa'],
            ['name' => 'Instituto Tecnológico Superior de El Dorado', 'state' => 'Sinaloa'],
            ['name' => 'Instituto Tecnológico Superior de los Mochis', 'state' => 'Sinaloa'],
            ['name' => 'Instituto Tecnológico Superior de Sinaloa, Campus Centro', 'state' => 'Sinaloa'],
            ['name' => 'Instituto Tecnológico de Los Mochis', 'state' => 'Sinaloa'],
            ['name' => 'Instituto Tecnológico de la Construcción (ITC), Delegación Sinaloa Centro', 'state' => 'Sinaloa'],
            ['name' => 'Instituto Tecnológico de Sinaloa de Leyva', 'state' => 'Sinaloa'],
            ['name' => 'Instituto Tecnológico de la Construcción (ITC), Delegación Sinaloa Norte', 'state' => 'Sinaloa'],

            // Sonora
            ['name' => 'Instituto Tecnológico Superior de Cajeme', 'state' => 'Sonora'],
            ['name' => 'Instituto Tecnológico de Sonora (ITSON), Campus Obregón Centro', 'state' => 'Sonora'],
            ['name' => 'Instituto Tecnológico de Sonora (ITSON), Campus Obregón Náinari', 'state' => 'Sonora'],
            ['name' => 'Instituto Tecnológico de Sonora (ITSON), Campus Empalme', 'state' => 'Sonora'],
            ['name' => 'Instituto Tecnológico Superior de Cananea', 'state' => 'Sonora'],
            ['name' => 'Instituto Tecnológico del Valle del Yaqui', 'state' => 'Sonora'],
            ['name' => 'Instituto Tecnológico Superior de Puerto Peñasco', 'state' => 'Sonora'],
            ['name' => 'Instituto Tecnológico de Guaymas', 'state' => 'Sonora'],
            ['name' => 'Instituto Tecnológico de Sonora (ITSON), Campus Guaymas', 'state' => 'Sonora'],
            ['name' => 'Instituto Tecnológico y de Estudios Superiores de Monterrey (ITESM), Campus Sonora Norte', 'state' => 'Sonora'],
            ['name' => 'Instituto Tecnológico de la Construcción (ITC), Delegación Sonora', 'state' => 'Sonora'],
            ['name' => 'Instituto Tecnológico de Sonora (ITSON), Campus Navojoa Sur', 'state' => 'Sonora'],
            ['name' => 'Instituto Tecnológico de Sonora (ITSON), Campus Navojoa Centro', 'state' => 'Sonora'],
            ['name' => 'Instituto Tecnológico de Agua Prieta', 'state' => 'Sonora'],
            ['name' => 'Instituto Tecnológico de Hermosillo', 'state' => 'Sonora'],
            ['name' => 'Instituto Tecnológico de Huatabampo', 'state' => 'Sonora'],
            ['name' => 'Instituto Tecnológico de Nogales', 'state' => 'Sonora'],
            ['name' => 'Instituto Tecnológico de San Luis Río Colorado', 'state' => 'Sonora'],

            // Tabasco
            ['name' => 'Instituto Tecnológico Superior de Centla', 'state' => 'Tabasco'],
            ['name' => 'Instituto Tecnológico Superior de Comalcalco', 'state' => 'Tabasco'],
            ['name' => 'Instituto Tecnológico Superior de los Ríos', 'state' => 'Tabasco'],
            ['name' => 'Instituto Tecnológico Superior de Macuspana', 'state' => 'Tabasco'],
            ['name' => 'Instituto Tecnológico Superior de Villa La Venta', 'state' => 'Tabasco'],
            ['name' => 'Instituto Tecnológico de La Zona Olmeca', 'state' => 'Tabasco'],
            ['name' => 'Instituto Tecnológico de Huimanguillo', 'state' => 'Tabasco'],
            ['name' => 'Instituto Tecnológico de la Construcción (ITC), campus Tabasco', 'state' => 'Tabasco'],
            ['name' => 'Instituto Tecnológico de La Chontalpa', 'state' => 'Tabasco'],
            ['name' => 'Instituto Tecnológico Superior de la Región Sierra', 'state' => 'Tabasco'],
            ['name' => 'Instituto Tecnológico de Villahermosa', 'state' => 'Tabasco'],

            // Tamaulipas
            ['name' => 'Instituto Tecnológico Superior de San Fernando', 'state' => 'Tamaulipas'],
            ['name' => 'Instituto Tecnológico Superior de Mante', 'state' => 'Tamaulipas'],
            ['name' => 'Instituto Tecnológico de Altamira', 'state' => 'Tamaulipas'],
            ['name' => 'Instituto Tecnológico de Cd. Madero', 'state' => 'Tamaulipas'],
            ['name' => 'Instituto Tecnológico de Cd. Victoria', 'state' => 'Tamaulipas'],
            ['name' => 'Instituto Tecnológico de Matamoros', 'state' => 'Tamaulipas'],
            ['name' => 'Instituto Tecnológico de Nuevo Laredo', 'state' => 'Tamaulipas'],
            ['name' => 'Instituto Tecnológico de la Construcción (ITC), Delegación Tamaulipas', 'state' => 'Tamaulipas'],
            ['name' => 'Instituto Tecnológico de Reynosa', 'state' => 'Tamaulipas'],

            // Tlaxcala
            ['name' => 'Instituto Tecnológico Superior de Tlaxco', 'state' => 'Tlaxcala'],
            ['name' => 'Instituto Tecnológico de Altiplano de Tlaxcala', 'state' => 'Tlaxcala'],
            ['name' => 'Instituto Tecnológico de Tlaxcala', 'state' => 'Tlaxcala'],
            ['name' => 'Instituto Tecnológico de Apizaco', 'state' => 'Tlaxcala'],

            // Veracruz
            ['name' => 'Instituto Tecnológico Superior de Acayucan', 'state' => 'Veracruz'],
            ['name' => 'Instituto Tecnológico Superior de Álamo Temapache', 'state' => 'Veracruz'],
            ['name' => 'Instituto Tecnológico Superior de Alvarado', 'state' => 'Veracruz'],
            ['name' => 'Instituto Tecnológico Superior de Chicontepec', 'state' => 'Veracruz'],
            ['name' => 'Instituto Tecnológico Superior de Zongolica (ITS), Sede Tequila', 'state' => 'Veracruz'],
            ['name' => 'Instituto Tecnológico Superior de Zongolica (ITS), Sede Tezonapa', 'state' => 'Veracruz'],
            ['name' => 'Instituto Tecnológico Superior de Coatzacoalcos', 'state' => 'Veracruz'],
            ['name' => 'Instituto Tecnológico Superior de Cosamaloapan', 'state' => 'Veracruz'],
            ['name' => 'Instituto Tecnológico de la Construcción (ITC)', 'state' => 'Veracruz'],
            ['name' => 'Instituto Tecnológico Superior de Huatusco', 'state' => 'Veracruz'],
            ['name' => 'Instituto Tecnológico Superior de Jesús Carranza', 'state' => 'Veracruz'],
            ['name' => 'Instituto Tecnológico Superior de Juan Rodríguez Clara', 'state' => 'Veracruz'],
            ['name' => 'Instituto Tecnológico Superior de Las Choapas', 'state' => 'Veracruz'],
            ['name' => 'Instituto Tecnológico de la Construcción (ITC), Delegación Veracruz Norte', 'state' => 'Veracruz'],
            ['name' => 'Instituto Tecnológico de la Construcción, Delegación Veracruz Sur', 'state' => 'Veracruz'],
            ['name' => 'Instituto Tecnológico de la Construcción (ITC), Delegación Veracruz Centro', 'state' => 'Veracruz'],
            ['name' => 'Instituto Tecnológico Superior de Martínez de la Torre', 'state' => 'Veracruz'],
            ['name' => 'Instituto Tecnológico Superior de Misantla', 'state' => 'Veracruz'],
            ['name' => 'Instituto Tecnológico Superior de Naranjos', 'state' => 'Veracruz'],
            ['name' => 'Instituto Tecnológico Superior de Pánuco', 'state' => 'Veracruz'],
            ['name' => 'Instituto Tecnológico Superior de Perote', 'state' => 'Veracruz'],
            ['name' => 'Instituto Tecnológico Superior de Poza Rica', 'state' => 'Veracruz'],
            ['name' => 'Instituto Tecnológico Superior de San Andrés Tuxtla', 'state' => 'Veracruz'],
            ['name' => 'Instituto Tecnológico Superior de Tantoyuca', 'state' => 'Veracruz'],
            ['name' => 'Instituto Tecnológico Superior de Tierra Blanca', 'state' => 'Veracruz'],
            ['name' => 'Instituto Tecnológico Superior de Xalapa', 'state' => 'Veracruz'],
            ['name' => 'Instituto Tecnológico Superior de Zongolica', 'state' => 'Veracruz'],
            ['name' => 'Instituto Tecnológico Superior de Zongolica (ITS), Sede Nogales', 'state' => 'Veracruz'],
            ['name' => 'Instituto Tecnológico Superior de Zongolica (ITS), Sede Tehuipango', 'state' => 'Veracruz'],
            ['name' => 'Instituto Tecnológico de Úrsulo Galván', 'state' => 'Veracruz'],
            ['name' => 'Instituto Tecnológico de Boca del Río', 'state' => 'Veracruz'],
            ['name' => 'Instituto Tecnológico de Cerro Azul', 'state' => 'Veracruz'],
            ['name' => 'Instituto Tecnológico de Minatitlán', 'state' => 'Veracruz'],
            ['name' => 'Instituto Tecnológico de Orizaba', 'state' => 'Veracruz'],
            ['name' => 'Instituto Tecnológico de Informática y Administración', 'state' => 'Veracruz'],
            ['name' => 'Instituto Tecnológico de Veracruz', 'state' => 'Veracruz'],
            ['name' => 'Instituto Tecnológico Superior de Zozocolco de Hidalgo', 'state' => 'Veracruz'],
            ['name' => 'Instituto Tecnológico de Estudios Superiores René Descartes, Plantel Veracruz', 'state' => 'Veracruz'],

            // Yucatán
            ['name' => 'Instituto Tecnológico Superior de Motul', 'state' => 'Yucatán'],
            ['name' => 'Instituto Tecnológico Superior de Progreso', 'state' => 'Yucatán'],
            ['name' => 'Instituto Tecnológico de la Construcción (ITC), Delegación Yucatán', 'state' => 'Yucatán'],
            ['name' => 'Instituto Tecnológico Superior de Sur del Estado de Yucatán', 'state' => 'Yucatán'],
            ['name' => 'Instituto Tecnológico Superior de Valladolid', 'state' => 'Yucatán'],
            ['name' => 'Instituto Tecnológico de Conkal', 'state' => 'Yucatán'],
            ['name' => 'Instituto Tecnológico de Tizimín', 'state' => 'Yucatán'],
            ['name' => 'Instituto Tecnológico de Mérida', 'state' => 'Yucatán'],

            // Zacatecas
            ['name' => 'Instituto Tecnológico Superior de Fresnillo', 'state' => 'Zacatecas'],
            ['name' => 'Instituto Tecnológico Superior de Jerez', 'state' => 'Zacatecas'],
            ['name' => 'Instituto Tecnológico Superior de Loreto', 'state' => 'Zacatecas'],
            ['name' => 'Instituto Tecnológico Superior de Nochistlán', 'state' => 'Zacatecas'],
            ['name' => 'Instituto Tecnológico Superior de Zacatecas Norte', 'state' => 'Zacatecas'],
            ['name' => 'Instituto Tecnológico Superior de Zacatecas Occidente', 'state' => 'Zacatecas'],
            ['name' => 'Instituto Tecnológico de la Construcción (ITC), Delegación Zacatecas', 'state' => 'Zacatecas'],
            ['name' => 'Instituto Tecnológico Superior de Zacatecas Sur', 'state' => 'Zacatecas'],
            ['name' => 'Instituto Tecnológico de Zacatecas', 'state' => 'Zacatecas'],
        ];

        foreach ($institutions as $inst) {
            $state = State::where('name', $inst['state'])->first();

            if ($state) {
                Institution::firstOrCreate([
                    'name' => $inst['name'],
                    'state_id' => $state->id,
                ]);
            }
        }

        $this->command->info('Institutions seeded successfully.');
    }
}
