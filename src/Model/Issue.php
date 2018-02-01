<?php

namespace PROCERGS\USD\Model;

use JMS\Serializer\Annotation as JMS;

class Issue implements IssueInterface
{
    /**
     * @var null
     *
     * @JMS\SerializedName("codIncidente")
     */
    private $id;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $description;

    /**
     * @var EndUserInterface
     */
    private $endUser;

    /** @var string */
    private $area;

    /** @var string */
    private $organization;

    /** @var string */
    private $configurationItem;

    /** @var string */
    private $group;

    /** @var string */
    private $symptom;

    /**
     * IssueInterface constructor.
     * @param IssueBuilder $builder
     */
    public function __construct(IssueBuilder $builder)
    {
        $data = array_merge([
            'id' => null,
        ], $builder->getData());
        
        $this->id = $data['id'];
        $this->title = $data['title'];
        $this->description = $data['description'];
        $this->area = $data['area'];
        $this->organization = $data['organization'];
        $this->configurationItem = $data['configurationItem'];
        $this->group = $data['group'];
        $this->symptom = $data['symptom'];
        $this->endUser = $data['endUser'];
    }

    /**
     * @return mixed the issue Id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return EndUserInterface
     */
    public function getEndUser(): EndUserInterface
    {
        return $this->endUser;
    }

    /**
     * @return string
     */
    public function getArea(): string
    {
        return $this->area;
    }

    /**
     * @return string
     */
    public function getOrganization(): string
    {
        return $this->organization;
    }

    /**
     * @return string
     */
    public function getConfigurationItem(): string
    {
        return $this->configurationItem;
    }

    /**
     * @return string
     */
    public function getGroup(): string
    {
        return $this->group;
    }

    /**
     * @return string
     */
    public function getSymptom(): string
    {
        return $this->symptom;
    }
}
