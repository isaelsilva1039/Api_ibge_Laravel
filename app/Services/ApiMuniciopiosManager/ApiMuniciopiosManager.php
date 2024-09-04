<?php

namespace App\Services\ApiMuniciopiosManager;

use App\Transformers\MunicipioTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Monolog\Logger;
use Symfony\Component\HttpFoundation\JsonResponse;


/**
 * Tdas as regar dessa api relacionadas ao municiopio deve ficar aqui
 */
class ApiMuniciopiosManager
{

    /**
     * @param $uf
     * @return JsonResponse
     * */
    public function getMunicipios(Request $request, $uf)
    {
        try {

            /**
             * Usando uma chave unica para o cache
             */
            $cacheKey = "municipios_{$uf}_page_{$request->input('page', 1)}_per_page_{$request->input('max_per_page', 10)}";

            /**
             * Já está cacheado ?
             */
            $result = Cache::remember($cacheKey, 3600, function () use ($uf, $request) {
                return $this->obtemMunicipiosUF($uf);
            });


            if (!$result || !isset($result['municipios'])) {
                throw new \Exception('Nenhum dado retornado da API');
            }

            /**
             * Para melhorar o retorno dos dados, prefiro usar o padrão do symfiny (Transforme)
             */
            $municipios = MunicipioTransformer::transformCollection($result['municipios'], $result['provider']);

            $page = $request->input('page', 1);
            $limit = $request->input('max_per_page', 10);

            $total = $municipios->count();
            $paginated = $municipios->forPage($page, $limit);


            return response()->json([
                'data' => $paginated->values(),
                'meta' => [
                    'current_page' => (int) $page,
                    'per_page' => (int) $limit,
                    'total' => $total,
                    'last_page' => ceil($total / $limit),
                ]
            ]);
        } catch (\Exception $e) {

            return response()->json(['error' => $e->getMessage()], 500);
        }
    }



    /**
     * @param $uf
     * @return array
     * */
    public function obtemMunicipiosUF($uf)
    {
        $provider = config('app.municipios_provider');

        if ($provider == 'brasilapi') {
            $url = "https://brasilapi.com.br/api/ibge/municipios/v1/$uf";
        } elseif ($provider === 'ibge') {
            $url = "https://servicodados.ibge.gov.br/api/v1/localidades/estados/$uf/municipios";
        } else {
            throw new \Exception('Provider não configurado corretamente', 500);
        }

        $response = Http::get($url)->throw();

        if ($response->failed()) {
            throw new \Exception('Falha ao obter dados do provedor' , 500);
        }

        return [
            'provider' => $provider,
            'municipios' => collect($response->json())
        ];
    }

}
