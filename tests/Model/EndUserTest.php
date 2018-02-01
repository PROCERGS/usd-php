<?php
/**
 * This file is part of the login-cidadao project or it's bundles and libs.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\USD\Model;

use libphonenumber\PhoneNumber;
use PHPUnit\Framework\TestCase;

class EndUserTest extends TestCase
{
    public function testEndUser()
    {
        $userCode = 'someUserCode';
        $name = 'User Name';
        $email = 'user@domain.tld';
        $phone = new PhoneNumber();
        $cpf = '123.123.123-79';

        $endUser = (new EndUser($name, $email, $userCode))
            ->setCpf($cpf)
            ->setPhone($phone);

        $this->assertSame($userCode, $endUser->getUserCode());
        $this->assertSame($name, $endUser->getName());
        $this->assertSame($email, $endUser->getEmail());
        $this->assertSame($phone, $endUser->getPhone());
        $this->assertSame($cpf, $endUser->getCpf());
    }
}
