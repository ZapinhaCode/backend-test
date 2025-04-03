<?php

namespace App\Integrations\Banking\Card;

use App\Integrations\Banking\Gateway;
use App\Repositories\Account\FindByUser;
use App\Exceptions\InternalErrorException;

class Find extends Gateway
{
    /**
     * Id externo da conta
     *
     * @var string
     */
    protected string $externalAccountId;

    /**
     * Id do usuário
     *
     * @var string
     */
    protected string $userId;

    public function __construct(string $userId)
    {
        $this->userId = $userId;
    }

    /**
     * Busca os dados de conta
     *
     * @return void
     */
    protected function findAccountData(): void
    {
        $account = (new FindByUser($this->userId))->handle();

        /*
            Talvez adicionar um Log::error para melhorar a depuração caso este projeto entre em produção,
            exemplo de como eu faria:

            if (is_null($account)) {
                \Log::error("Conta não encontrada para o usuário {$this->userId}");

                throw new InternalErrorException(
                    'ACCOUNT_NOT_FOUND',
                    161001001
                );
            }
        */

        if (is_null($account)) {
            throw new InternalErrorException(
                'ACCOUNT_NOT_FOUND',
                161001001
            );
        }

        $this->externalAccountId = $account['external_id'];
    }

    /**
     * Constroi a url da request
     *
     * @return string
     */
    protected function requestUrl(): string
    {
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
            Neste handle() aplicar também um try/catch e juntamente um Log::error para que seja tratado possíveis
            falhas de requisições que quebrem a execução do código

            try {
                $this->findAccountData();

                $request = $this->sendRequest(
                    method: 'get',
                    url:    $this->requestUrl(),
                    action: 'FIND_CARD',
                    params: $this->payload()
                );

                return $this->formatDetailsResponse($request);
            } catch (\Throwable $e) {
                \Log::error("Erro ao buscar cartão para a conta {$this->externalAccountId}: " . $e->getMessage());

                return [
                    'success' => false,
                    'message' => 'Erro ao buscar cartão.',
                    'error'   => $e->getMessage(),
                ];
            }
        */

        $this->findAccountData();

        $url = $this->requestUrl();

        $request = $this->sendRequest(
            method: 'get',
            url:    $url,
            action: 'FIND_CARD',
            params: []
        );

        return $this->formatDetailsResponse($request);
    }
}
