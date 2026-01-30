<?php

namespace App\Domain\Repositories\User;

use App\Domain\Models\AccountType;
use App\Domain\Models\Customer;
use App\Domain\Models\User;
use Illuminate\Database\Query\Builder;
use App\Domain\Repositories\User\UserRepositoryInterface;
use Illuminate\Support\Facades\Hash;

class UserRepository implements UserRepositoryInterface
{
    /**
     * @param $id
     * @return Builder|mixed
     */
    public function getOne($id)
    {
        return User::with("role")->find($id);
    }

    public function readUser($request)
    {
        $users = User::where('role_id', '!=', 1)->with(['customer', 'accountType']);


        if (!empty($filter = ($request['filter'] ?? false))) {
            $users = $users->where(function ($q) use ($filter) {
                $q->where('f_name', 'ilike', "%" . $filter . "%")
                    ->orWhere('l_name', 'like', "%" . $filter . "%")
                    ->orWhere('email', 'like', "%" . $filter . "%");
            })->orWhereHas("customer", function ($q) use ($filter) {
                $q->where('name', 'ilike', "%" . $filter . "%");
            });
        }

        if (($request['sort_by'] ?? false) && ($request['sort_desc'] ?? false)) {
            $users =  $users->orderBy($request['sort_by'], $request['sort_desc']);
        } else {
            $users = $users->orderBy('id', 'desc');
        }

        if (!empty($page = ($request['page'] ?? false)) && !empty($perPage = ($request['per_page'] ?? false))) {
            return $users->paginate($perPage, ['*'], 'page', $page);
        }

        $users = $users->get();

        foreach ($users as $i => $user) {
            $users[$i]->customer_name = (($user->customer) ? $user->customer['name'] : "N/A");
            $users[$i]->account_type_name = (($user->accountType) ? $user->accountType['name'] : "N/A");
        }

        return $users;
    }

    public function createUser($request)
    {
        return User::create([
            'customer_id' => $request->customer_id,
            'account_type_id' => $request->account_type_id,
            'role_id' => 2,
            'last_login' => null,
            'email' => $request->email,
            'f_name' => $request->f_name,
            'l_name' => $request->l_name,
            'password' => Hash::make($request->password),
            'trial' => $request->trial
        ]);
    }

    public function readCompanies($request)
    {
        return Customer::all();
    }

    public function readAccountTypes($request)
    {
        return AccountType::all();
    }

    public function updateUser($request, $user)
    {

        $user->customer_id = $request->customer_id;
        $user->account_type_id = $request->account_type_id;
        $user->email = $request->email;
        $user->f_name = $request->f_name;
        $user->l_name = $request->l_name;

        $user->trial = $request->trial;


        if ($request->password) {
            $user->password = Hash::make($request->password);
        }

        return $user->save();
    }
    public function updateProfile($request)
    {
        $user = auth()->user();
        $user->f_name = $request->f_name;
        $user->l_name = $request->l_name;

        if ($request->password) {
            $user->password = Hash::make($request->password);
        }

        return $user->save();
    }
}
