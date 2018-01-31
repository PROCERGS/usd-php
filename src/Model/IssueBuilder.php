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

class IssueBuilder
{
    private $data;

    private static $requiredData = [
        'title',
        'description',
        'endUser',
        'area',
        'organization',
        'configurationItem',
        'group',
        'symptom',
    ];

    public function getData()
    {
        $missingKeys = array_diff(self::$requiredData, array_keys($this->data));
        if (count($missingKeys) > 0) {
            $missingKeys = implode(', ', $missingKeys);
            throw new \BadMethodCallException("Your Issue object is missing required data: {$missingKeys}");
        }

        return $this->data;
    }

    public function setId($id): IssueBuilder
    {
        $this->data['id'] = $id;

        return $this;
    }

    public function setTitle(string $title): IssueBuilder
    {
        $this->data['title'] = $title;

        return $this;
    }

    public function setDescription(string $description): IssueBuilder
    {
        $this->data['description'] = $description;

        return $this;
    }

    public function setArea(string $area): IssueBuilder
    {
        $this->data['area'] = $area;

        return $this;
    }

    public function setOrganization(string $organization): IssueBuilder
    {
        $this->data['organization'] = $organization;

        return $this;
    }

    public function setConfigurationItem(string $configurationItem): IssueBuilder
    {
        $this->data['configurationItem'] = $configurationItem;

        return $this;
    }

    public function setGroup(string $group): IssueBuilder
    {
        $this->data['group'] = $group;

        return $this;
    }

    public function setEndUser(EndUserInterface $endUser): IssueBuilder
    {
        $this->data['endUser'] = $endUser;

        return $this;
    }

    public function setSymptom(string $symptom): IssueBuilder
    {
        $this->data['symptom'] = $symptom;

        return $this;
    }
}
