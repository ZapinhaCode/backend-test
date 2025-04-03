<?php

namespace App\Domains\User;

use App\Domains\BaseDomain;
use Illuminate\Support\Facades\Hash;
use App\Repositories\User\CanUseEmail;
use App\Exceptions\InternalErrorException;
use App\Repositories\User\CanUseDocumentNumber;

class Create extends BaseDomain
{
    /**
     * Empresa
     *
     * @var string
     */
    protected string $companyId;

    /**
     * Nome
     *
     * @var string
     */
    protected string $name;

    /**
     * CPF
     *
     * @var string
     */
    protected string $documentNumber;

    /**
     * Email
     *
     * @var string
     */
    protected string $email;

    /**
     * Senha
     *
     * @var string
     */
    protected string $password;

    /**
     * Tipo
     *
     * @var string
     */
    protected string $type;

    public function __construct(
        string $companyId,
        string $name,
        string $documentNumber,
        string $email,
        string $password,
        string $type
    ) {
        $this->companyId      = $companyId;
        $this->name           = $name;
        $this->documentNumber = $documentNumber;
        $this->email          = $email;
        $this->type           = $type;

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

    /*
        Validar se a senha inserida é forte para melhorar a segurança do usuário, talvez implementar uma função
        que valida isso da seguinte forma:

        Aqui neste caso estou validando se a senha possui mais de 8 caracteres, se possui pelo menos uma letra maiúscula e
        se a senha possui pelo menos um número

        protected function validatePassword(string $password): void
        {
            if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
                throw new InternalErrorException('A senha deve ter pelo menos 8 caracteres, incluir uma letra maiúscula e um número.', 0);
            }
        }
    */


    /**
     * Encripta a senha
     *
     * @param string $password
     *
     * @return void
     */
    protected function cryptPassword(string $password): void
    {
        /*
            Dentro deste método adicionar uma verificação para garantir que a senha não seja null

            if (empty($password)) {
                throw new InternalErrorException('Senha não informada.', 0);
            }
        */

        $this->password = Hash::make($password);
    }

    /**
     * Email deve ser únicos no sistema
     *
     * @return void
     */
    protected function checkEmail(): void
    {
        if (!(new CanUseEmail($this->email))->handle()) {
            throw new InternalErrorException(
                'Não é possível adicionar o E-mail informado',
                0
            );
        }
    }

    /**
     * Email deve ser únicos no sistema
     *
     * @return void
     */
    protected function checkDocumentNumber(): void
    {
        if (!(new CanUseDocumentNumber($this->documentNumber))->handle()) {
            throw new InternalErrorException(
                'Não é possível adicionar o CPF informado',
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
        if (!in_array($this->type, ['USER', 'VIRTUAL', 'MANAGER'])) {
            throw new InternalErrorException(
                'Não é possível adicionar o tipo informado',
                0
            );
        }
    }

    /*
        Verificar se existe a companyId antes de criar o usuário

        protected function checkCompany(): void
        {
            if (!Company::where('id', $this->companyId)->exists()) {
                throw new InternalErrorException("Empresa não cadastrada.", 0);
            }
        }
    */

    /**
     * Checa se é possível realizar a criação do usuário
     *
     * @return self
     */
    public function handle(): self
    {
        $this->checkEmail();
        $this->checkDocumentNumber();
        $this->checkType();

        /*
            Chamar a verificação a função checkCompany e a validatePassword
            $this->checkCompany();
            $this->validatePassword();
        */

        return $this;
    }
}
