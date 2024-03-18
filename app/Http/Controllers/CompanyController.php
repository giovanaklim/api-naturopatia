<?php

namespace App\Http\Controllers;

use App\Helpers\Response;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompanyController extends Controller
{
    public function getCompany()
    {
        DB::beginTransaction();
        try {
            $response = Company::all()->first();
            DB::commit();
            return Response::getJsonResponse('success', $response, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiExceptionManager::handleException($e, func_get_args(), $e->getMessage());
        }
    }

}
