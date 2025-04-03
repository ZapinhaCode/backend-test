<?php

namespace App\Integrations\Banking\Card;

use App\Integrations\Banking\Gateway;
use App\Exceptions\InternalErrorException;
use App\Domains\Card\Register as RegisterDomain;
use App\Repositories\Card\FindByUser as FindCardByUser;
use App\Repositories\Account\FindByUser as FindAccountByUser;

class Register extends Gateway
{
    /**
     * Id externo do cartão
     *
     * @var string
     */
    protected string $externalCardId;

    /**
     * Id externo da conta
     *
     * @var string
     */
    protected string $externalAccountId;

    /**
     * Dados necessários para o registro do cartão
     *
     * @var RegisterDomain
     */
    protected RegisterDomain $domain;

    public function __construct(RegisterDomain $domain)
    {
        $this->domain = $domain;
    }

    /**
     * Busca os dados de conta
     *
     * @return void
     */
    protected function findAccountData(): void
    {
        $account = (new FindAccountByUser($this->domain->userId))->handle();

        if (is_null($account)) {
            throw new InternalErrorException(
                'ACCOUNT_NOT_FOUND',
                161001001
            );
        }

        $this->externalAccountId = $account['external_id'];
    }

    /**
     * Busca os dados de conta
     *
     * @return void
     */

    /*
        Esta função não está sendo chamada em nenhum lugar do código, para que ela seja executada precisamos chamá-la
        na função handle()
    */
    protected function findCardData(): void
    {
        $account = (new FindCardByUser($this->domain->userId))->handle();

        if (is_null($account)) {
            throw new InternalErrorException(
                'CARD_NOT_FOUND',
                149001001
            );
        }

        $this->externalCardId = $account['external_id'];
    }

    /**
     * Constroi a url da request
     *
     * @return string
     */
    protected function requestUrl(): string
    {
        /*
            Tratar caso o $this->externalAccountId ser null gerando erro na execução, montaria uma condição assim:

            if (!isset($this->externalAccountId)) {
                throw new \RuntimeException('O ID externo da conta não foi definido.');
            }
        */

        return "account/$this->externalAccountId/card";
    }

    /**
     * Cria de uma conta
     *
     * @return array
     */
    public function handle(): array
    {
        /*
          Montar neste handle() um try/catch para possua um tratamento para não ocorrer da aplicação cair

            try {
                $this->findAccountData();
                $this->findUserCardData();

                $request = $this->sendRequest(
                    method: 'post',
                    url:    $this->requestUrl(),
                    action: 'REGISTER_CARD',
                    params: $this->payload()
                );

                return $this->formatDetailsResponse($request);
            } catch (\Throwable $e) {
                \Log::error("Erro ao registrar cartão para a conta {$this->externalAccountId}: " . $e->getMessage());

                return [
                    'success' => false,
                    'message' => 'Erro ao registrar cartão.',
                    'error'   => $e->getMessage(),
                ];
            }
        */

        $this->findAccountData();
        /*
            Chamada da função:
            $this->findCardData();
        */

        $url = $this->requestUrl();

        $request = $this->sendRequest(
            method: 'post',
            url:    $url,
            action: 'REGISTER_CARD',
            params: [
                'pin' => $this->domain->pin,
                'id'  => $this->domain->cardId,
            ]
        );

        return $this->formatDetailsResponse($request);
    }
}
