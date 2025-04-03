<?php

namespace App\Repositories\Token;

use App\Models\User;

class Create
{
    /**
     * Id do usuário
     *
     * @var string
     */
    protected string $id;

    /**
     * Permissões
     *
     * @var array
     */
    protected array $permissions;

    /**
     * Model base para implementação
     *
     * @var string
     */
    protected string $model;

    public function __construct(string $id, array $permissions = [])
    {
        $this->id          = $id;
        $this->permissions = $permissions;
        $this->model       = User::class;
    }

    /**
     * Criação de token de acesso do usuário
     *
     * @return string
     */
    public function handle(): string
    {
        /*
            Caso o usuário não foi encontrado gera uma uma exception para tratar este erro:

            try {
                $user = app($this->model)->findOrFail($this->id);
            } catch (ModelNotFoundException $e) {
                throw new TokenGenerationException("Usuário não encontrado para gerar token.", 404);
            }

            return $user->createToken(config('auth.token_name'), $this->permissions)
                        ->plainTextToken;
        */

        return app($this->model)
            ->findOrFail($this->id)
            ->createToken(config('auth.token_name'), $this->permissions)
            ->plainTextToken;
    }
}
