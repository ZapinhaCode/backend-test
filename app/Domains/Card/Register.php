<?php

namespace App\Domains\Card;

use App\Domains\BaseDomain;
use App\Repositories\Account\FindByUser;
use App\Exceptions\InternalErrorException;
use App\Repositories\Card\CanUseExternalId;

class Register extends BaseDomain
{
    /**
     * Id da conta
     *
     * @var string
     */
    protected string $accountId;

    /**
     * Id do usuário
     *
     * @var string
     */
    protected string $userId;

    /**
     * Id do cartão
     *
     * @var string
     */
    protected string $cardId;

    /**
     * PIN do cartão
     *
     * @var string
     */
    protected string $pin;

    public function __construct(string $userId, string $pin, string $cardId)
    {
        $this->userId = $userId;
        /*
            Talvez criptografar o pin do cartão para melhor segurança dos dados

            aqui eu chamaria uma função que faria isto para mim
            $this->setPin($pin);
        */

        $this->pin    = $pin;
        $this->cardId = $cardId;
    }

    /*        
        Biblioteca para realizar o hash do pin
        use Illuminate\Support\Facades\Hash;

        Define e criptografa o PIN
        protected function setPin(string $pin): void
        {
            if (!$this->isValidPin($pin)) {
                throw new InternalErrorException('O PIN deve conter 4 ou 6 dígitos numéricos.', 0);
            }

            $this->pin = Hash::make($pin);
        }

        Valida se o PIN tem o formato correto aceitando 4 ou 6 dígitos numéricos
        protected function isValidPin(string $pin): bool
        {
            return preg_match('/^\d{4}(\d{2})?$/', $pin);
        }
    */

    /**
     * Busca o id de conta
     *
     * @return void
     */
    protected function findAccountId(): void
    {
        $account = (new FindByUser($this->userId))->handle();

        if (is_null($account)) {
            throw new InternalErrorException(
                'ACCOUNT_NOT_FOUND',
                161001001
            );
        }

        $this->accountId = $account['id'];
    }

    /**
     * Cartão não pode já estar vinculado
     */
    protected function checkExternalId()
    {
        if (!(new CanUseExternalId($this->cardId))->handle()) {
            throw new InternalErrorException(
                'Não é possível vincular esse cartão',
                0
            );
        }
    }

    /**
     * Checa se é possível vincular o cartão
     *
     * @return self
     */
    public function handle(): self
    {
        $this->findAccountId();
        $this->checkExternalId();

        return $this;
    }
}
