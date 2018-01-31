<?php

namespace PROCERGS\USD\Model;

use libphonenumber\PhoneNumber;

interface EndUserInterface
{
    /**
     * EndUserInterface constructor.
     *
     * @param string $name user's name
     * @param string $email user's email
     * @param string $userCode
     */
    public function __construct(string $name, string $email, string $userCode);

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return string
     */
    public function getEmail(): string;

    /**
     * @param PhoneNumber $phoneNumber
     * @return EndUserInterface
     */
    public function setPhone(PhoneNumber $phoneNumber);

    /**
     * @return PhoneNumber
     */
    public function getPhone(): PhoneNumber;

    /**
     * @param string $cpf
     * @return EndUserInterface
     */
    public function setCpf(string $cpf);

    /**
     * @return string
     */
    public function getCpf(): string;

    /**
     * @return string
     */
    public function getUserCode(): string;
}
