<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Throwable;

class UserController extends Controller
{
    public function show(Request $request): bool|AnonymousResourceCollection|string
    {
        $user = User::find($request->header("x-tarefas-authorizer"));
        if (!$user) {
            return json_encode(["message" => "error"]);
        }
        return JsonResource::collection($user->get());
    }

    /**
     * @throws Throwable
     */
    public function store(Request $request): bool|string
    {
        $user = new User();
        $user->name = $request->input("name");
        $user->email = $request->input("email");
        $user->password = password_hash($request->input("password"), CRYPT_BLOWFISH);
        try {
            $user->saveOrFail();
            return json_encode(["message" => "succes", "id" => $user->id]);
        } catch (Throwable) {
            return json_encode(["message" => "error"]);
        }
    }

    public function update(Request $request): bool|string
    {
        $user = User::find($request->header("x-tarefas-authorizer"));
        if ($user) {
            $user->name = $request->input("name");
            $user->email = $request->input("email");
            $user->password = $request->input("password");
            try {
                $user->updateOrFail();
                return json_encode(["message" => "succes", "id" => $user->id]);
            } catch (Throwable) {
                return json_encode(["message" => "error"]);
            }
        }
        return json_encode(["message" => "error"]);
    }

    public function destroy(Request $request)
    {
        User::find($request->header("x-tarefas-authorizer"))?->delete();
    }
}
