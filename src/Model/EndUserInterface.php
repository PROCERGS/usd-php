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
    public function getPhone();

    /**
     * @param string $cpf
     * @return EndUserInterface
     */
    public function setCpf(string $cpf);

    /**
     * @return string
     */
    public function getCpf();

    /**
     * @return string
     */
    public function getUserCode(): string;
}
