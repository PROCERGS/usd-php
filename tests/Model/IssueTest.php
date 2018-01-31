<?php

namespace PROCERGS\USD\Model;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class IssueTest extends TestCase
{
    public function testIssue()
    {
        $id = '123456';
        $title = 'title';
        $desc = 'desc';
        $area = 'area';
        $org = 'org';
        $configItem = 'configItem';
        $group = 'group';
        $symptom = 'symptom';

        /** @var MockObject|EndUserInterface $endUser */
        $endUser = $this->getMockBuilder(EndUserInterface::class)->getMock();

        $builder = (new IssueBuilder())
            ->setId($id)
            ->setTitle($title)
            ->setDescription($desc)
            ->setArea($area)
            ->setOrganization($org)
            ->setConfigurationItem($configItem)
            ->setGroup($group)
            ->setSymptom($symptom)
            ->setEndUser($endUser);

        $issue = new Issue($builder);

        $this->assertSame($id, $issue->getId());
        $this->assertSame($title, $issue->getTitle());
        $this->assertSame($desc, $issue->getDescription());
        $this->assertSame($area, $issue->getArea());
        $this->assertSame($org, $issue->getOrganization());
        $this->assertSame($configItem, $issue->getConfigurationItem());
        $this->assertSame($group, $issue->getGroup());
        $this->assertSame($symptom, $issue->getSymptom());
        $this->assertSame($endUser, $issue->getEndUser());
    }
}
