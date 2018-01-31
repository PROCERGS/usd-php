<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\USD;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use PROCERGS\USD\Model\EndUser;
use PROCERGS\USD\Model\Issue;
use PROCERGS\USD\Model\IssueBuilder;
use PROCERGS\USD\Model\IssueInterface;

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

    /**
     * UsdService constructor.
     * @param ClientInterface $http
     * @param array $config
     */
    public function __construct(ClientInterface $http, array $config)
    {
        $this->http = $http;
        $this->defaultPhoneCountry = $config['defaultPhoneCountry'];
        $this->newIssueEndpoint = $config['newIssueEndpoint'];
        $this->phoneUtil = PhoneNumberUtil::getInstance();
    }

    public function createIssue(IssueInterface $issue): IssueInterface
    {
        try {
            $endUser = $issue->getEndUser();
            // TODO: do something with it...
            $cpf = $endUser->getCpf();
            $body = [
                'titulo' => $issue->getTitle(),
                'descricao' => $issue->getDescription(),
                'usuarioFinalAfetado' => $endUser->getUserCode(),
                'contatoUsuarioFinalAfetado' => $endUser->getName(),
                'telefoneUsuarioFinalAfetado' => $this->phoneUtil->format($endUser->getPhone(),
                    PhoneNumberFormat::E164),
                'emailUsuarioFinalAfetado' => $endUser->getEmail(),
            ];

            $response = $this->http->request('POST', $this->newIssueEndpoint, ['json' => $body]);
            $headers = $response->getHeaders();

            if ($response->getStatusCode() !== 201 || !isset($headers['Location'][0])) {
                // TODO: use appropriate exception
                throw new \RuntimeException("Error creating issue");
            }

            $issueUri = $headers['Location'][0];
            $response = $this->http->request('GET', $issueUri);

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

            $createdIssue = new Issue($builder);

            return $createdIssue;
        } catch (GuzzleException $e) {
            // TODO: now what?
            throw new \RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
