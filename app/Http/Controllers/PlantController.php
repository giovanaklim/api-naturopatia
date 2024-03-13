<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiExceptionManager;
use App\Helpers\Response;
use App\Models\ContraIndications;
use App\Models\DrugInteraction;
use App\Models\Indications;
use App\Models\Plant;
use App\Models\PlantLike;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PlantController extends Controller
{
    public function store(Request $request, User $user)
    {
        DB::beginTransaction();
        try {
            $path = null;
            if ($request->hasFile('file')){
                $path = $request->file('file')->storeAs('public/files', $request->file('file')->getClientOriginalName());
            }
            $plant = Plant::create([
                'name' => $request->name,
                'scientific_name' => $request->scientific_name,
                'file_path' => isset($path) ? asset(Storage::url($path)): null,
            ]);

            PlantLike::create([
                'user_id' => $request->user()->id,
                'plant_id' => $plant->id,
                'like' => false
            ]);

            foreach ($request->indication as $i) {
                Indications::create([
                    'plant_id' => $plant->id,
                    'name'=> $i['name']
                ]);
            }

            foreach ($request->contraindication as $c) {
                ContraIndications::create([
                    'plant_id' => $plant->id,
                    'name'=> $c['name']
                ]);
            }

            foreach ($request->medicines as $m) {
                DrugInteraction::create([
                    'plant_id' => $plant->id,
                    'name'=> $m['name']
                ]);
            }

            DB::commit();
            return Response::getJsonResponse('success', $plant, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiExceptionManager::handleException($e, func_get_args(), $e->getMessage());
        }
    }

    public function getPlants(Request $request) {
        try {
            return Plant::where(function($query) use($request) {
                $query->whereHas('indication', function($subQuery) use($request) {
                    $subQuery->whereIn('name', array_column($request->symptoms, 'name'));
                })
                    ->orWhereHas('indication', function($subQuery) use($request) {
                        $subQuery->whereIn('name', array_column($request->illnesses, 'name'));
                    });
            })
                ->whereDoesntHave('contraIndication', function($query) use($request) {
                    $query->whereIn('name', array_column($request->illnesses, 'name'));
                })
                ->whereDoesntHave('drugInteraction', function($query) use($request) {
                    $query->whereIn('name', array_column($request->medicines, 'name'));
                })
                ->with(['indication', 'contraIndication', 'drugInteraction', 'plantLike'])
                ->get();
        }catch (\Exception $e) {
            return ApiExceptionManager::handleException($e, func_get_args(), $e->getMessage());
        }
    }

    public function updatePlantLike( $id){
        try {
            $plantLike = PlantLike::where('plant_id', $id)->first();
            if ($plantLike) {
                $newLikeValue = !$plantLike->like;
                $plantLike->update(['like' => $newLikeValue]);
            }
            DB::commit();
            return Response::getJsonResponse('success', $plantLike, 200);
        }catch (\Exception $e) {
            DB::rollBack();
            return ApiExceptionManager::handleException($e, func_get_args(), $e->getMessage());
        }
    }
}
