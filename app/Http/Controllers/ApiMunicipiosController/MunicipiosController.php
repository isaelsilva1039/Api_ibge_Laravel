<?php
namespace App\Http\Controllers\ApiMunicipiosController;

use App\Http\Controllers\Controller;
use App\Services\ApiMuniciopiosManager\ApiMuniciopiosManager;
use Illuminate\Http\Request;

class MunicipiosController extends Controller
{

    /** @var ApiMuniciopiosManager  */
    private  $apiMuniciopiosManager;
    public function __construct(ApiMuniciopiosManager $apiMuniciopiosManager)
    {
        $this->apiMuniciopiosManager = $apiMuniciopiosManager;
    }

    public function index(Request $request, $uf)
    {
        return $this->apiMuniciopiosManager->getMunicipios($request, $uf);
    }

}
