<?php

namespace Tests\Feature;

namespace Tests\Feature;

use App\Services\ApiMuniciopiosManager\ApiMuniciopiosManager;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ApiMuniciopiosManagerTest extends TestCase
{
    /** @test */
    public function it_can_fetch_municipios_from_brasilapi()
    {
        Http::fake([
            'https://brasilapi.com.br/api/ibge/municipios/v1/RS' => Http::response([
                ['nome' => 'Porto Alegre', 'codigo_ibge' => 4314902],
                ['nome' => 'Canoas', 'codigo_ibge' => 4304606],
            ], 200),
        ]);

        $apiMuniciopiosManager = new ApiMuniciopiosManager();
        $request = new \Illuminate\Http\Request();
        $result = $apiMuniciopiosManager->getMunicipios($request, 'RS');

        // Verifica se a resposta contém os municípios esperados
        $this->assertJson($result->content());

    }

    /** @test */
    public function it_can_cache_municipios()
    {
        // Configura a chave de cache
        $cacheKey = "municipios_RS_page_1_per_page_10";

        // Simula o cache e a resposta da API BrasilAPI
        Cache::shouldReceive('remember')
            ->once()
            ->with($cacheKey, 3600, \Closure::class)
            ->andReturn([
                'provider' => 'brasilapi',
                'municipios' => collect([
                    ['nome' => 'Porto Alegre', 'codigo_ibge' => 4314902],
                    ['nome' => 'Canoas', 'codigo_ibge' => 4304606],
                ]),
            ]);

        // Faz a requisição para o manager
        $apiMuniciopiosManager = new ApiMuniciopiosManager();
        $request = new \Illuminate\Http\Request();
        $result = $apiMuniciopiosManager->getMunicipios($request, 'RS');

        // Verifica se a resposta contém os municípios esperados
        $this->assertJson($result->content());
        $data = json_decode($result->getContent(), true);

        // Verifica se os dados estão no formato correto
        $this->assertEquals('Porto Alegre', $data['data'][0]['name']);
        $this->assertEquals('Canoas', $data['data'][1]['name']);
    }



    /** @test
     *
     * @TODO: Esse teste está com erro
     *
     * */
    public function it_returns_error_if_api_fails()
    {

        Http::fake([
            'https://brasilapi.com.br/api/ibge/municipios/v1/SP' => Http::response(null, 500),
        ]);

        $apiMuniciopiosManager = new ApiMuniciopiosManager();
        $request = new \Illuminate\Http\Request();


        $response = $apiMuniciopiosManager->getMunicipios($request, 'SP');


        $this->assertEquals(500, $response->status());
        $this->assertEquals('Falha ao obter dados do provedor', json_decode($response->getContent(), true)['error']);
    }

}
