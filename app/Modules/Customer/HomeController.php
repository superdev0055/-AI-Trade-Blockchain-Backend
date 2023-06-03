<?php

namespace App\Modules\Customer;

use App\Models\Funds;
use App\Models\Subscribes;
use App\Modules\CustomerBaseController;
use Illuminate\Http\Request;
use JetBrains\PhpStorm\ArrayShape;

class HomeController extends CustomerBaseController
{
    /**
     * @return array
     */
    #[ArrayShape(['funds' => "mixed", 'data' => "\string[][]"])]
    public function home(): array
    {
        return [
            'funds' => Funds::query()
                ->with('mainCoin')
                ->with('subCoin')
                ->inRandomOrder()
                ->take(20)
                ->get()
                ->toArray(),
            'data' => ['$217B', '100+', '103M+']
        ];
    }

    /**
     * @param Request $request
     * @return void
     */
    public function subscribe(Request $request): void
    {
        $params = $request->validate([
            'email' => 'required|email', # email
        ]);
        $exists = Subscribes::where('email', $params['email'])->exists();
        if (!$exists) {
            Subscribes::create($params);
        }
    }
}
