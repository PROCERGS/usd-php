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

interface IssueInterface
{
    /**
     * IssueInterface constructor.
     *
     * @param IssueBuilder $builder
     */
    public function __construct(IssueBuilder $builder);

    /**
     * @return mixed the issue Id
     */
    public function getId();

    /**
     * @return string
     */
    public function getTitle(): string;

    /**
     * @return string
     */
    public function getDescription(): string;

    /**
     * @return EndUserInterface
     */
    public function getEndUser(): EndUserInterface;

    /**
     * @return string
     */
    public function getArea(): string;

    /**
     * @return string
     */
    public function getOrganization(): string;

    /**
     * @return string
     */
    public function getConfigurationItem(): string;

    /**
     * @return string
     */
    public function getGroup(): string;

    /**
     * @return string
     */
    public function getSymptom(): string;
}
