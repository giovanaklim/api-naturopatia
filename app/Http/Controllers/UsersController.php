<?php

namespace App\Http\Controllers;

use App\Exceptions\ApiExceptionManager;
use App\Helpers\Response;
use App\Http\Requests\LoginRequest;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use function PHPUnit\Framework\throwException;

class UsersController extends Controller
{
    public function store(LoginRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = User::firstWhere('email', $request->email);
            if ($user) {
                if($user->type === 'admin'){
                    $user->makeVisible(['password']);
                }
                throw new \Exception('user already exists');
            }
            else{
                $user = User::create($request->toArray());
            }
            $company = Company::create([
                'name' => $request->name,
                'phone'=> '',
                'email'=> '',
            ]);

            $user->company_id = $company->id;

            DB::commit();
            return Response::getJsonResponse('success', $user, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiExceptionManager::handleException($e, func_get_args(), $e->getMessage());
        }
    }

    public function updateUser (Request $request){
        if ($request->user()->type !== 'admin') throw new \Exception("user don't have access");

        DB::beginTransaction();
        try {
            $user = $request->user();
            $user->update([
                'email' => $request->email,
                'name'=> $request->name
            ]);

           $company = Company::where('id', $request->company_id)->update([
                    'email' => $request->company_email,
                    'phone' => $request->company_phone,
                ]);

            DB::commit();
            return Response::getJsonResponse('success', $company, 200);
        }catch (\Exception $e) {
            DB::rollBack();
            return ApiExceptionManager::handleException($e, func_get_args(), $e->getMessage());
        }
    }
}
