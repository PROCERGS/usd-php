<?php
/**
 * This file is part of the login-cidadao project or it's bundles.
 *
 * (c) Guilherme Donato <guilhermednt on github>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PROCERGS\USD\Model;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class IssueBuilderTest extends TestCase
{
    public function testRequiredData()
    {
        $builder = new IssueBuilder();

        /** @var MockObject|EndUserInterface $endUser */
        $endUser = $this->getMockBuilder(EndUserInterface::class)->getMock();

        $builder
            ->setTitle('title')
            ->setDescription('desc')
            ->setArea('area')
            ->setOrganization('org')
            ->setConfigurationItem('configItem')
            ->setGroup('group')
            ->setSymptom('symptom')
            ->setEndUser($endUser);

        $this->assertCount(8, $builder->getData());
    }

    public function testErrorOnMissingData()
    {
        $this->expectException(\BadMethodCallException::class);
        (new IssueBuilder())->setId('123')->getData();
    }
}
