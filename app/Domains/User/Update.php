<?php

namespace App\Domains\User;

use App\Domains\BaseDomain;
use Illuminate\Support\Facades\Hash;
use App\Repositories\User\CanUseEmail;
use App\Exceptions\InternalErrorException;

class Update extends BaseDomain
{
    /**
     * Id do usuário
     *
     * @var string
     */
    protected string $id;

    /**
     * Empresa
     *
     * @var string
     */
    protected string $companyId;

    /**
     * Nome
     *
     * @var string|null
     */
    protected ?string $name;

    /**
     * Email
     *
     * @var string|null
     */
    protected ?string $email;

    /**
     * Senha
     *
     * @var string|null
     */
    protected ?string $password;

    /**
     * Tipo
     *
     * @var string|null
     */
    protected ?string $type;

    public function __construct(
        string $id,
        string $companyId,
        ?string $name,
        ?string $email,
        ?string $password,
        ?string $type
    ) {
        $this->id        = $id;
        $this->companyId = $companyId;
        $this->name      = $name;
        $this->email     = $email;
        $this->type      = $type;

        /*
            Talvez utilizar o para colocar strtolower
            para o email para garantir que o mesmo seja sempre salvo em minúsculo e strtoupper para o type para sempre
            gravar ele em maiúsculo.
        */

        /*
            $this->email          = strtolower($email);
            $this->type           = strtoupper($type);
        */

        $this->cryptPassword($password);
    }

    /**
     * Encripta a senha
     *
     * @param string|null $password
     *
     * @return void
     */
    protected function cryptPassword(?string $password): void
    {
        /*
            Talvez da mesma forma de cadastrar a senha, para que ela seja atualizada colocar mais alguns critérios para
            que ela seja mais forte

            if (!is_null($password)) {
                if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
                    throw new InternalErrorException('A senha deve ter pelo menos 8 caracteres, incluir uma letra maiúscula e um número.', 0);
                } else {
                    $this->password = Hash::make($password);
                }
            } else {
                $this->password = null;
            }
        */

        $this->password = !is_null($password) ? Hash::make($password) : null;
    }

    /**
     * Email deve ser únicos no sistema
     *
     * @return void
     */
    protected function checkEmail(): void
    {
        if (is_null($this->email)) {
            return;
        }
        if (!(new CanUseEmail($this->email))->handle()) {
            throw new InternalErrorException(
                'Não é possível adicionar o E-mail informado',
                0
            );
        }
    }

    /**
     * Valida o tipo
     *
     * @return void
     */
    protected function checkType(): void
    {
        if (is_null($this->type)) {
            return;
        }
        if (!in_array($this->type, ['USER', 'VIRTUAL', 'MANAGER'])) {
            throw new InternalErrorException(
                'Não é possível adicionar o tipo informado',
                0
            );
        }
    }

    /**
     * Checa se é possível realizar a criação do usuário
     *
     * @return self
     */
    public function handle(): self
    {
        $this->checkEmail();
        $this->checkType();

        return $this;
    }
}
