<?php
// # -
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class REstados extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        if(Schema::hasTable('r_estados') && DB::table('r_estados')->count() == 0){
            DB::unprepared("
                INSERT INTO r_estados (id, sigla, nome, capital) VALUES (1, 'AC', 'Acre', 'Rio Branco');
                INSERT INTO r_estados (id, sigla, nome, capital) VALUES (2, 'AL', 'Alagoas', 'Maceió');
                INSERT INTO r_estados (id, sigla, nome, capital) VALUES (3, 'AP', 'Amapá', 'Macapá');
                INSERT INTO r_estados (id, sigla, nome, capital) VALUES (4, 'AM', 'Amazonas', 'Manaus');
                INSERT INTO r_estados (id, sigla, nome, capital) VALUES (5, 'BA', 'Bahia', 'Salvador');
                INSERT INTO r_estados (id, sigla, nome, capital) VALUES (6, 'CE', 'Ceara', 'Fortaleza');
                INSERT INTO r_estados (id, sigla, nome, capital) VALUES (7, 'DF', 'Distrito Federal', 'Brasília');
                INSERT INTO r_estados (id, sigla, nome, capital) VALUES (8, 'ES', 'Espírito Santo', 'Vitória');
                INSERT INTO r_estados (id, sigla, nome, capital) VALUES (9, 'GO', 'Goiás', 'Goiânia');
                INSERT INTO r_estados (id, sigla, nome, capital) VALUES (10, 'MA', 'Maranhão', 'São Luiz');
                INSERT INTO r_estados (id, sigla, nome, capital) VALUES (11, 'MT', 'Mato Grosso', 'Cuiabá');
                INSERT INTO r_estados (id, sigla, nome, capital) VALUES (12, 'MS', 'Mato Grosso do Sul', 'Campo Grande');
                INSERT INTO r_estados (id, sigla, nome, capital) VALUES (13, 'MG', 'Minas Gerais', 'Belo Horizonte');
                INSERT INTO r_estados (id, sigla, nome, capital) VALUES (14, 'PA', 'Pará', 'Belém');
                INSERT INTO r_estados (id, sigla, nome, capital) VALUES (15, 'PB', 'Paraíba', 'João Pessoa');
                INSERT INTO r_estados (id, sigla, nome, capital) VALUES (16, 'PR', 'Paraná', 'Curitiba');
                INSERT INTO r_estados (id, sigla, nome, capital) VALUES (17, 'PE', 'Pernambuco', 'Recife');
                INSERT INTO r_estados (id, sigla, nome, capital) VALUES (18, 'PI', 'Piauí', 'Terezina');
                INSERT INTO r_estados (id, sigla, nome, capital) VALUES (19, 'RJ', 'Rio de Janeiro', 'Rio de Janeiro');
                INSERT INTO r_estados (id, sigla, nome, capital) VALUES (20, 'RN', 'Rio Grande do Norte', 'Natal');
                INSERT INTO r_estados (id, sigla, nome, capital) VALUES (21, 'RS', 'Rio Grande do Sul', 'Porto Alegre');
                INSERT INTO r_estados (id, sigla, nome, capital) VALUES (22, 'RO', 'Rondônia', 'Porto Velho');
                INSERT INTO r_estados (id, sigla, nome, capital) VALUES (23, 'RR', 'Roraima', 'Boa Vista');
                INSERT INTO r_estados (id, sigla, nome, capital) VALUES (24, 'SC', 'Santa Catarina', 'Florianópolis');
                INSERT INTO r_estados (id, sigla, nome, capital) VALUES (25, 'SP', 'São Paulo', 'São Paulo');
                INSERT INTO r_estados (id, sigla, nome, capital) VALUES (26, 'SE', 'Sergipe', 'Aracajú');
                INSERT INTO r_estados (id, sigla, nome, capital) VALUES (27, 'TO', 'Tocantins', 'Palmas');
            ");
        }
    }
}
