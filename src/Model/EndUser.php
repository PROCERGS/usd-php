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

class EndUser implements EndUserInterface
{
    /** @var string */
    private $name;

    /** @var string */
    private $email;

    /** @var PhoneNumber */
    private $phone;

    /** @var string */
    private $cpf;

    /** @var string */
    private $userCode;

    /**
     * @inheritDoc
     */
    public function __construct(string $name, string $email, string $userCode)
    {
        $this->name = $name;
        $this->email = $email;
        $this->userCode = $userCode;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @inheritDoc
     */
    public function setPhone(PhoneNumber $phoneNumber)
    {
        $this->phone = $phoneNumber;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @inheritDoc
     */
    public function setCpf(string $cpf)
    {
        $this->cpf = $cpf;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getCpf()
    {
        return $this->cpf;
    }

    /**
     * @inheritDoc
     */
    public function getUserCode(): string
    {
        return $this->userCode;
    }


}
