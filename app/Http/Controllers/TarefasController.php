<?php

namespace App\Http\Controllers;

use App\Http\Resources\TarefasResource;
use App\Models\Tarefas;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Throwable;

class TarefasController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $data = (new Tarefas())
            ->where("user", "=", $request->header("x-tarefas-authorizer"))
            ->orderBy("created_at", $request->input("reverse") ? "desc" : "asc");
        return TarefasResource::collection($data->paginate());
    }

    /**
     * Show the form struct for creating a new resource.
     *
     */
    public function create(): array
    {
        return $this->struct(new Tarefas());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return string|false
     * @throws Throwable
     */
    public function store(Request $request): bool|string
    {
        $tarefas = new Tarefas();
        $tarefas->tarefa = $request->input("tarefa");
        $tarefas->concluida = $request->input("concluida");
        $tarefas->user = $request->header("x-tarefas-authorizer");
        try {
            $tarefas->saveOrFail();
            return json_encode(["message" => "succes", "id" => $tarefas->id]);
        } catch (Throwable) {
            return json_encode(["message" => "error"]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return bool|string
     */
    public function show(int $id): bool|string
    {
        $data = Tarefas::find($id);
        if ($data) {
            return $data->toJson();
        }
        return json_encode(["message" => "error"]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Tarefas $tarefas
     * @return Response
     */
    public function edit(Tarefas $tarefas)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return false|string
     */
    public function update(Request $request, int $id): bool|string
    {
        $tarefas = Tarefas::find($id);
        if ($tarefas) {
            $texto = $request->input("tarefa");
            $fase = $request->input("concluida");
            is_null($texto) ?: $tarefas->tarefa = $texto;
            is_null($fase) ?: $tarefas->concluida = $fase;
            $tarefas->user = $request->header("x-tarefas-authorizer");
            try {
                $tarefas->updateOrFail();
                return json_encode(["message" => "succes", "id" => $tarefas->id]);
            } catch (Throwable) {
                return json_encode(["message" => "error"]);
            }
        }
        return json_encode(["message" => "error"]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     */
    public function destroy(int $id)
    {
        Tarefas::find($id)?->delete();
    }
}
