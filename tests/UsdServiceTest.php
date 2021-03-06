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

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use libphonenumber\PhoneNumber;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PROCERGS\USD\Exception\IssueCreationException;
use PROCERGS\USD\Exception\IssueNotFoundException;
use PROCERGS\USD\Model\EndUserInterface;
use PROCERGS\USD\Model\IssueInterface;

final class UsdServiceTest extends TestCase
{
    public function testConstructor()
    {
        /** @var MockObject|ClientInterface $httpClient */
        $httpClient = $this->getMockBuilder(ClientInterface::class)->getMock();
        $this->assertInstanceOf(
            UsdService::class,
            new UsdService($httpClient, $this->getConfig())
        );
    }

    public function testIssueCreation()
    {
        try {
            $issueId = random_int(1111111, 9999999);
        } catch (\Exception $e) {
            $this->fail($e->getMessage());

            return;
        }

        $title = 'Some title';
        $description = "A super complete description of the issue...\n\nWith multiple\nlines!";
        $userName = "Full User's Name";
        $userEmail = 'user@domain.tld';
        $userPhone = (new PhoneNumber())
            ->setCountryCode(55)
            ->setNationalNumber('51987654321');
        $e164 = '+5551987654321';
        $userCpf = '123.123.123-79';

        $createdIssueJson = json_encode([
            'codIncidente' => $issueId,
            'titulo' => $title,
            'descricao' => $description,
            'areaIncidente' => 'SOME AREA',
            'siglaOrganizacao' => 'ORG',
            'itemConfiguracao' => 'ITEM321',
            'grupo' => 'GROUP DELTA',
            'sintoma' => 'Unknown',
            'usuarioFinalAfetado' => 'userCode',
            'contatoUsuarioFinalAfetado' => $userName,
            'telefoneUsuarioFinalAfetado' => $e164,
            'emailUsuarioFinalAfetado' => $userEmail,
        ]);

        $endUser = $this->getMockBuilder(EndUserInterface::class)->getMock();
        $endUser->expects($this->once())->method('getName')->willReturn($userName);
        $endUser->expects($this->once())->method('getEmail')->willReturn($userEmail);
        $endUser->expects($this->once())->method('getPhone')->willReturn($userPhone);
        $endUser->expects($this->once())->method('getCpf')->willReturn($userCpf);

        $newIssueUri = "https://usd/issues/{$issueId}";

        $requests = [];
        $client = $this->getHttpClient($requests, [
            new Response(201, ['Location' => $newIssueUri]),
            new Response(200, ['Content-Type' => 'application/json'], $createdIssueJson),
        ]);

        /** @var MockObject|IssueInterface $issue */
        $issue = $this->getMockBuilder(IssueInterface::class)->getMock();
        $issue->expects($this->once())->method('getTitle')->willReturn($title);
        $issue->expects($this->once())->method('getDescription')->willReturn($description);
        $issue->expects($this->once())->method('getEndUser')->willReturn($endUser);

        $usd = new UsdService($client, $this->getConfig());

        $createdIssue = $usd->createIssue($issue);

        $this->assertInstanceOf(IssueInterface::class, $createdIssue);
        $this->assertEquals($issueId, $createdIssue->getId());
        $this->assertEquals($title, $createdIssue->getTitle());
        $this->assertEquals($description, $createdIssue->getDescription());

        $this->assertCount(2, $requests);

        /** @var Request $getIssueRequest */
        $getIssueRequest = isset($requests[1]['request']) ? $requests[1]['request'] : null;
        $this->assertAuthHeaders($getIssueRequest->getHeaders());
        $this->assertInstanceOf(Request::class, $getIssueRequest);
        $this->assertEquals($newIssueUri, $getIssueRequest->getUri()->__toString());

        /** @var Request $getIssueRequest */
        $postIssueRequest = isset($requests[0]['request']) ? $requests[0]['request'] : null;
        $reqBody = json_decode($postIssueRequest->getBody()->__toString());

        $this->assertAuthHeaders($postIssueRequest->getHeaders());
        $this->assertEquals($title, $reqBody->titulo);
        $this->assertEquals($description, $reqBody->descricao);
        $this->assertEquals('', $reqBody->usuarioFinalAfetado);
        $this->assertEquals($userName, $reqBody->contatoUsuarioFinalAfetado);
        $this->assertEquals('+5551987654321', $reqBody->telefoneUsuarioFinalAfetado);
        $this->assertEquals($userEmail, $reqBody->emailUsuarioFinalAfetado);
    }

    public function testIssueCreationWithPhoneIssue()
    {
        try {
            $issueId = random_int(1111111, 9999999);
        } catch (\Exception $e) {
            $this->fail($e->getMessage());

            return;
        }

        $title = 'Some title';
        $description = "A super complete description of the issue...\n\nWith multiple\nlines!";
        $userName = "Full User's Name";
        $userEmail = 'user@domain.tld';
        $userCpf = '123.123.123-79';

        $createdIssueJson = json_encode([
            'codIncidente' => $issueId,
            'titulo' => $title,
            'descricao' => $description,
            'areaIncidente' => 'SOME AREA',
            'siglaOrganizacao' => 'ORG',
            'itemConfiguracao' => 'ITEM321',
            'grupo' => 'GROUP DELTA',
            'sintoma' => 'Unknown',
            'usuarioFinalAfetado' => 'userCode',
            'contatoUsuarioFinalAfetado' => $userName,
            'telefoneUsuarioFinalAfetado' => 'error',
            'emailUsuarioFinalAfetado' => $userEmail,
        ]);

        $endUser = $this->getMockBuilder(EndUserInterface::class)->getMock();
        $endUser->expects($this->once())->method('getName')->willReturn($userName);
        $endUser->expects($this->once())->method('getEmail')->willReturn($userEmail);
        $endUser->expects($this->once())->method('getPhone')->willReturn(new \stdClass());
        $endUser->expects($this->once())->method('getCpf')->willReturn($userCpf);

        $newIssueUri = "https://usd/issues/{$issueId}";

        $requests = [];
        $client = $this->getHttpClient($requests, [
            new Response(201, ['Location' => $newIssueUri]),
            new Response(200, ['Content-Type' => 'application/json'], $createdIssueJson),
        ]);

        /** @var MockObject|IssueInterface $issue */
        $issue = $this->getMockBuilder(IssueInterface::class)->getMock();
        $issue->expects($this->once())->method('getTitle')->willReturn($title);
        $issue->expects($this->once())->method('getDescription')->willReturn($description);
        $issue->expects($this->once())->method('getEndUser')->willReturn($endUser);

        $usd = new UsdService($client, $this->getConfig());

        $createdIssue = $usd->createIssue($issue);

        $this->assertInstanceOf(IssueInterface::class, $createdIssue);
        $this->assertEquals($issueId, $createdIssue->getId());
        $this->assertEquals($title, $createdIssue->getTitle());
        $this->assertEquals($description, $createdIssue->getDescription());
        $this->assertNull($createdIssue->getEndUser()->getPhone());

        $this->assertCount(2, $requests);

        /** @var Request $getIssueRequest */
        $getIssueRequest = isset($requests[1]['request']) ? $requests[1]['request'] : null;
        $this->assertAuthHeaders($getIssueRequest->getHeaders());
        $this->assertInstanceOf(Request::class, $getIssueRequest);
        $this->assertEquals($newIssueUri, $getIssueRequest->getUri()->__toString());

        /** @var Request $postIssueRequest */
        $postIssueRequest = isset($requests[0]['request']) ? $requests[0]['request'] : null;
        $reqBody = json_decode($postIssueRequest->getBody()->__toString());

        $this->assertAuthHeaders($postIssueRequest->getHeaders());
        $this->assertEquals($title, $reqBody->titulo);
        $this->assertEquals($description, $reqBody->descricao);
        $this->assertEquals('', $reqBody->usuarioFinalAfetado);
        $this->assertEquals($userName, $reqBody->contatoUsuarioFinalAfetado);
        $this->assertNull($reqBody->telefoneUsuarioFinalAfetado);
        $this->assertEquals($userEmail, $reqBody->emailUsuarioFinalAfetado);
    }

    public function testIssueCreationException()
    {
        $this->expectException(IssueCreationException::class);

        $requests = [];
        $client = $this->getHttpClient($requests, [
            new Response(500),
        ]);

        /** @var MockObject|IssueInterface $issue */
        $issue = $this->getMockBuilder(IssueInterface::class)->getMock();

        $usd = new UsdService($client, $this->getConfig());
        $usd->createIssue($issue);
    }

    public function testIssueCreationUnexpectedResponse()
    {
        $this->expectException(IssueCreationException::class);

        $requests = [];
        $client = $this->getHttpClient($requests, [
            new Response(200),
        ]);

        /** @var MockObject|IssueInterface $issue */
        $issue = $this->getMockBuilder(IssueInterface::class)->getMock();

        $usd = new UsdService($client, $this->getConfig());
        $usd->createIssue($issue);
    }

    public function testInvalidConfigs()
    {
        $client = $this->getMockBuilder(ClientInterface::class)->getMock();

        $count = 0;
        try {
            new UsdService($client, []);
            $count++;
        } catch (\Exception $e) {
            $this->assertInstanceOf(\InvalidArgumentException::class, $e);
            $this->assertEquals("Missing 'defaultPhoneCountry'", $e->getMessage());
        }
        try {
            new UsdService($client, [
                'defaultPhoneCountry' => 'BR',
            ]);
            $count++;
        } catch (\Exception $e) {
            $this->assertInstanceOf(\InvalidArgumentException::class, $e);
            $this->assertEquals("Missing 'newIssueEndpoint'", $e->getMessage());
        }
        try {
            new UsdService($client, [
                'defaultPhoneCountry' => 'BR',
                'newIssueEndpoint' => 'dummy',
            ]);
            $count++;
        } catch (\Exception $e) {
            $this->assertInstanceOf(\InvalidArgumentException::class, $e);
            $this->assertEquals("Missing 'getIssueEndpoint'", $e->getMessage());
        }
        try {
            new UsdService($client, [
                'defaultPhoneCountry' => 'BR',
                'newIssueEndpoint' => 'dummy',
                'getIssueEndpoint' => 'dummy',
            ]);
            $count++;
        } catch (\Exception $e) {
            $this->assertInstanceOf(\InvalidArgumentException::class, $e);
            $this->assertEquals("Missing {id} parameter in get issue endpoint URI.", $e->getMessage());
        }
        try {
            new UsdService($client, [
                'defaultPhoneCountry' => 'BR',
                'newIssueEndpoint' => 0,
                'getIssueEndpoint' => '{id}',
            ]);
            $count++;
        } catch (\Exception $e) {
            $this->assertInstanceOf(\InvalidArgumentException::class, $e);
            $this->assertEquals("URI must be a string or UriInterface", $e->getMessage());
        }
        try {
            new UsdService($client, [
                'defaultPhoneCountry' => 'BR',
                'newIssueEndpoint' => 'dummy',
                'getIssueEndpoint' => 0,
            ]);
            $count++;
        } catch (\Exception $e) {
            $this->assertInstanceOf(\InvalidArgumentException::class, $e);
            $this->assertEquals("URI must be a string or UriInterface", $e->getMessage());
        }

        $this->assertEquals(0, $count);
    }

    public function testGetIssue()
    {
        $id = '123456';
        $title = 'Some title';
        $description = "A super complete description of the issue...\n\nWith multiple\nlines!";
        $userName = "Full User's Name";
        $userEmail = 'user@domain.tld';

        $issueJson = json_encode([
            'codIncidente' => $id,
            'titulo' => $title,
            'descricao' => $description,
            'areaIncidente' => 'SOME AREA',
            'siglaOrganizacao' => 'ORG',
            'itemConfiguracao' => 'ITEM321',
            'grupo' => 'GROUP DELTA',
            'sintoma' => 'Unknown',
            'usuarioFinalAfetado' => 'userCode',
            'contatoUsuarioFinalAfetado' => $userName,
            'telefoneUsuarioFinalAfetado' => 'error',
            'emailUsuarioFinalAfetado' => $userEmail,
        ]);

        $requests = [];
        $client = $this->getHttpClient($requests, [
            new Response(200, ['Content-Type' => 'application/json'], $issueJson),
        ]);
        $usd = new UsdService($client, $this->getConfig());
        $issue = $usd->getIssue($id);

        $this->assertInstanceOf(IssueInterface::class, $issue);
        $this->assertEquals($id, $issue->getId());
        $this->assertEquals($title, $issue->getTitle());
        $this->assertEquals($description, $issue->getDescription());
    }

    public function testGetNonExistentIssue()
    {
        $this->expectException(IssueNotFoundException::class);

        $id = '123456';
        $error = json_encode(["message" => "Número do incidente não encontrado"]);

        $requests = [];
        $client = $this->getHttpClient($requests, [
            new Response(404, ['Content-Type' => 'application/json'], $error),
        ]);
        $usd = new UsdService($client, $this->getConfig());
        $usd->getIssue($id);
    }

    public function testGetIssueException()
    {
        $this->expectException(\RuntimeException::class);

        $id = '123456';

        $requests = [];
        $client = $this->getHttpClient($requests, [
            new Response(500),
        ]);
        $usd = new UsdService($client, $this->getConfig());
        $usd->getIssue($id);
    }

    private function getHttpClient(array &$requests, array $responses)
    {
        $mock = new MockHandler($responses);

        $history = Middleware::history($requests);
        $handler = HandlerStack::create($mock);
        $handler->push($history);

        return new Client([
            'handler' => $handler,
            'headers' => [
                'matricula' => 1234,
                'organizacao' => 'myOrg',
                'senha' => 's3cr37 p4ssw0rd',
            ],
        ]);
    }

    private function assertAuthHeaders($headers)
    {
        $this->assertArrayHasKey('matricula', $headers);
        $this->assertArrayHasKey('organizacao', $headers);
        $this->assertArrayHasKey('senha', $headers);
    }

    private function getConfig()
    {
        return [
            'defaultPhoneCountry' => 'BR',
            'newIssueEndpoint' => 'https://usd/issues',
            'getIssueEndpoint' => 'https://usd/issues/{id}',
        ];
    }
}
