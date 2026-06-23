<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Validator;

class ConfigController extends Controller
{
    /**
     * Get all system configurations.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $configs = Config::all()->pluck('value', 'key')->map(function ($value) {
            return json_decode($value, true);
        });

        return response()->json($configs);
    }

    /**
     * Update multiple configuration values.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $data = $request->all();

        // Validate basic rules to ensure specific types for specific keys
        $validator = Validator::make($data, [
            'allow_register' => 'sometimes|boolean',
            'rate_limit_requests' => 'sometimes|integer|min:1',
            'rate_limit_time' => 'sometimes|integer|min:1',
            'theme_preset' => 'sometimes|string',
            'theme_primary' => 'sometimes|string',
            'theme_surface' => 'sometimes|nullable|string',
            'theme_menuMode' => 'sometimes|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validatedData = $validator->validated();

        foreach ($validatedData as $key => $value) {
            Config::setValue($key, $value);
        }

        return response()->json([
            'message' => 'Configurações atualizadas com sucesso.',
        ]);
    }

    /**
     * Clear application cache.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function clearCache()
    {
        try {
            Artisan::call('cache:clear');
            return response()->json([
                'message' => 'Cache do sistema limpo com sucesso.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Falha ao limpar cache.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
