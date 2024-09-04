<?php


namespace App\Transformers;

class MunicipioTransformer
{
    public static function transform($municipio, $provider)
    {
        if ($provider === 'ibge') {
            return [
                'name' => $municipio['nome'],
                'ibge_code' => $municipio['id'],
            ];
        } else {
            return [
                'name' => $municipio['nome'],
                'ibge_code' => $municipio['codigo_ibge'],
            ];
        }
    }

    public static function transformCollection($municipios, $provider)
    {
        return $municipios->map(function ($municipio) use ($provider) {
            return self::transform($municipio, $provider);
        });
    }
}
