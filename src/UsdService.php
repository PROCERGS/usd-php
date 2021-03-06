<?php
/**
 * This file is part of the login-cidadao project or it's bundles and libs.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\USD;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use function GuzzleHttp\Psr7\uri_for;
use GuzzleHttp\UriTemplate;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use PROCERGS\USD\Exception\IssueCreationException;
use PROCERGS\USD\Exception\IssueNotFoundException;
use PROCERGS\USD\Model\EndUser;
use PROCERGS\USD\Model\Issue;
use PROCERGS\USD\Model\IssueBuilder;
use PROCERGS\USD\Model\IssueInterface;
use Psr\Http\Message\ResponseInterface;

class UsdService
{
    /** @var ClientInterface */
    private $http;

    /** @var PhoneNumberUtil */
    private $phoneUtil;

    /** @var string */
    private $defaultPhoneCountry;

    /** @var string */
    private $newIssueEndpoint;

    /** @var string */
    private $getIssueEndpoint;

    /**
     * UsdService constructor.
     * @param ClientInterface $http
     * @param array $config
     */
    public function __construct(ClientInterface $http, array $config)
    {
        $this->http = $http;
        $this->checkConfig($config);
        $this->defaultPhoneCountry = $config['defaultPhoneCountry'];
        $this->newIssueEndpoint = $config['newIssueEndpoint'];
        $this->getIssueEndpoint = $config['getIssueEndpoint'];
        $this->phoneUtil = PhoneNumberUtil::getInstance();
    }

    public function createIssue(IssueInterface $issue): IssueInterface
    {
        try {
            $response = $this->http->request('POST', $this->newIssueEndpoint, [
                'json' => $this->getPayloadFromIssue($issue),
            ]);
            $headers = $response->getHeaders();

            if ($response->getStatusCode() !== 201 || !isset($headers['Location'][0])) {
                throw new IssueCreationException("Error creating issue.", $response->getStatusCode());
            }

            $issueUri = $headers['Location'][0];
            $createdIssue = $this->getIssueFromResponse($this->http->request('GET', $issueUri));

            return $createdIssue;
        } catch (GuzzleException $e) {
            throw new IssueCreationException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function getIssue($id): IssueInterface
    {
        try {
            $uri = (new UriTemplate())->expand($this->getIssueEndpoint, ['id' => $id]);
            $response = $this->http->request('GET', $uri);
            $issue = $this->getIssueFromResponse($response);

            return $issue;
        } catch (GuzzleException $e) {
            if ($e->getCode() === 404) {
                throw new IssueNotFoundException("Issue not found");
            }

            throw new \RuntimeException("Couldn't fetch the issue {$id}", $e->getCode(), $e);
        }
    }

    private function getPayloadFromIssue(IssueInterface $issue): array
    {
        $endUser = $issue->getEndUser();
        // TODO: do something with it...
        $cpf = $endUser->getCpf();

        $phone = $endUser->getPhone();
        if ($phone instanceof PhoneNumber) {
            $phone = $this->phoneUtil->format($phone, PhoneNumberFormat::E164);
        } else {
            $phone = null;
        }

        return [
            'titulo' => $issue->getTitle(),
            'descricao' => $issue->getDescription(),
            'usuarioFinalAfetado' => $endUser->getUserCode(),
            'contatoUsuarioFinalAfetado' => $endUser->getName(),
            'telefoneUsuarioFinalAfetado' => $phone,
            'emailUsuarioFinalAfetado' => $endUser->getEmail(),
            'areaIncidente' => $issue->getArea(),
            'siglaOrganizacao' => $issue->getOrganization(),
            'itemConfiguracao' => $issue->getConfigurationItem(),
            'grupo' => $issue->getGroup(),
            'sintoma' => $issue->getSymptom(),
        ];
    }

    private function getIssueFromResponse(ResponseInterface $response): IssueInterface
    {
        $issueData = json_decode($response->getBody()->__toString());

        $endUser = new EndUser($issueData->contatoUsuarioFinalAfetado, $issueData->emailUsuarioFinalAfetado,
            $issueData->usuarioFinalAfetado);
        if (isset($issueData->telefoneUsuarioFinalAfetado)) {
            try {
                $phone = $this->phoneUtil->parse($issueData->telefoneUsuarioFinalAfetado,
                    $this->defaultPhoneCountry);
                $endUser->setPhone($phone);
            } catch (NumberParseException $e) {
                // Ignore phone
            }
        }

        $builder = (new IssueBuilder())
            ->setId($issueData->codIncidente)
            ->setTitle($issueData->titulo)
            ->setDescription($issueData->descricao)
            ->setArea($issueData->areaIncidente)
            ->setOrganization($issueData->siglaOrganizacao)
            ->setConfigurationItem($issueData->itemConfiguracao)
            ->setGroup($issueData->grupo)
            ->setSymptom($issueData->sintoma)
            ->setEndUser($endUser);

        return new Issue($builder);
    }

    private function checkConfig(array $config)
    {
        foreach (['defaultPhoneCountry', 'newIssueEndpoint', 'getIssueEndpoint'] as $item) {
            if (!isset($config[$item])) {
                throw new \InvalidArgumentException("Missing '{$item}'");
            }
        }

        uri_for($config['newIssueEndpoint']);
        uri_for($config['getIssueEndpoint']);

        if (false === strstr($config['getIssueEndpoint'], '{id}')) {
            throw new \InvalidArgumentException("Missing {id} parameter in get issue endpoint URI.");
        }
    }
}
