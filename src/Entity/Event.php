<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $category = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $picture = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $creator = null;

    #[ORM\Column(nullable: true)]
    private ?int $interested = null;

    #[ORM\Column(nullable: true)]
    private ?int $attending = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $TicketLink = null;

    #[ORM\Column(length: 10000, nullable: true)]
    private ?string $Comments = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'goingTo')]
    private Collection $attendingUsers;

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private ?array $location = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $address = null;


    public function __construct()
    {
        $this->attendingUsers = new ArrayCollection();
        $this->attending=0;
        $this->interested=0;
    }

    #[ORM\Column(nullable: true)]
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }



    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): static
    {
        $this->category = $category;

        return $this;
    }




    public function getPicture(): ?string
    {
        return $this->picture;
    }

    public function setPicture(?string $picture): static
    {
        $this->picture = $picture;

        return $this;
    }

    public function getCreator(): ?string
    {
        return $this->creator;
    }

    public function setCreator(?string $creator): static
    {
        $this->creator = $creator;

        return $this;
    }

    public function getInterested(): ?int
    {
        return $this->interested;
    }

    public function setInterested(?int $interested): static
    {
        $this->interested = $interested;

        return $this;
    }

    public function getAttending(): ?int
    {
        return $this->attending;
    }

    public function setAttending(?int $attending): static
    {
        $this->attending = $attending;

        return $this;
    }

    public function getTicketLink(): ?string
    {
        return $this->TicketLink;
    }

    public function setTicketLink(?string $TicketLink): static
    {
        $this->TicketLink = $TicketLink;

        return $this;
    }

    public function getComments(): ?string
    {
        return $this->Comments;
    }

    public function setComments(?string $Comments): static
    {
        $this->Comments = $Comments;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getAttendingUsers(): Collection
    {
        return $this->attendingUsers;
    }

    public function addAttendingUser(User $attendingUser): static
    {
        if (!$this->attendingUsers->contains($attendingUser)) {
            $this->attendingUsers->add($attendingUser);
            $attendingUser->addGoingTo($this);
        }

        return $this;
    }

    public function removeAttendingUser(User $attendingUser): static
    {
        if ($this->attendingUsers->removeElement($attendingUser)) {
            $attendingUser->removeGoingTo($this);
        }

        return $this;
    }

    public function getLocation(): ?array
    {
        return $this->location;
    }

    public function setLocation(?array $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(?string $address): static
    {
        $this->address = $address;

        return $this;
    }
}
