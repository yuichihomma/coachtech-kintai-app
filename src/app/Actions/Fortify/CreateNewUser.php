<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Http\Requests\RegisterRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;


class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * 新規ユーザー登録処理
     *
     * @param  array $input
     * @return \App\Models\User
     */
    public function create(array $input)
    {
        $request = new RegisterRequest();

    validator(
        $input,
        $request->rules(),
        $request->messages()
    )->validate();

        // ==========================
        // ユーザー作成（FN001）
        // ==========================
        return User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
        ]);
    }
}
