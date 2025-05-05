<?php
// # -
namespace App\Http\Controllers\MRA;

class MRAListas
{

    // :: Status - Ativo / Inativo
    public static function Get_options_status_ai($unsets = null)
    {
        $options = array (
            ""  =>  "---",
            1   => 	"Ativo",
            0   =>  "Inativo",
        );
        if(!is_null($unsets)){ foreach($unsets as $k => $u){ unset($options[$u]); } }
        return $options;
    }

    public static function Get_status_ai($value = null)
    {
        if(is_null($value) || ($value != 0 and empty($value))){ return ''; }
        $options = self::Get_options_status_ai();
        if(isset($options[$value])) { return $options[$value]; }
    }

    // :: Estados
    public static function Get_options_estados($unsets = null)
    {
        $options = array (
                ""   => "---",
                "AC" => "Acre",
                "AL" => "Alagoas",
                "AM" => "Amazonas",
                "AP" => "Amapá",
                "BA" => "Bahia",
                "CE" => "Ceará",
                "DF" => "Distrito Federal",
                "ES" => "Espirito Santo",
                "GO" => "Goiás",
                "MA" => "Maranhão",
                "MG" => "Minas Gerais",
                "MS" => "Mato Grosso do Sul",
                "MT" => "Mato Grosso",
                "PA" => "Pará",
                "PB" => "Paraíba",
                "PE" => "Pernambuco",
                "PI" => "Piauí",
                "PR" => "Paraná",
                "RJ" => "Rio de Janeiro",
                "RN" => "Rio Grande do Norte",
                "RO" => "Rondônia",
                "RR" => "Roraima",
                "RS" => "Rio Grande do Sul",
                "SC" => "Santa Catarina",
                "SE" => "Sergipe",
                "SP" => "São Paulo",
                "TO" => "Tocantins"
        );
        if(!is_null($unsets)){ foreach($unsets as $k => $u){ unset($options[$u]); } }
        return $options;
    }

    public static function Get_estados($value = null)
    {
        if(is_null($value) || ($value != 0 and empty($value))){ return ''; }
        $options = self::Get_options_estados();
        if (isset($options[$value])) {
            return $options[$value];
        }
    }

    // :: Tipo de Pessoa
    public static function Get_options_tipo_pessoa($unsets = null)
    {
        $options = array (
                ""  => "---",
                "F" => "Física",
                "J" => "Jurídica",
                "E" => "Estrangeiro"
        );
        if(!is_null($unsets)){ foreach($unsets as $k => $u){ unset($options[$u]); } }
        return $options;
    }

    public static function Get_tipo_pessoa($value = null)
    {
        if(is_null($value) || ($value != 0 and empty($value))){ return ''; }
        $options = self::Get_options_tipo_pessoa();
        if(isset($options[$value])) { return $options[$value]; }
    }

    // :: Países / Código SPED
    public static function Get_options_paises($unsets = null)
    {
        $options = [
            ""      => "---",
            132	    => "Afeganistão",
            7560	=> "África do Sul",
            153	    => "Aland, Ilhas",
            175	    => "Albânia, Republica da",
            230	    => "Alemanha",
            370	    => "Andorra",
            400	    => "Angola",
            418	    => "Anguilla",
            420	    => "Antartica",
            3596	=> "Antártida",
            434	    => "Antigua e Barbuda",
            531	    => "Arábia Saudita",
            590	    => "Argélia",
            639	    => "Argentina",
            647	    => "Armênia, Republica da",
            655	    => "Aruba",
            698	    => "Austrália",
            728	    => "Áustria",
            736	    => "Azerbaijão, Republica do",
            779	    => "Bahamas, Ilhas",
            809	    => "Bahrein, Ilhas",
            817	    => "Bangladesh",
            833	    => "Barbados",
            850	    => "Belarus, Republica da",
            876	    => "Bélgica",
            884	    => "Belize",
            2291	=> "Benin",
            906     => "Bermudas",
            973     => "Bolívia",
            990     => "Bonaire",
            981     => "Bósnia-herzegovina (Republica da)",
            1015	=> "Botsuana",
            1023	=> "Bouvet, Ilha",
            1058	=> "Brasil",
            1082	=> "Brunei",
            1112	=> "Bulgária, Republica da",
            310	    => "Burkina Faso",
            1155	=> "Burundi",
            1198	=> "Butão",
            1279	=> "Cabo Verde, Republica de",
            1457	=> "Camarões",
            1414	=> "Camboja",
            1490	=> "Canada",
            1546	=> "Catar",
            1376	=> "Cayman, Ilhas",
            1538	=> "Cazaquistão, Republica do",
            7889	=> "Chade",
            1589	=> "Chile",
            1600	=> "China, Republica Popular",
            1635	=> "Chipre",
            5118	=> "Christmas, Ilha (Navidad)",
            7412	=> "Cingapura",
            1651	=> "Cocos (Keeling), Ilhas",
            1694	=> "Colômbia",
            1732	=> "Comores, Ilhas",
            1775	=> "Congo",
            8885	=> "Congo, Republica Democrática do",
            1830	=> "Cook, Ilhas",
            1902	=> "Coreia, Republica da",
            1872	=> "Coreia, Republica Popular Democrática da",
            1937	=> "Costa do Marfim",
            1961	=> "Costa Rica",
            1953	=> "Croácia (Republica da)",
            1996	=> "Cuba",
            200	    => "Curaçao",
            2321	=> "Dinamarca",
            7838	=> "Djibuti",
            2356	=> "Dominica, Ilha",
            2402	=> "Egito",
            6874	=> "El Salvador",
            2445	=> "Emirados Árabes Unidos",
            2399	=> "Equador",
            2437	=> "Eritreia",
            2470	=> "Eslovaca, Republica",
            2461	=> "Eslovênia, Republica da",
            2453	=> "Espanha",
            2496	=> "Estados Unidos",
            2518	=> "Estônia, Republica da",
            7544	=> "Eswatini",
            2534	=> "Etiópia",
            2550	=> "Falkland (Ilhas Malvinas)",
            2593	=> "Feroe, Ilhas",
            8702	=> "Fiji",
            2674	=> "Filipinas",
            2712	=> "Finlândia",
            1619	=> "Formosa (Taiwan)",
            2755	=> "Franca",
            2810	=> "Gabão",
            2852	=> "Gambia",
            2895	=> "Gana",
            2917	=> "Georgia, Republica da",
            2933	=> "Gibraltar",
            2976	=> "Granada",
            3018	=> "Grécia",
            3050	=> "Groenlândia",
            3093	=> "Guadalupe",
            3131	=> "Guam",
            3174	=> "Guatemala",
            1504	=> "Guernsey, Ilha do Canal (Inclui Alderney e Sark)",
            3379	=> "Guiana",
            3255	=> "Guiana francesa",
            3298	=> "Guine",
            3344	=> "Guine-Bissau",
            3310	=> "Guine-Equatorial",
            3417	=> "Haiti",
            3450	=> "Honduras",
            3514	=> "Hong Kong",
            3557	=> "Hungria, Republica da",
            3573	=> "Iémen",
            3603	=> "Ilha Heard e Ilhas McDonald",
            3433	=> "Ilha Herad e Ilhas Macdonald",
            2925	=> "Ilhas Geórgia do Sul e Sandwich do Sul",
            18664	=> "Ilhas Menores Distantes dos Estados Unidos",
            3611	=> "Índia",
            3654	=> "Indonésia",
            3727	=> "Ira, Republica Islâmica do",
            3697	=> "Iraque",
            3751	=> "Irlanda",
            3794	=> "Islândia",
            3832	=> "Israel",
            3867	=> "Itália",
            3883	=> "Iugoslávia, República Fed. da",
            3913	=> "Jamaica",
            3999	=> "Japão",
            1508	=> "Jersey, Ilha do Canal",
            4030	=> "Jordânia",
            4111	=> "Kiribati",
            1988	=> "Kuwait",
            4200	=> "Laos, Republica Popular Democrática do",
            4260	=> "Lesoto",
            4278	=> "Letônia, Republica da",
            4316	=> "Líbano",
            4340	=> "Libéria",
            4383	=> "Líbia",
            4405	=> "Liechtenstein",
            4421	=> "Lituânia, Republica da",
            4456	=> "Luxemburgo",
            4472	=> "Macau",
            4499	=> "Macedônia do Norte",
            4502	=> "Madagascar",
            4553	=> "Malásia",
            4588	=> "Malavi",
            4618	=> "Maldivas",
            4642	=> "Mali",
            4677	=> "Malta",
            3595	=> "Man, Ilha de",
            4723	=> "Marianas do Norte",
            4740	=> "Marrocos",
            4766	=> "Marshall, Ilhas",
            4774	=> "Martinica",
            4855	=> "Mauricio",
            4880	=> "Mauritânia",
            4885	=> "Mayotte (Ilhas Francesas)",
            4936	=> "México",
            930	    => "Mianmar (Birmânia)",
            4995	=> "Micronesia",
            5053	=> "Moçambique",
            4944	=> "Moldávia, Republica da",
            4952	=> "Mônaco",
            4979	=> "Mongólia",
            4985	=> "Montenegro",
            5010	=> "Montserrat, Ilhas",
            5070	=> "Namíbia",
            5088	=> "Nauru",
            5177	=> "Nepal",
            5215	=> "Nicarágua",
            5258	=> "Níger",
            5282	=> "Nigéria",
            5312	=> "Niue, Ilha",
            5355	=> "Norfolk, Ilha",
            5380	=> "Noruega",
            5428	=> "Nova Caledonia",
            5487	=> "Nova Zelândia",
            5568	=> "Oma",
            5738	=> "Países Baixos (Holanda)",
            5754	=> "Palau",
            5780	=> "Palestina",
            5800	=> "Panamá",
            5452	=> "Papua Nova Guine",
            5762	=> "Paquistão",
            5860	=> "Paraguai",
            5894	=> "Peru",
            5932	=> "Pitcairn, Ilha",
            5991	=> "Polinésia Francesa",
            6033	=> "Polônia, Republica da",
            6114	=> "Porto Rico",
            6076	=> "Portugal",
            6238	=> "Quênia",
            6254	=> "Quirguiz, Republica",
            6289	=> "Reino Unido",
            6408	=> "Republica Centro-Africana",
            6475	=> "Republica Dominicana",
            7370	=> "Republika Srbija",
            6602	=> "Reunião, Ilha",
            6700	=> "Romênia",
            6750	=> "Ruanda",
            6769	=> "Rússia, Federação da",
            6858	=> "Saara Ocidental",
            6777	=> "Salomão, Ilhas",
            6904	=> "Samoa",
            6912	=> "Samoa Americana",
            6971	=> "San Marino",
            7102	=> "Santa Helena",
            7153	=> "Santa Lucia",
            6939	=> "São Bartolomeu",
            6955	=> "São Cristovão e Neves, Ilhas",
            6980	=> "São Martinho, Ilha de (Parte Francesa)",
            6998	=> "São Martinho, Ilha de (Parte Holandesa)",
            7005	=> "São Pedro e Miquelon",
            7200	=> "São Tome e Príncipe, Ilhas",
            7056	=> "São Vicente e Granadinas",
            7285	=> "Senegal",
            7358	=> "Serra Leoa",
            7315	=> "Seychelles",
            7447	=> "Síria, Republica Árabe da",
            7480	=> "Somalia",
            7501	=> "Sri Lanka",
            7595	=> "Sudão",
            7600	=> "Sudao do Sul",
            7641	=> "Suécia",
            7676	=> "Suíça",
            7706	=> "Suriname",
            7552	=> "Svalbard e Jan Mayen",
            7722	=> "Tadjiquistao, Republica do",
            7765	=> "Tailândia",
            7803	=> "Tanzânia, Republica Unida da",
            7919	=> "Tcheca, Republica",
            7811	=> "Terras Austrais e Antárcticas Francesas",
            3607	=> "Terras Austrais e Antárticas Francesas",
            7820	=> "Território Britânico do Oceano Indico",
            7951	=> "Timor Leste",
            8001	=> "Togo",
            8109	=> "Tonga",
            8052	=> "Toquelau, Ilhas",
            8150	=> "Trinidad e Tobago",
            8206	=> "Tunísia",
            8230	=> "Turcas e Caicos, Ilhas",
            8249	=> "Turcomenistão, Republica do",
            8273	=> "Turquia",
            8281	=> "Tuvalu",
            8311	=> "Ucrânia",
            8338	=> "Uganda",
            8451	=> "Uruguai",
            8478	=> "Uzbequistão, Republica do",
            5517	=> "Vanuatu",
            8486	=> "Vaticano, Estado da Cidade do",
            8508	=> "Venezuela",
            8583	=> "Vietnã",
            8630	=> "Virgens, Ilhas (Britânicas)",
            8664	=> "Virgens, Ilhas (E.U.A.)",
            8753	=> "Wallis e Futuna, Ilhas",
            8907	=> "Zâmbia",
            6653	=> "Zimbabue",
            8958	=> "Zona do Canal do Panamá"
        ];
        if(!is_null($unsets)){ foreach($unsets as $k => $u){ unset($options[$u]); } }
        return $options;
    }

    public static function Get_paises($value = null)
    {
        if(is_null($value) || ($value != 0 and empty($value))){ return ''; }
        $options = self::Get_options_paises();
        if(isset($options[$value])) { return $options[$value]; }
    }

    // :: Tipo de Pagamento
    public static function Get_options_tipo_pagamento($unsets = null)
    {
        $options = array (
            ""  =>  "---",
            1   => 	"À Vista",
            2   =>  "Parcelado"
        );
        if(!is_null($unsets)){ foreach($unsets as $k => $u){ unset($options[$u]); } }
        return $options;
    }

    public static function Get_tipo_pagamento($value = null)
    {
        if(is_null($value) || ($value != 0 and empty($value))){ return ''; }
        $options = self::Get_options_tipo_pagamento();
        if(isset($options[$value])) { return $options[$value]; }
    }

    // :: Status de Conclusão
    public static function Get_options_status_conclusao($unsets = null)
    {
        $options = array (
            ""  =>  "---",
            1   => 	"Concluído",
            2   =>  "Pendente"
        );
        if(!is_null($unsets)){ foreach($unsets as $k => $u){ unset($options[$u]); } }
        return $options;
    }

    public static function Get_status_conclusao($value = null)
    {
        if(is_null($value) || ($value != 0 and empty($value))){ return ''; }
        $options = self::Get_options_status_conclusao();
        if(isset($options[$value])) { return $options[$value]; }
    }

    // :: Status de Pagamento
    public static function Get_options_status_pagamento($unsets = null)
    {
        $options = array (
            ""  =>  "---",
            1   => 	"Pago",
            2   =>  "Pendente"
        );
        if(!is_null($unsets)){ foreach($unsets as $k => $u){ unset($options[$u]); } }
        return $options;
    }

    public static function Get_status_pagamento($value = null)
    {
        if(is_null($value) || ($value != 0 and empty($value))){ return ''; }
        $options = self::Get_options_status_pagamento();
        if(isset($options[$value])) { return $options[$value]; }
    }

    // :: Formas de Pagamento
    public static function Get_options_formas_pagamentos($unsets = null)
    {
        $options = array (
            ""  =>  "---",
            1   => 	"Cartão de Débito",
            2   =>  "Cartão de Crédito",
            3   =>  "Boleto",
            4   =>  "Pix",
            5   =>  "Dinheiro",
            6   =>  "Cheque",
            7   =>  "TED"
        );
        if(!is_null($unsets)){ foreach($unsets as $k => $u){ unset($options[$u]); } }
        return $options;
    }

    public static function Get_formas_pagamentos($value = null)
    {
        if(is_null($value) || ($value != 0 and empty($value))){ return ''; }
        $options = self::Get_options_formas_pagamentos();
        if(isset($options[$value])) { return $options[$value]; }
    }

}

